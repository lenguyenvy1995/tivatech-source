<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\QuoteDomain;
use App\Models\Campaign;
use App\Models\Budget;
use App\Models\Note;
use App\Models\User;
use App\Models\Status;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampaignController extends Controller
{
    public function index(Request $request, Website $website)
    {
        $user = Auth::user();

        // Kiểm tra vai trò của người dùng để xây dựng query phù hợp
        if ($user->hasRole(['admin', 'manager'])) {
            // Admin hoặc manager có thể xem tất cả các chiến dịch
            $query = Campaign::with('status')->where('website_id', $website->id)->orderByDesc('start');
        } elseif ($user->hasRole(['saler'])) {
            // Saler chỉ có thể xem các chiến dịch của chính mình
            $query = Campaign::with('status')->where('website_id', $website->id)->where('user_id', $user->id)->orderByDesc('start');
        } else {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập vào danh sách chiến dịch.');
        }
        // Xử lý DataTables nếu request là AJAX
        if ($request->ajax()) {

            return DataTables::of($query)
                ->addColumn('action', function ($campaign)  use ($user) {
                    $buttons = '';
                    if ($campaign->status_id == '5') {
                        if ($user->hasRole('saler') || $user->hasRole('admin') || $user->hasRole('manager')) {
                            $buttons .= '<form action="' . route('campaigns.destroy', parameters: $campaign->id) . '" method="POST" style="display:inline-block;">';
                            $buttons .= csrf_field() . method_field('DELETE');
                            $buttons .= '<button type="submit" class="btn btn-danger btn-sm ml-2" onclick="return confirm(\'Bạn có chắc chắn muốn xóa website này?\');"><i class="fas fa-trash"></i></button>';
                            $buttons .= '</form>';
                        }
                    }
                    $buttons .= '<a href="' . route('campaigns.show', $campaign->id) . '" class="btn btn-info btn-sm ml-2"><i class="fas fa-eye"></i></a>';

                    return $buttons;
                })
                ->addColumn('duration', function ($campaign) {
                    $start = $campaign->start ? Carbon::parse($campaign->start)->format('d-m-Y') : 'N/A';
                    $end = $campaign->end ?  Carbon::parse($campaign->end)->format('d-m-Y') : 'N/A';

                    return '<span class="bg-primary rounded p-2">' . $start . '</span> <i class="fas fa-long-arrow-alt-right"></i> <span class="bg-primary rounded p-2">' . $end . '</span>';
                })
                ->addColumn('status_name', function ($campaign) {
                    return $campaign->status ? '<span class="p-2 ' . $campaign->status->theme . '">' . $campaign->status->name . '</span>' : 'Chưa xác định';
                })
                ->rawColumns(['action', 'duration', 'status_name'])
                ->make(true);
        }

        return view('campaigns.index', compact('website'));
    }
    public function updateStatus(Request $request)
    {
        $campaign = Campaign::findOrFail($request->campaign_id);
        $campaign->status_id = $request->status_id;
        $campaign->save();
        return response()->json(data: ['success' => true]);
    }

    public function updateVat(Request $request)
    {
        $campaign = Campaign::findOrFail($request->campaign_id);
        $campaign->vat = $request->vat;
        $campaign->save();

        return response()->json(['success' => true]);
    }

    public function updatePaid(Request $request)
    {
        $campaign = Campaign::findOrFail($request->campaign_id);
        $campaign->paid = $request->paid;
        $campaign->save();

        return response()->json(['success' => true]);
    }
    /// quản lý chiến dịch

    public function list(Request $request)
    {
        $user = Auth::user();

        // Query chính với các field cụ thể cần dùng (tối ưu từ ban đầu)
        $query = Campaign::query()
            ->select('id', 'website_id', 'user_id', 'start', 'end', 'payment', 'budgetmonth', 'status_id', 'typecamp_id', 'paid', 'vat')
            ->with(['website:id,name', 'user:id,fullname', 'status:id,name,theme']) // Load nhẹ relation
            ->withCount('budgets'); // lấy nhanh số lượng budgets
        // Điều kiện role
        if ($user->hasRole('saler')) {
            $query->where('user_id', $user->id)->whereIn('status_id', ['1', '2']);
        } elseif ($user->hasRole('admin|manager|techads')) {
            $query->whereIn('status_id', ['1', '2']);
        } else {
            return response()->json(['data' => []]);
        }
        // Lấy câu lệnh SQL và bindings
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        // Xem câu truy vấn hoàn chỉnh
        $fullSql = vsprintf(str_replace('?', "'%s'", $sql), $bindings);

        // Hoặc sử dụng dd() để kiểm tra ngay trên trình duyệt
        dd($fullSql);

        // Các bộ lọc nhanh
        if ($request->filter_paid == '1') $query->where('paid', 0);
        if ($request->filter_vat == '1') $query->where('vat', 1);
        if ($request->filter_status) $query->where('status_id', $request->filter_status);
        if ($request->filter_user) $query->where('user_id', $request->filter_user);

        if ($request->filter_typecamp_tg && $request->filter_typecamp_ns) {
            $query->whereIn('typecamp_id', [1, 2]);
        } elseif ($request->filter_typecamp_tg) {
            $query->where('typecamp_id', 1);
        } elseif ($request->filter_typecamp_ns) {
            $query->where('typecamp_id', 2);
        }

        if ($request->filter_expired == '1') {
            $query->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->where('typecamp_id', 2)
                        ->whereRaw('(payment - (SELECT COALESCE(SUM(budget), 0) FROM budgets WHERE campaign_id = campaigns.id)) <= (budgetmonth / 30 + (budgetmonth / 30 / 2))');
                })->orWhere(function ($q2) {
                    $q2->where('typecamp_id', 1)
                        ->whereRaw('(budgets_count + 1) >= (DATEDIFF(end, start) + 1)');
                });
            });
        }

        // Search nhanh theo website
        if ($request->filled('search')) {
            $query->whereHas('website', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
        dd($query->toSql(), $query->getBindings());
        // DataTables server-side xử lý
        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('stt', function ($campaign) use ($user) {
                    $res = '<span class="status-dot ' . $campaign->status->theme . '"></span> ' . ucfirst($campaign->status->name);
                    return $res;
                })
                ->addColumn('website_name', function ($campaign) {
                    return '<a href="' . route('campaigns.budgets', $campaign->id) . '" target="_blank">' . optional($campaign->website)->name . '</a><br><small>Saler: ' . optional($campaign->user)->fullname . '</small>';
                })
                ->addColumn('duration', function ($campaign) {
                    return Carbon::parse($campaign->start)->format('H:i d-m-Y') . ' → ' . Carbon::parse($campaign->end)->format('H:i d-m-Y');
                })
                ->addColumn('information', function ($campaign) {
                    return 'Ngân sách: <strong>' . number_format($campaign->budgetmonth) . '</strong><br>'
                        . 'Thanh toán: <strong>' . number_format($campaign->payment) . '</strong><br>'
                        . 'VAT: ' . ($campaign->vat == 2 ? 'Đã Xuất' : 'Chưa Xuất');
                })
                ->addColumn('expired', function ($campaign) {
                    $budgetSum = Budget::where('campaign_id', $campaign->id)->sum('budget');
                    $remainingBudget = $campaign->payment - $budgetSum;
                    if ($campaign->typecamp_id == 2) {
                        return number_format($remainingBudget);
                    } else {
                        $totalDays = Carbon::parse($campaign->start)->diffInDays(Carbon::parse($campaign->end)) + 1;
                        $remainingDays = $totalDays - $campaign->budgets_count;
                        return $remainingDays . ' ngày còn lại';
                    }
                })
                ->addColumn('note_campaign', function ($campaign) {
                    $notes = Note::where('campaign_id', $campaign->id)->latest()->limit(3)->pluck('note')->toArray();
                    $noteText = implode('<br>- ', $notes);
                    return '- ' . $noteText;
                })
                ->addColumn('action', function ($campaign) use ($user) {
                    $buttons = '';
                    if ($user->hasRole(['admin', 'techads', 'manager'])) {
                        $buttons .= '<form action="' . route('campaigns.destroy', $campaign->id) . '" method="POST" style="display:inline-block;">'
                            . csrf_field() . method_field('DELETE')
                            . '<button class="btn btn-danger btn-sm ml-1"><i class="fas fa-trash"></i></button></form>';
                    }
                    $buttons .= '<a href="' . route('campaigns.show', $campaign->id) . '" class="btn btn-info btn-sm ml-1"><i class="fas fa-eye"></i></a>'
                        . '<button class="btn bg-purple btn-sm ml-1" onclick="openNoteModal(' . $campaign->id . ')"><i class="fas fa-sticky-note"></i></button>';
                    return $buttons;
                })
                ->rawColumns(['stt', 'website_name', 'note_campaign', 'action', 'information'])
                ->make(true);
        }

        return view('campaigns.list');
    }
    public function list2(Request $request)
    {
        $user = Auth::user(); // Lấy thông tin người dùng hiện tại

        // Lọc theo điều kiện `paid = 0` nếu `filter_paid = 1`
        if ($request->filter_paid == '1') {
            $query = Campaign::query();
            $query->where('paid', 0);
            if (! $user->hasRole('admin')) {
                $query = Campaign::where('user_id', $user->id);
            }
        } else {
            // Khởi tạo truy vấn cơ bản với các điều kiện dựa trên role
            if ($user->hasRole('admin|manager|techads')) {
                $query = Campaign::whereIn('status_id', ['1', '2']);
            } elseif ($user->hasRole('saler')) {
                $query = Campaign::where('user_id', $user->id)
                    ->whereIn('status_id', ['1', '2']);
            } else {
                $query = Campaign::query(); // Nếu không có quyền, trả về một truy vấn rỗng
            }
        }
        // Lọc theo trạng thái
        if ($request->filter_status && $request->filter_status != '') {
            $query->where('status_id', $request->filter_status);
        }
        // Lọc theo nhân viên
        if ($request->filter_user && $request->filter_user != '') {
            $query->where('user_id', $request->filter_user);
        }
        // Kiểm tra xem có cả 2 bộ lọc đều được chọn
        if ($request->filter_typecamp_tg == '1' && $request->filter_typecamp_ns == '1') {
            // Nếu cả 2 được chọn, lọc cả loại 'trọn gói' và 'ngân sách'
            $query->whereIn('typecamp_id', [1, 2]);
        } elseif ($request->filter_typecamp_tg == '1') {
            // Nếu chỉ chọn 'trọn gói'
            $query->where('typecamp_id', 1);
        } elseif ($request->filter_typecamp_ns == '1') {
            // Nếu chỉ chọn 'ngân sách'
            $query->where('typecamp_id', 2);
        }
        // Thêm điều kiện lọc chiến dịch sắp hết ngân sách nếu có
        if ($request->filter_expired == '1') {
            $query->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->where('typecamp_id', 2)
                        ->whereRaw('(payment - (SELECT COALESCE(SUM(budget), 0) FROM budgets WHERE campaign_id = campaigns.id)) <= (budgetmonth / 30 + (budgetmonth / 30 / 2))');
                })
                    ->orWhere(function ($q2) {
                        $q2->where('typecamp_id', 1)
                            ->whereRaw('((SELECT COUNT(*) FROM budgets WHERE campaign_id = campaigns.id) + 1) >= (DATEDIFF(end, start) + 1)');
                    });
            });
        }
        // Lọc theo điều kiện `paid = 0` nếu `filter_paid = 1`
        if ($request->filter_paid == '1') {
            $query->where('paid', 0);
        }
        // Lọc theo điều kiện `vat = 1` nếu `filter_paid = 1`
        if ($request->filter_vat == '1') {
            $query->where('vat', 1);
        }
        // Thêm các điều kiện sắp xếp theo yêu cầu
        $query = $query->with(['budgets'])
            ->select('campaigns.*', DB::raw('
                CASE 
                    WHEN typecamp_id = 2 AND (budgetmonth - (SELECT SUM(budget) FROM budgets WHERE campaign_id = campaigns.id)) <= (budgetmonth / 30 + (budgetmonth / 30 / 2)) THEN 1
                    WHEN typecamp_id = 1 AND ((SELECT COUNT(*) FROM budgets WHERE campaign_id = campaigns.id) + 1) >= (DATEDIFF(end, start) + 1) THEN 2
                    WHEN typecamp_id = 1 THEN 3
                    WHEN typecamp_id = 2 THEN 4
                    ELSE 5
                END as sort_order,
                CASE 
                    WHEN typecamp_id = 1 THEN (DATEDIFF(end, start) + 1) - (SELECT COUNT(*) FROM budgets WHERE campaign_id = campaigns.id)
                    WHEN typecamp_id = 2 THEN budgetmonth - (SELECT SUM(budget) FROM budgets WHERE campaign_id = campaigns.id)
                    ELSE 0
                END as secondary_order
            '))
            ->orderBy('sort_order')
            ->orderBy('secondary_order');
        if ($request->has('search') && $request->search != '') {
            if ($user->hasRole('admin|manager|techads')) {
                $query = Campaign::select('campaigns.*', 'website.name as website_name')
                    ->join('website', 'campaigns.website_id', '=', 'website.id')
                    ->where(function ($query) use ($request) {
                        if ($request->has('search') && $request->search != '') {
                            $searchValue = $request->search;
                            $query->where('website.name', 'LIKE', '%' . $searchValue . '%');
                        }
                        // Các điều kiện lọc khác của bạn ở đây
                    })
                    ->orderBy('campaigns.end', 'desc'); // Sắp xếp theo campaigns.end giảm dần
            } elseif ($user->hasRole('saler')) {
                $query = Campaign::where('user_id', $user->id)
                    ->whereIn('status_id', ['1', '2']);
                $query = Campaign::select('campaigns.*', 'website.name as website_name')
                    ->join('website', 'campaigns.website_id', '=', 'website.id')
                    ->where(function ($query) use ($request) {
                        if ($request->has('search') && $request->search != '') {
                            $searchValue = $request->search;
                            $query->where('website.name', 'LIKE', '%' . $searchValue . '%');
                        }
                        // Các điều kiện lọc khác của bạn ở đây
                    })
                    ->where('campaigns.user_id', $user->id)
                    ->whereIn('campaigns.status_id', values: ['1', '2',])
                    ->orderBy('campaigns.end', 'desc'); // Sắp xếp theo campaigns.end giảm dần
                ;
            } else {
                $query = Campaign::query(); // Nếu không có quyền, trả về một truy vấn rỗng
            }
        }
        // Trả về DataTable
        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('stt', function ($campaign) use ($user) {
                    if ($user->hasRole('admin|manager|techads')) {
                        $res = '';
                        $res .= '<div class="dropdown status-dropdown">';
                        $res .= '<button class="btn btn-link p-0 dropdown-toggle" type="button" id="dropdownMenu-' . $campaign->id . '" 
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                        // Hiển thị dot của trạng thái hiện tại
                        $res .= '<span class="status-dot ' . $campaign->status->theme . '"></span> ' . ucfirst($campaign->status->name);
                        $res .= '</button>';

                        // Dropdown các trạng thái
                        $res .= '<div class="dropdown-menu" aria-labelledby="dropdownMenu-' . $campaign->id . '">';
                        foreach (Status::all() as $status) {
                            $res .= '<a class="dropdown-item status-item" 
                                            data-id="' . $campaign->id . '" 
                                            data-status-id="' . $status->id . '" 
                                            style="color: ' . $status->theme . ';"
                                            href="#">
                                        <span class="status-dot ' . $status->theme . '"></span> ' . ucfirst($status->name) . '
                                    </a>';
                        }
                        $res .= '</div></div>';
                        if ($user->hasRole('admin|manager')) {
                            // Thêm custom switch cho Thanh toán Paid chỉ khi $campaign->vat != 0
                            $labelClass = '';
                            $res .= '<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">';
                            $res .= '<input type="checkbox" class="custom-control-input paid-switch" id="paid-switch-' . $campaign->id . '" data-id="' . $campaign->id . '" ' . ($campaign->paid == 1 ? 'checked' : '') . '>';
                            $res .= '<label class="custom-control-label ' . $labelClass . '" for="paid-switch-' . $campaign->id . '">THANH TOÁN</label>';
                            $res .= '</div>';
                            // Thêm custom switch cho VAT chỉ khi $campaign->vat != 0
                            if ($campaign->vat != 0) {
                                $labelClass = '';
                                $res .= '<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">';
                                $res .= '<input type="checkbox" class="custom-control-input vat-switch" id="vat-switch-' . $campaign->id . '" data-id="' . $campaign->id . '" ' . ($campaign->vat == 2 ? 'checked' : '') . '>';
                                $res .= '<label class="custom-control-label ' . $labelClass . '" for="vat-switch-' . $campaign->id . '">THUẾ GTGT</label>';
                                $res .= '</div>';
                            }
                        }
                    } elseif ($user->hasRole('saler')) {
                        $res = '<span class="status-dot ' . $campaign->status->theme . '"></span> ' . ucfirst($campaign->status->name);
                    }
                    return $res;
                })
                ->addColumn('website_name', function ($campaign) use ($user) {
                    $res = '';
                    if ($user->hasRole('admin|manager|techads')) {
                        $res .= '<a href="' . route('campaigns.budgets', $campaign->id) . '" target="_blank">' . $campaign->website->name . '</a><br><small>Saler: ' . $campaign->user->fullname . '</small>';
                    } elseif ($user->hasRole('saler')) {
                        $res .= $campaign->website->name ?? 'N/A';
                    }
                    return $res;
                })
                ->addColumn('note_campaign', function ($campaign) use ($user) {
                    $res = '';
                    $totalNotes = $campaign->note->count();

                    // Hiển thị tối đa 3 ghi chú đầu tiên
                    foreach ($campaign->note as $key => $note) {
                        if ($key < 3) {
                            $res .= '- ' . $note->note . '<br>';
                        }
                    }
                    // Thêm dấu "..." nếu có nhiều hơn 3 ghi chú
                    if ($totalNotes > 3) {
                        $res .= '<span class="text-danger show-more" onclick="toggleNotes(' . $campaign->id . ')" style="cursor: pointer;">Xem thêm</span>';
                        $res .= '<div id="fullNotes' . $campaign->id . '" style="display: none;">';

                        // Hiển thị tất cả các ghi chú trong phần ẩn
                        foreach ($campaign->note->skip(3) as $note) {
                            $res .= '- ' . $note->note . '<br>';
                        }

                        $res .= '</div>';
                    }
                    if ($user->hasRole('admin|manager|techads')) {
                        // Thêm chi tiết "Ghi chú"
                        if ($campaign->note->isNotEmpty()) {
                            $res .= '<a target="_blank" href="' . route('campaigns.listNote', $campaign->id) . '" class="ml-2" ><i class="fas fa-eye"></i></a>';
                        }
                    }
                    return $res;
                })
                ->addColumn('action', function ($campaign) use ($user) {
                    $buttons = '';
                    if ($user->hasRole(['admin', 'techads', 'manager'])) {
                        $buttons .= '<form action="' . route('campaigns.destroy', $campaign->id) . '" method="POST" style="display:inline-block;">';
                        $buttons .= csrf_field() . method_field('DELETE');
                        $buttons .= '<button type="submit" class="btn btn-danger btn-sm ml-1" onclick="return confirm(\'Bạn có chắc chắn muốn xóa chiến dịch này?\');"><i class="fas fa-trash"></i></button>';
                        $buttons .= '</form>';
                    }
                    $buttons .= '<a href="' . route('campaigns.show', $campaign->id) . '" class="btn btn-info btn-sm ml-1"><i class="fas fa-eye"></i></a>';

                    // Thêm button "Ghi chú"
                    $buttons .= '<button type="button" class="btn bg-purple btn-sm ml-1" onclick="openNoteModal(' . $campaign->id . ')"><i class="fas fa-sticky-note"></i></button>';

                    return $buttons;
                })
                ->addColumn('duration', function ($campaign) {
                    $start = $campaign->start ? Carbon::parse($campaign->start)->format('H:i d-m-Y ') : 'N/A';
                    $end = $campaign->end ?  Carbon::parse($campaign->end)->format('H:i d-m-Y ') : 'N/A';

                    return '<span > <strong>Bắt Đầu</strong> <br>' . $start . '</span> <br><span > <strong>Kết Thúc</strong> <br>' . $end . '</span>';
                })
                ->addColumn('information', function ($campaign) {
                    $res = '<span>Ngân sách: </span><strong class="text-danger"> ' . number_format($campaign->budgetmonth) . '</strong> <br>';
                    if ($campaign->paid == 1) {
                        $res .= '<span>Thanh toán: </span><del>' . number_format($campaign->payment) . '</del><br>';
                    } else {
                        $res .= '<span>Thanh Toán: </span><strong class="text-danger"> ' . number_format($campaign->payment) . '</strong> <br>';
                    }
                    if ($campaign->vat == 1) {
                        $res .= '<span>GTGT (VAT) :</span><span class="badge badge-danger">Chưa Xuất</span><br>';
                    } elseif ($campaign->vat == 2) {
                        $res .= '<span>GTGT (VAT) :</span><span class="badge badge-success">Đã Xuất</span><br>';
                    }
                    return $res;
                })
                ->addColumn('expired', function ($campaign) use ($user) {
                    $res = '<div ';
                    if ($campaign->typecamp_id == 1) {
                        $res .= 'class="callout callout-info p-1 m-0 text-center" >';
                        $start = $campaign->start ? Carbon::parse($campaign->start) : null;
                        $end = $campaign->end ? Carbon::parse($campaign->end) : null;
                        $days = $start->diffInDays($end);
                        // Xác định giờ nhập
                        $startTime = $start->format('H:i');
                        $endTime = $end->format('H:i');

                        if ($startTime == '00:00' && $endTime == '00:00') {
                            $days += 1; // Từ 00:00 đến 00:00 là trọn ngày
                        } elseif ($startTime == '12:00' && $endTime == '12:00') {
                            // Giữ nguyên, vì diffInDays() đã đúng
                        } elseif ($startTime == '00:00' && $endTime == '12:00') {
                            $days -= 1; // Vì 00:00 đến 12:00 không trọn 1 ngày
                        } elseif ($startTime == '12:00' && $endTime == '00:00') {
                            $days += 1; // Vì 12:00 đến 00:00 kéo dài hơn 1 ngày
                        }
                        if ($start && $end) {
                            $budgetCount = $campaign->budgets->sum('calu');
                            $remainingDays = $days - $budgetCount;
                            if ($budgetCount + 1 >= $days) {
                                if ($budgetCount + 1 == $days) {
                                    $res .= '<h5><span class="badge badge-pill badge-warning">Còn ' . $remainingDays . ' Ngày </span></h5>';
                                } elseif ($budgetCount > $days) {
                                    $res .= '<h5><span class="badge badge-pill badge-danger">HẾT HẠN ' . $remainingDays . ' Ngày</span></h5>';
                                } else {
                                    $res .= '<h5><span class="badge badge-pill badge-danger">HẾT HẠN </span></h5>';
                                }
                            } else {
                                $res .= $remainingDays . ' Ngày <br>';
                            }
                            $res .= $campaign->budgets->sum('calu') . ' / ' . $days;
                        }
                        if ($user->hasRole(['admin', 'techads', 'manager'])) {
                            $cl = '';
                            if ($campaign->budgets->sum("budget") > $campaign->payment) {
                                $cl = "bg-fuchsia color-palette";
                            };
                            $res .= '<p class="' . $cl . '">' . number_format($campaign->budgets->sum("budget")) . ' / <span>' . number_format($campaign->payment) . '<span><p>';
                        }
                    } elseif ($campaign->typecamp_id == 2) {
                        $res .= 'class="callout callout-danger m-0 p-1 text-center" >';
                        $start = $campaign->start ? Carbon::parse($campaign->start) : null;
                        $end = $campaign->end ? Carbon::parse($campaign->end) : null;
                        $days = $start->diffInDays($end) + 1 ?: 1;

                        $totalBudget = $campaign->budgets ? $campaign->budgets->sum('budget') : 0;
                        $remainingBudget = $campaign->payment - $totalBudget;
                        $threshold = $campaign->budgetmonth / 30 + ($campaign->budgetmonth / 30 / 2);

                        if ($remainingBudget <= $threshold) {
                            $res .= '<h5><span class="badge badge-pill badge-danger">' . number_format($remainingBudget) . '</span></h5>';
                        } else {
                            $res .= number_format($campaign->payment - $totalBudget) . '<br>';
                        }
                        $res .= $campaign->budgets->sum('calu') . ' / ' . $days;
                    }
                    return $res . '</div>';
                })
                ->rawColumns(['stt', 'website_name', 'note_campaign', 'action', 'duration', 'information', 'expired'])
                ->with(['req' => $request->search])
                ->make(true);
        }
        return view('campaigns.list'); // Trả về view chứa DataTable
    }

    public function show($id)
    {
        $campaign = Campaign::with('status')->findOrFail($id); // Sử dụng 'with' nếu có liên kết với status
        // dd($campaign);
        return view('campaigns.show', compact('campaign'));
    }
    public function create()
    {
        $domains = QuoteDomain::all(); // Lấy tất cả domains

        return view('campaigns.create', compact('domains')); // Sử dụng lại cùng view
    }

    public function store(Request $request)
    {
        // Validate các trường dữ liệu
        $request->validate([
            'domain' => 'required',
            'top_position' => 'required|string',
            'region' => 'required|string',
            'keyword_type' => 'required|string',
            'typecamp_id' => 'required|integer',
            'display' => 'required|string',
            'budgetmonth' => 'required|numeric',
            'payment' => 'required|numeric',
            'promotion' => 'nullable|numeric',
            'start' => 'required|date_format:d-m-Y H:i',
            'end' => 'required|date_format:d-m-Y H:i',
            'device' => 'required|string',
            'keywords' => 'required|string',
            'notes' => 'nullable|string',
        ]);
        // Kiểm tra và xác định `website_id`
        if ($request->filled('website_id')) {
            $website_id = $request->website_id;
        } else {
            // Tìm kiếm website dựa trên domain trong bảng `quote_domains`
            $website = Website::where('name', $request->domain)->first();

            if ($website) {
                // Nếu website tồn tại, lấy website_id của nó
                $website_id = $website->id;
            } else {
                // Nếu không tồn tại, tạo website mới và gán `user` hiện tại
                $website = Website::create(['name' => $request->domain]);
                $website_id = $website->id;

                // Gán `User` hiện tại vào `Website` mới qua mối quan hệ nhiều-nhiều
                $website->users()->attach(Auth::id());
            }
        }
        // Chuyển đổi định dạng ngày
        $startDate = Carbon::createFromFormat('d-m-Y H:i', $request->start)->format('Y-m-d H:i:s');
        $endDate = Carbon::createFromFormat('d-m-Y H:i', $request->end)->format('Y-m-d H:i:s');
        $request->merge(['start' => $startDate]);
        $request->merge(['end' => $endDate]);
        $request->merge(['status_id' => '5']);
        $request->merge(['user_id' => Auth::id()]);
        $request->merge(['website_id' => $website_id]);
        // Tạo một bản ghi campaign mới
        $campaign = new Campaign;
        $campaign->fill($request->all());
        $campaign->save();
        $note = new Note;
        $note->user_id = Auth::id();
        $note->campaign_id = $campaign->id;
        $note->note = $request->note_campaign;
        $note->save();
        // dd($request->all());

        return redirect()->route('websites.campaigns', $website_id)->with('success', 'Campaign đã được gia hạn thành công.');
    }
    // Controller
    public function getNote(Campaign $campaign)
    {
        return response()->json(['note' => $campaign->note]);
    }

    public function saveNote(Request $request, Campaign $campaign)
    {
        $campaign->note = $request->note;
        $campaign->save();

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        $campaign = Campaign::findOrFail($id); // Lấy dữ liệu Campaign theo ID
        $domains = Website::all(); // Giả sử bạn muốn danh sách website cho dropdown
        return view('campaigns.edit', compact('campaign', 'domains'));
    }
    public function showRenewForm($id)
    {
        $campaign = Campaign::findOrFail($id); // Lấy dữ liệu Campaign theo ID
        $domains = Website::all(); // Giả sử bạn muốn danh sách website cho dropdown
        return view('campaigns.renew', compact('campaign', 'domains'));
    }
    public function update(Request $request, $id)
    {
        // Validate dữ liệu
        $request->validate([
            'top_position' => 'required|string',
            'region' => 'required|string',
            'keyword_type' => 'required|string',
            'typecamp_id' => 'required|integer',
            'display' => 'required|string',
            'budgetmonth' => 'required|numeric',
            'payment' => 'required|numeric',
            'promotion' => 'nullable|numeric',
            'start' => 'required|date_format:d-m-Y H:i',
            'end' => 'required|date_format:d-m-Y H:i',
            'device' => 'required|string',
            'keywords' => 'required|string',
            'notes' => 'nullable|string',
        ]);
        // Chuyển đổi định dạng ngày
        $startDate = Carbon::createFromFormat('d-m-Y H:i', $request->start)->format('Y-m-d H:i:s');
        $endDate = Carbon::createFromFormat('d-m-Y H:i', $request->end)->format('Y-m-d H:i:s');
        $request->merge(['start' => $startDate]);
        $request->merge(['end' => $endDate]);
        $campaign = Campaign::findOrFail($id);
        $campaign->update($request->all()); // Cập nhật Campaign
        return redirect()->route('websites.campaigns', $campaign->website->id)->with('success', 'Campaign đã được cập nhật thành công.');
    }
    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id); // Tìm campaign theo ID
        $website = $campaign->website_id;
        $campaign->delete(); // Xóa campaign
        return redirect()->back()->with('success', 'Campaign đã được xóa thành công.');
    }
    public function setups(Request $request)
    {
        // Lấy người dùng hiện tại
        $user = Auth::user();
        // Kiểm tra role của người dùng
        if ($user->hasRole('admin|manager|techads')) {
            // Nếu người dùng là "admin", "manager" hoặc "techads", lấy toàn bộ campaigns
            $campaigns = Campaign::where('status_id', 5)
                ->with('website')->orderByDesc('id');
        } elseif ($user->hasRole('saler')) {
            // Nếu người dùng là "saler", chỉ lấy các campaigns của người dùng đó
            $campaigns = Campaign::where('status_id', 5)
                ->where('user_id', $user->id)
                ->with('website'); // Quan hệ "website"
        } else {
            return back()->with('error', 'Bạn Không có quyền truy cập');
        }
        if ($request->ajax()) {
            return DataTables::of($campaigns)
                ->editColumn('start', function ($campaign) {
                    return Carbon::parse($campaign->start)->format('d-m-Y');
                })
                ->editColumn('end', function ($campaign) {
                    return Carbon::parse($campaign->end)->format('d-m-Y');
                })
                ->editColumn('budgetmonth', function ($campaign) {
                    return number_format($campaign->budgetmonth);
                })
                ->addColumn('website_name', function ($campaign) use ($user) {
                    if ($user->hasRole('admin|manager|techads')) {
                        // Nếu người dùng là "admin", "manager" hoặc "techads", lấy toàn bộ campaigns
                        return $campaign->website->name . '<br>' . '<small>' . $campaign->user->fullname . '</small>'; // Hiển thị tên website từ quan hệ
                    } else {
                        return $campaign->website->name; // Hiển thị tên website từ quan hệ
                    }
                })
                ->addColumn('action', function ($campaign) use ($user) {
                    $buttons = '';

                    if ($campaign->status_id == '5') {
                        if ($user->hasRole('saler') || $user->hasRole('admin') || $user->hasRole('manager')) {
                            $buttons .= '<form action="' . route('campaigns.destroy', parameters: $campaign->id) . '" method="POST" style="display:inline-block;">';
                            $buttons .= csrf_field() . method_field('DELETE');
                            $buttons .= '<button type="submit" class="btn btn-danger btn-sm ml-1" onclick="return confirm(\'Bạn có chắc chắn muốn xóa website này?\');"><i class="fas fa-trash"></i></button>';
                            $buttons .= '</form>';
                        }
                    }
                    $buttons .= '<a href="' . route('campaigns.show', $campaign->id) . '" class="btn btn-info btn-sm ml-2"><i class="fas fa-eye"></i></a>';
                    if ($user->hasRole('techads') || $user->hasRole('admin') || $user->hasRole('manager')) {
                        $buttons .= '<button onclick="setupCampaign(' . $campaign->id . ')" class="btn btn-success btn-sm ml-2"><i class="fas fa-cog"></i> </button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['action', 'website_name', 'duration', 'status_name'])
                ->make(true);
        }
        return view('campaigns.setups');
    }
    //thay ddoiro trangj thai
    public function markAsSetup($id, Request $request)
    {
        $campaign = Campaign::findOrFail($id); // Tìm campaign theo ID

        // Cập nhật status_id thành 1 (hoàn thành setup)
        $campaign->status_id = 1;
        $campaign->save();

        return response()->json(['success' => 'Campaign đã được setup thành công.']);
    }
    //bảng tính lương dự kiến
    public function getMonthlySalesData(Request $request)
    {
        // Nhận giá trị từ `monthYearPicker` và tách thành tháng và năm
        $users = User::where('status', 1)->get();
        $monthYear = $request->input('monthYearPicker', now()->format('m-Y'));
        list($month, $year) = explode('-', $monthYear);
        // Chuyển đổi giá trị thành số nguyên để đảm bảo đúng kiểu
        $month = (int)$month;
        $year = (int)$year;
        // Điều kiện cho typecamp_id = 1
        isset($request->user_id) ? $userId = $request->user_id : $userId = Auth::id();
        $user = User::find($userId);
        $campaignsType1 = Campaign::where('user_id', $userId)
            ->where('typecamp_id', 1)
            ->where(function ($query) use ($month, $year) {
                $query->where(function ($q) use ($month, $year) {
                    // Chiến dịch bắt đầu trong tháng
                    $q->whereMonth('start', $month)
                        ->whereYear('start', $year);
                })
                    ->orWhere(function ($q) use ($month, $year) {
                        // Chiến dịch kết thúc trong tháng
                        $q->whereMonth('end', $month)
                            ->whereYear('end', $year);
                    })
                    ->orWhere(function ($q) use ($month, $year) {
                        // Chiến dịch bao trùm toàn bộ tháng (bắt đầu trước và kết thúc sau tháng đó)
                        $q->whereDate('start', '<=', Carbon::create($year, $month, 1))
                            ->whereDate('end', '>=', Carbon::create($year, $month)->endOfMonth());
                    });
            })->orderBy('end', 'asc');
        $campaignsType2 = Campaign::where('user_id', $userId)
            ->where('typecamp_id', 2)
            ->where('status_id', 3)
            ->whereMonth('end', $month)
            ->whereYear('end', $year)
            ->orderBy('end', 'asc');
        // Sử dụng `union` và lấy tất cả kết quả
        $campaigns = $campaignsType1->union($campaignsType2)->orderBy('end', 'asc')->get();
        // $campaigns = $campaignsType1->merge($campaignsType2);
        if ($request->ajax()) {

            return DataTables::of($campaigns)
                ->addColumn('website_name', function ($campaign) {
                    return ($campaign->website->name ?? 'N/A');
                })
                ->addColumn('duration', function ($campaign) {
                    $start = $campaign->start ? Carbon::parse($campaign->start)->format('d-m-Y') : 'N/A';
                    $end = $campaign->end ? Carbon::parse($campaign->end)->format('d-m-Y') : 'N/A';
                    return '<span class="p-2">' . $start . '</span> <i class="fas fa-long-arrow-alt-right"></i> <span class="p-2">' . $end . '</span>';
                })
                ->addColumn('daysInMonth', function ($campaign) use ($month, $year) {
                    if ($campaign->typecamp_id == 1) {
                        $startOfMonth = Carbon::create($year, $month, 1);
                        $endOfMonth = $startOfMonth->copy()->endOfMonth();
                        $campaignStart = Carbon::parse($campaign->start);
                        $campaignEnd = Carbon::parse($campaign->end);

                        if ($campaignStart->month == $month && $campaignStart->year == $year) {
                            // Case 1: start is within the month and year
                            if ($campaignEnd->month == $month && $campaignEnd->year == $year) {
                                return $campaignStart->diffInDays($campaignEnd) + 1;
                            } elseif ($campaignEnd->greaterThan($endOfMonth)) {
                                return $endOfMonth->diffInDays($campaignStart) + 1;
                            }
                        } elseif ($campaignStart->lessThan($startOfMonth)) {
                            // Case 2: start is before the month and year
                            if ($campaignEnd->greaterThan($endOfMonth)) {
                                return $endOfMonth->day;
                            } elseif ($campaignEnd->month == $month && $campaignEnd->year == $year) {
                                return $campaignEnd->diffInDays($startOfMonth) + 1;
                            }
                        }
                    }
                    return 'Ngân sách'; // Default case if none of the above match
                })
                ->editColumn('promotion', function ($campaign) {
                    return number_format($campaign->promotion);
                })
                ->addColumn('type', function ($campaign) {
                    return $campaign->typecamp_id == 1 ? 'Trọn gói' : 'Ngân sách';
                })
                ->addColumn('sales', function ($campaign) use ($month, $year) {
                    if ($campaign->typecamp_id == 1) {
                        $startOfMonth = Carbon::create($year, $month, 1);
                        $endOfMonth = $startOfMonth->copy()->endOfMonth();
                        $campaignStart = Carbon::parse($campaign->start);
                        $campaignEnd = Carbon::parse($campaign->end);

                        // Tính số ngày thuộc tháng hiện tại
                        if ($campaignStart->lessThanOrEqualTo($endOfMonth) && $campaignEnd->greaterThanOrEqualTo($startOfMonth)) {
                            $actualStart = $campaignStart->greaterThan($startOfMonth) ? $campaignStart : $startOfMonth;
                            $actualEnd = $campaignEnd->lessThan($endOfMonth) ? $campaignEnd : $endOfMonth;

                            // Số ngày thuộc tháng hiện tại
                            $daysInMonth = $actualStart->diffInDays($actualEnd) + 1;

                            // Tính dailyRate dựa trên promotion hoặc budgetmonth (chia đều cho 30 ngày)
                            $dailyRate = $campaign->promotion && $campaign->promotion != 0
                                ? $campaign->promotion / 30
                                : $campaign->budgetmonth / 30;

                            // Tính doanh số
                            return number_format($dailyRate * $daysInMonth);
                        }

                        return '0'; // Trường hợp không thuộc tháng
                    } else {
                        return number_format($campaign->payment);
                    }
                })
                ->addColumn('budgetmonth', function ($campaign) {
                    return number_format($campaign->budgetmonth);
                })
                ->addColumn('payment', function ($campaign) {
                    return number_format($campaign->payment);
                })
                ->rawColumns(['duration', 'sales'])
                ->with([
                    'totalSalesType1' => $this->getMonthlySales($month, $year, $userId),
                    'totalSalesType2' =>  $this->getMonthlySalesTypeCamp2($month, $year, $userId),
                    'totalSales' =>  $this->getMonthlySales($month, $year, $userId) + $this->getMonthlySalesTypeCamp2($month, $year, $userId),
                ])
                ->make(true);
        }
        return view('campaigns/monthly_sales', compact('users'));
    }
    //tính doanh số trọn gói theo tháng
    public static function  getMonthlySales($month = null, $year = null, $userId = null)
    {
        // Nếu không có giá trị $month và $year, sử dụng tháng và năm hiện tại
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;
        $userId = $userId ?? Auth::id();
        $user = User::find($userId ?? Auth::id());
        $totalSales = 0;
        if ($user->hasRole('admin|manager')) {
            // Lấy các chiến dịch với điều kiện `user_id = Auth::id()` và `typecamp_id = 1`
            $campaigns =  Campaign::where('typecamp_id', 1)
                ->where(function ($query) use ($month, $year) {
                    $query->where(function ($q) use ($month, $year) {
                        // Chiến dịch bắt đầu trong tháng
                        $q->whereMonth('start', $month)
                            ->whereYear('start', $year);
                    })
                        ->orWhere(function ($q) use ($month, $year) {
                            // Chiến dịch kết thúc trong tháng
                            $q->whereMonth('end', $month)
                                ->whereYear('end', $year);
                        })
                        ->orWhere(function ($q) use ($month, $year) {
                            // Chiến dịch bao trùm toàn bộ tháng (bắt đầu trước và kết thúc sau tháng đó)
                            $q->whereDate('start', '<=', Carbon::create($year, $month, 1))
                                ->whereDate('end', '>=', Carbon::create($year, $month)->endOfMonth());
                        });
                })
                ->get();
            // Tính tổng doanh số
            $totalSales = $campaigns->sum(function ($campaign) use ($month, $year) {
                // Ngày đầu và cuối tháng
                $startOfMonth = Carbon::create($year, $month, 1);
                $endOfMonth = $startOfMonth->copy()->endOfMonth();

                // Chuyển đổi start và end thành đối tượng Carbon
                $campaignStart = Carbon::parse($campaign->start);
                $campaignEnd = Carbon::parse($campaign->end);

                // Xác định số ngày của chiến dịch trong tháng
                if ($campaignStart >= $startOfMonth && $campaignEnd <= $endOfMonth) {
                    // Trường hợp 1: start và end đều nằm trong tháng
                    $daysInMonth = $campaignStart->diffInDays($campaignEnd) + 1;
                } elseif ($campaignStart->month == $month && $campaignEnd->month > $month) {
                    // Trường hợp 2: start nằm trong tháng, end vượt qua tháng
                    $daysInMonth = $endOfMonth->diffInDays($campaignStart) + 1;
                } elseif ($campaignEnd->month == $month && $campaignStart->month < $month) {
                    // Trường hợp 3: end nằm trong tháng, start trước tháng
                    $daysInMonth = $campaignEnd->diffInDays($startOfMonth) + 1;
                } else {
                    // Trường hợp 4: chiến dịch bắt đầu trước và kết thúc sau tháng
                    $daysInMonth = $startOfMonth->diffInDays($endOfMonth) + 1;
                }

                // Kiểm tra giá trị của promotion trước khi tính doanh số
                $dailyRate = $campaign->promotion && $campaign->promotion != 0
                    ? $campaign->promotion / 30
                    : $campaign->budgetmonth / 30;
                // Tính doanh số cho chiến dịch
                return $dailyRate * $daysInMonth;
            });
        } else {
            // Lấy các chiến dịch với điều kiện `user_id = Auth::id()` và `typecamp_id = 1`
            $campaigns =  Campaign::where('user_id', $userId)
                ->where('typecamp_id', 1)
                ->where(function ($query) use ($month, $year) {
                    $query->where(function ($q) use ($month, $year) {
                        // Chiến dịch bắt đầu trong tháng
                        $q->whereMonth('start', $month)
                            ->whereYear('start', $year);
                    })
                        ->orWhere(function ($q) use ($month, $year) {
                            // Chiến dịch kết thúc trong tháng
                            $q->whereMonth('end', $month)
                                ->whereYear('end', $year);
                        })
                        ->orWhere(function ($q) use ($month, $year) {
                            // Chiến dịch bao trùm toàn bộ tháng (bắt đầu trước và kết thúc sau tháng đó)
                            $q->whereDate('start', '<=', Carbon::create($year, $month, 1))
                                ->whereDate('end', '>=', Carbon::create($year, $month)->endOfMonth());
                        });
                })
                ->get();
            // Tính tổng doanh số
            $totalSales = $campaigns->sum(function ($campaign) use ($month, $year) {
                // Ngày đầu và cuối tháng
                $startOfMonth = Carbon::create($year, $month, 1);
                $endOfMonth = $startOfMonth->copy()->endOfMonth();

                // Chuyển đổi start và end thành đối tượng Carbon
                $campaignStart = Carbon::parse($campaign->start);
                $campaignEnd = Carbon::parse($campaign->end);

                // Xác định số ngày của chiến dịch trong tháng
                if ($campaignStart->greaterThanOrEqualTo($startOfMonth) && $campaignEnd->lessThanOrEqualTo($endOfMonth)) {
                    // Trường hợp 1: start và end đều nằm trong tháng
                    $daysInMonth = $campaignStart->diffInDays($campaignEnd) + 1;
                } elseif ($campaignStart->greaterThanOrEqualTo($startOfMonth) && $campaignEnd->greaterThan($endOfMonth)) {
                    // Trường hợp 2: start nằm trong tháng, end vượt qua tháng
                    $daysInMonth = $endOfMonth->diffInDays($campaignStart) + 1;
                } elseif ($campaignEnd->lessThanOrEqualTo($endOfMonth) && $campaignStart->lessThan($startOfMonth)) {
                    // Trường hợp 3: end nằm trong tháng, start trước tháng
                    $daysInMonth = $campaignEnd->diffInDays($startOfMonth) + 1;
                } elseif ($campaignStart->lessThan($startOfMonth) && $campaignEnd->greaterThan($endOfMonth)) {
                    // Trường hợp 4: chiến dịch bắt đầu trước và kết thúc sau tháng
                    $daysInMonth = $startOfMonth->diffInDays($endOfMonth) + 1;
                } else {
                    // Trường hợp không thuộc tháng hiện tại
                    $daysInMonth = 0;
                }

                // Kiểm tra giá trị của promotion trước khi tính doanh số
                $dailyRate = $campaign->promotion && $campaign->promotion != 0
                    ? $campaign->promotion / 30
                    : $campaign->budgetmonth / 30;
                // Tính doanh số cho chiến dịch
                return $dailyRate * $daysInMonth;
            });
        }
        return $totalSales;
    }
    //tính doanh số ngân sách theo tháng
    public static function getMonthlySalesTypeCamp2($month = null, $year = null, $userId = null)
    {
        $totalSales = 0;
        // Sử dụng tháng và năm hiện tại nếu không có giá trị đầu vào
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;
        $userId = $userId ?? Auth::id();
        $user = User::find($userId ?? Auth::id());
        if ($user->hasRole('admin|manager')) {
            // Lấy các chiến dịch với điều kiện: typecamp_id = 2, user_id = Auth::id(), status_id = 3, và end nằm trong tháng-năm nhập vào
            $campaigns = Campaign::where('typecamp_id', 2)
                ->where('status_id', 3)
                ->whereMonth('end', $month)
                ->whereYear('end', $year)
                ->get();

            // Tính tổng doanh số cho các chiến dịch
            $totalSales = $campaigns->sum(function ($campaign) {
                return $campaign->payment; // Sử dụng doanh số dựa trên trường "payment" của campaign
            });
        } else {
            // Lấy các chiến dịch với điều kiện: typecamp_id = 2, user_id = Auth::id(), status_id = 3, và end nằm trong tháng-năm nhập vào
            $campaigns = Campaign::where('typecamp_id', 2)
                ->where('user_id', $userId)
                ->where('status_id', 3)
                ->whereMonth('end', $month)
                ->whereYear('end', $year)
                ->get();

            // Tính tổng doanh số cho các chiến dịch
            $totalSales = $campaigns->sum(function ($campaign) {
                return $campaign->payment; // Sử dụng doanh số dựa trên trường "payment" của campaign
            });
        }
        return $totalSales;
    }
    public function showBudgets($campaignId)
    {
        $campaign = Campaign::with(['budgets' => function ($query) {
            $query->orderBy('date', 'desc'); // Sắp xếp giảm dần theo date
        }])->findOrFail($campaignId);
        // Trả về JSON cho Datatable (sử dụng AJAX)
        if (request()->ajax()) {
            return datatables()->of($campaign->budgets)
                ->editColumn('budget', function ($row) {
                    $tooltip = $row->account ?? '';
                    return '<span data-toggle="tooltip" title="' . $tooltip . '">' . number_format($row->budget) . '</span>';
                })
                ->editColumn('date', function ($row) {
                    return Carbon::parse($row->date)->format('d-m-Y');
                })
                ->addColumn('calu', function ($row) {
                    $radioButtons = '
                    <div class="form-group form-inline text-center justify-content-around">
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input custom-control-input-danger changeCalu" type="radio" id="calu_0_' . $row->id . '" data-id="' . $row->id . '" name="calu_' . $row->id . '" value="0"' . ($row->calu == 0 ? ' checked' : '') . '>
                        <label for="calu_0_' . $row->id . '" class="custom-control-label">Không Tính</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input custom-control-input-danger changeCalu" type="radio" id="calu_0_5_' . $row->id . '"data-id="' . $row->id . '" name="calu_' . $row->id . '" value="0.5"' . ($row->calu == 0.5 ? ' checked' : '') . '>
                        <label for="calu_0_5_' . $row->id . '" class="custom-control-label">1/2 Ngày</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input custom-control-input-danger changeCalu" type="radio" id="calu_1_' . $row->id . '" data-id="' . $row->id . '" name="calu_' . $row->id . '" value="1"' . ($row->calu == 1 ? ' checked' : '') . '>
                        <label for="calu_1_' . $row->id . '" class="custom-control-label">1 Ngày</label>
                    </div></div>
                ';
                    return $radioButtons;
                })
                ->addColumn('action', function ($row) {
                    return '
                        <a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-primary btn-sm editBudget"><i class="fas fa-pen">  </i></a>
                        <a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-danger btn-sm deleteBudget"><i class="fas fa-trash">  </i></a>
                    ';
                })
                ->rawColumns(['action', 'calu', 'budget'])
                ->make(true);
        }
        // Trả về view nếu không phải AJAX
        return view('campaigns.budgets', compact('campaign'));
    }
    //tìm kiếm theo từ khoá
    public function search(Request $request)
    {
        if ($request->ajax()) {
            $keyword = $request->keyword;

            $campaigns = Campaign::query();

            // Nếu có từ khóa, thêm điều kiện lọc
            if ($request->keyword) {
                $campaigns->where('keywords', 'LIKE', '%' . $request->keyword . '%');
            } else {
                // Nếu không có keyword, trả về dữ liệu rỗng
                return DataTables::of([])->make(true);
            }
            $campaigns->orderBy('start', 'desc');

            return DataTables::of($campaigns)
                ->editColumn('keywords', function ($row) use ($keyword) {
                    // Làm nổi bật từ khóa và xuống dòng nếu có nhiều từ khóa (phân cách bởi dấu phẩy)
                    $highlighted = str_ireplace(
                        $keyword,
                        '<span style="color: red; font-weight: bold;">' . $keyword . '</span>',
                        $row->keywords
                    );
                    return nl2br($highlighted); // Thay thế xuống dòng với HTML
                })
                ->editColumn('budgetmonth', function ($row) {
                    return number_format($row->budgetmonth);
                })
                ->addColumn('time', function ($row) {
                    return Carbon::parse($row->start)->format('d-m-Y') . ' ==> ' . Carbon::parse($row->end)->format('d-m-Y');
                })
                ->addColumn('website', function ($row) {
                    $campaign = "<a href=" . route('campaigns.show', $row->id) . " target='_blank'>" . $row->website->name . " </a>";
                    return $campaign;
                })
                ->addColumn('status', function ($row) {
                    $status = '<span class=" p-2 badge ' . $row->status->theme . '">' . $row->status->name . '</span>';
                    return $status;
                })
                ->rawColumns(['actions', 'website', 'keywords', 'status'])
                ->make(true);
        }

        return view('campaigns.search');
    }
}
