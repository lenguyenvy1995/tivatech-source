<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\QuoteDomain;
use App\Models\Campaign;
use App\Models\Budget;
use App\Models\Note;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
        if ($request->ajax()) {
            $query = Campaign::query()
                ->select([
                    'campaigns.id',
                    'campaigns.website_id',
                    'campaigns.user_id',
                    'campaigns.start',
                    'campaigns.end',
                    'campaigns.payment',
                    'campaigns.budgetmonth',
                    'campaigns.status_id',
                    'campaigns.typecamp_id',
                    'campaigns.paid',
                    'campaigns.vat',
                    'website.name as website_name',
                    'users.fullname as user_fullname',
                    'status.name as status_name',
                    'status.theme as status_theme',
                    DB::raw('(SELECT SUM(budget) FROM budgets WHERE budgets.campaign_id = campaigns.id) as total_budgets'),
                    DB::raw('(SELECT SUM(calu) FROM budgets WHERE budgets.campaign_id = campaigns.id) as total_calu'),
                    DB::raw('(SELECT GROUP_CONCAT(note SEPARATOR "\n") FROM notes WHERE notes.campaign_id = campaigns.id ORDER BY created_at DESC) as latest_note')
                ])
                ->leftJoin('website', 'website.id', '=', 'campaigns.website_id')
                ->leftJoin('users', 'users.id', '=', 'campaigns.user_id')
                ->leftJoin('status', 'status.id', '=', 'campaigns.status_id');

            // Lọc trạng thái: nếu filter_status được chọn thì lọc đúng trạng thái, nếu không thì mặc định chỉ lấy 1 và 2
            if ($request->filled('filter_status')) {
                $query->whereIn('campaigns.status_id', (array)$request->filter_status);
            } else {
                $query->whereIn('campaigns.status_id', [1, 2]);
            }
            // Lọc theo nhân viên (user)
            if ($request->filled('filter_user')) {
                $query->where('campaigns.user_id', $request->filter_user);
            }
            // lọc chiến dịch hết hạn
            if ($request->filter_expired == '1') {
                $query->where(function ($q) {
                    $q->where(function ($q1) {
                        // Ngân sách: sắp hết ngân sách
                        $q1->where('typecamp_id', 2)
                            ->whereRaw('(payment - (SELECT COALESCE(SUM(budget), 0) FROM budgets WHERE campaign_id = campaigns.id)) <= (budgetmonth / 30)');
                    })
                    ->orWhere(function ($q2) {
                        // Trọn gói: sắp hết ngày chạy
                        $q2->where('typecamp_id', 1)
                            ->whereRaw('((SELECT COALESCE(SUM(calu), 0) FROM budgets WHERE campaign_id = campaigns.id)) >= (DATEDIFF(end, start) + 1 - 2)');
                    });
                });
            }
            // Lọc theo loại chiến dịch
            $filterTypecamp = [];
            if ($request->filter_typecamp_tg == '1') {
                $filterTypecamp[] = 1;
            }
            if ($request->filter_typecamp_ns == '2') {
                $filterTypecamp[] = 2;
            }
            if (!empty($filterTypecamp)) {
                $query->whereIn('campaigns.typecamp_id', $filterTypecamp);
            }

            // Thêm lọc thanh toán
            if ($request->filter_paid != '') {
                $query->where('campaigns.paid', $request->filter_paid);
            }

            if ($user->hasRole('saler')) {
                $query->where('campaigns.user_id', $user->id);
            }

            // Lọc theo tên website nếu có search
            if ($request->filled('search.value')) {
                $query->where('website.name', 'like', '%' . $request->input('search.value') . '%');
            }

            // Sorting logic based on filters
            if (
                $request->filled('filter_paid') ||
                $request->filter_expired == '1' ||
                $request->filter_typecamp_tg == '1' ||
                $request->filter_typecamp_ns == '2' ||
                $request->filled('search.value')
            ) {
                $query->orderBy('campaigns.end');
            } else {
                $query->orderBy('campaigns.status_id', 'asc')->orderBy('campaigns.end', 'asc');
            }

            return DataTables::of($query)
                ->addColumn('status', function ($campaign) {
                    $user =Auth::user();
                    $statusList = [
                        1 => ['name' => 'hoạt động', 'color' => '#28a745'],
                        2 => ['name' => 'tạm dừng', 'color' => '#ffc107'],
                        3 => ['name' => 'hoàn thành', 'color' => '#17a2b8'],
                        4 => ['name' => 'hết chạy', 'color' => '#6c757d'],
                        5 => ['name' => 'setup', 'color' => '#e83e8c'],
                    ];

                    $current = $statusList[$campaign->status_id] ?? ['name' => 'không xác định', 'color' => '#6c757d'];

                    if ($user && $user->hasRole('saler')) {
                        return '<div class="d-flex align-items-center">
                                    <div style="width:10px;height:10px;border-radius:50%;background-color:' . $current['color'] . ';margin-right:5px;"></div>
                                    <span style="font-size:14px;color:' . $current['color'] . ';">' . ucfirst($current['name']) . '</span>
                                </div>';
                    }

                    $html = '<div class="dropdown">
                                <button class="btn btn-sm dropdown-toggle d-flex align-items-center" type="button" id="dropdownStatus-' . $campaign->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background:transparent;border:none;padding:0;">
                                    <div style="width:10px;height:10px;border-radius:50%;background-color:' . $current['color'] . ';margin-right:5px;"></div>
                                    <span style="font-size:14px;color:' . $current['color'] . ';">' . ucfirst($current['name']) . '</span>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownStatus-' . $campaign->id . '">';

                    foreach ($statusList as $id => $status) {
                        $html .= '<a class="dropdown-item change-status d-flex align-items-center" href="#" data-campaign-id="' . $campaign->id . '" data-status-id="' . $id . '">
                                    <div style="width:10px;height:10px;border-radius:50%;background-color:' . $status['color'] . ';margin-right:5px;"></div>
                                    <span style="color:' . $status['color'] . ';">' . ucfirst($status['name']) . '</span>
                                  </a>';
                    }

                    $html .= '</div></div>';
                    return $html;
                })
                ->addColumn('website_name', function ($campaign) {
                    // $url = route('campaigns.budgets', ['campaignId' => $campaign->id]);
                    $user =Auth::user();
                    $url = '/campaigns/' . $campaign->id . '/budgets';
                    if ($user && $user->hasRole('saler')) {
                        return '<div>
                            <a style="font-weight:bold;"> ' . $campaign->website_name . '</a>
                            <br><small class="text-muted">Saler: ' . ($campaign->user_fullname ?? 'Không có') . '</small>
                        </div>';
                    }
                    return '<div>
                                <a href="' . $url . '" target="_blank" style="font-weight:bold;">' . $campaign->website_name . '</a>
                                <br><small class="text-muted">Saler: ' . ($campaign->user_fullname ?? 'Không có') . '</small>
                            </div>';
                })
                ->addColumn('duration', function ($campaign) {
                    $start = $campaign->start ? Carbon::parse($campaign->start)->format('H:i d-m-Y') : '';
                    $end = $campaign->end ? Carbon::parse($campaign->end)->format('H:i d-m-Y') : '';
                    return "<div><strong>Bắt đầu:</strong> $start<br><strong>Kết thúc:</strong> $end</div>";
                })
                ->addColumn('budget_payment', function ($campaign) {
                    $budgetmonth = number_format($campaign->budgetmonth);
                    $payment = number_format($campaign->payment);
                    $paidBadge = $campaign->paid
                        ? '<del style="color:green; font-weight:bold;">' . $payment . '</del>'
                        : '<span style="color:red; font-weight:bold;">' . $payment . ' 🔥</span>';
                    $vatCheckbox = $campaign->vat != 0 ? '<div class="form-check form-check-inline"><input class="form-check-input toggle-vat" type="checkbox" data-id="' . $campaign->id . '" ' . ($campaign->vat == 2 ? 'checked' : '') . '> VAT</div>' : '';

                    return '
                        <div class="d-flex flex-column text-center">
                            <div>
                                <span>Ngân sách: <strong>' . $budgetmonth . '</strong></span><br>
                                <span>Thanh toán: ' . $paidBadge . '</span>
                            </div>
                            <div class="mt-2">
                                <div class="form-check form-check-inline mr-2">
                                    <input type="checkbox" class="form-check-input toggle-paid" data-id="' . $campaign->id . '" ' . ($campaign->paid ? 'checked' : '') . '>
                                    <label class="form-check-label">Thanh toán</label>
                                </div>
                                ' . $vatCheckbox . '
                            </div>
                        </div>';
                })
                ->addColumn('renew', function ($campaign) {
                    $user = Auth::user();
                    $totalBudgets = $campaign->total_budgets ?? 0;
                    $payment = $campaign->payment ?? 0;
                    $start = $campaign->start ? Carbon::parse($campaign->start) : null;
                    $end = $campaign->end ? Carbon::parse($campaign->end) : null;
                    $runningDays = round($campaign->total_calu ?? 0, 1);
                    $totalDays = 0;

                    // Updated: Always count both start and end dates as inclusive
                    if ($start && $end) {
                        $totalDays = $start->diffInDays($end) + 1;
                    }

                    $notification = '';

                    if ($campaign->typecamp_id == 1) { // Trọn gói
                        $remainingDays = $totalDays - $runningDays;
                        if ($remainingDays > 2) {
                            $notification = '<div><small class="badge badge-success">Còn ' . $remainingDays . ' ngày</small></div>';
                        } elseif ($remainingDays > 0.5 && $remainingDays <= 2) {
                            $notification = '<div><small class="badge badge-warning">Còn '. $remainingDays .' ngày</small></div>';
                        } elseif ($remainingDays <= 0.5) {
                            $notification = '<div><small class="badge badge-danger">Hết hạn ' . $remainingDays . ' ngày</small></div>';
                        }
                    } elseif ($campaign->typecamp_id == 2) { // Ngân sách
                        $remainingBudget = $payment - $totalBudgets;
                        $threshold = $campaign->budgetmonth / 30;
                        if ($remainingBudget <= $threshold) {
                            $notification = '<div><small class="badge badge-danger">NS:' . number_format($remainingBudget) . '</small></div>';
                        } else {
                            $notification = '<div><small class="badge badge-secondary">NS: ' . number_format($remainingBudget) . '</small></div>';
                        }
                    }

                    $html = '<div class="text-center">';
                    if (!$user->hasRole('saler')) {
                        $html .= number_format($totalBudgets) . ' / ' . number_format($payment) . '<br>';
                    }
                    $html .= '<small>' . $runningDays . ' / ' . $totalDays . ' ngày</small>';
                    $html .= $notification;
                    $html .= '</div>';

                    return $html;
                })
                ->addColumn('note', function ($campaign) {
                    if (!empty($campaign->latest_note)) {
                        $notes = explode("\n", $campaign->latest_note);
                        $html = '';
                        $limit = 3;
                
                        foreach ($notes as $index => $note) {
                            if ($index < $limit) {
                                $html .= '👉 ' . e($note) . '<br>';
                            }
                        }
                
                        $html .= '<div class="d-flex align-items-center mt-2">';
                        if (count($notes) > $limit) {
                            $html .= '<button class="btn btn-xs btn-outline-danger mr-1" onclick="toggleNotes(' . $campaign->id . ')">Xem thêm</button>';
                        }
                        $html .= '<a class="btn btn-xs btn-outline-primary" target="_blank" href='.route('campaigns.listNote',['campaign'=>$campaign->id]).' ">Chi tiết </a>';
                        $html .= '</div>';
                
                        if (count($notes) > $limit) {
                            $html .= '<div id="fullNotes' . $campaign->id . '" style="display:none;">';
                            foreach (array_slice($notes, $limit) as $note) {
                                $html .= '👉 ' . e($note) . '<br>';
                            }
                            $html .= '</div>';
                        }
                
                        return $html;
                    }
                    return '';
                })
                ->addColumn('action', function ($campaign) {
                    $user = Auth::user();
                    $buttons = '<div class="d-flex justify-content-center align-items-center">';

                    $buttons .= '<a target="_blank" href="' . route('campaigns.show', $campaign->id) . '" class="btn btn-info btn-sm mx-1"><i class="fas fa-eye"></i></a>';
                    $buttons .= '<button class="btn btn-warning btn-sm mx-1" onclick="openNoteModal(' . $campaign->id . ')"><i class="fas fa-sticky-note"></i></button>';

                    if ($user && !$user->hasRole('saler')) {
                        $buttons .= '<form action="' . route('campaigns.destroy', $campaign->id) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm mx-1" onclick="return confirm(\'Xóa chiến dịch này?\')"><i class="fas fa-trash"></i></button>
                        </form>';
                    }

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['status', 'website_name', 'duration', 'budget_payment', 'renew', 'note', 'action'])
                ->toJson();
        }

        return view('campaigns.list');
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
        // Lấy dữ liệu Campaign theo ID, kèm thông tin website
        $campaign = Campaign::with('website')->findOrFail($id);
        if (request()->ajax()) {
            return response()->json($campaign);
        }
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
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật chiến dịch thành công !',
                'website_id' => $campaign->website->id,
                'redirect_url' => route('campaigns.show', $campaign->id),
            ]);
        }
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
                    return number_format($row->budget);
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
                ->rawColumns(['action', 'calu'])
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
