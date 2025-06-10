<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CampaignController;
use Google\Client;
use Google\Service\Sheets;
use App\Services\GoogleSheetService;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use App\Services\ZaloService;

class AdminController extends Controller
{
    /**
     * Hiển thị Dashboard Admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $totalBudgetsYesterday = '';
        $CampaignController = new CampaignController;
        if (Auth::user()->hasRole("admin")) {
            $campUnpaid = Campaign::where("paid", 0)->count();
            $monthlySales = $CampaignController::getMonthlySales(now()->month, now()->year);
            $getMonthlySalesTypeCamp2 = $CampaignController::getMonthlySalesTypeCamp2(); // Tính tổng doanh số cho tháng hiện tại

        } else {
            $campUnpaid = Campaign::where("user_id", Auth::user()->id)->where("paid", 0)->count();
            $monthlySales = $CampaignController::getMonthlySales(now()->month, now()->year);
            $getMonthlySalesTypeCamp2 = $CampaignController::getMonthlySalesTypeCamp2(); // Tính tổng doanh số cho tháng hiện tại
        }
        return view('admin.dashboard', compact('monthlySales', 'getMonthlySalesTypeCamp2', 'campUnpaid'));
    }
    // Trong Controller
    public function getMonthlySalesData(Request $request)
    {
        // Nhận giá trị từ `monthYearPicker` và tách thành tháng và năm
        $monthYear = $request->input('monthYearPicker', now()->format('m-Y'));
        list($month, $year) = explode('-', $monthYear);
        // Chuyển đổi giá trị thành số nguyên để đảm bảo đúng kiểu
        $year = (int)$year;
        $userId = $request->user_id ?? Auth::id();

        $CampaignController = new CampaignController;
        $monthlySales = []; // Khởi tạo mảng doanh số
        // Giả sử bạn có dữ liệu doanh số trong bảng sales hoặc logic để tính doanh số
        for ($month = 1; $month <= 12; $month++) {
            $monthlySales[] = [
                'month' => "Tháng $month",
                'sales' => $CampaignController::getMonthlySales($month, $year, $userId) + $CampaignController::getMonthlySalesTypeCamp2($month, $year, $userId),
            ];
        }

        return response()->json($monthlySales);
    }
    // hiệu suất cập nhật trong ngày
    public function intradayPerformance(Request $request)
    {
        $spreadsheetId = '1AlCXcv1KEdoQZK5N68HxzLoQ2No8Q4PyuDdNNUcgIKE'; // ID Google Sheet của bạn
        $range = 'cost_now'; // Lấy toàn bộ sheet
        $apiKey = env('GOOGLE_SHEET_API_KEY'); // API Key từ file .env
        if ($request->ajax()) {
            $GoogleSheetService = new GoogleSheetService;
            $data = $GoogleSheetService->fetchWithApiKey($spreadsheetId, $range, $apiKey);

            // Lấy header row và dữ liệu
            $header = [];
            $rows = [];
            $aggregatedData = [];

            if (isset($data['values']) && count($data['values']) > 0) {
                $header = array_map('strtolower', $data['values'][0]); // Row đầu tiên làm header
                $dataRows = array_slice($data['values'], 1); // Dữ liệu còn lại
                $idCounter = 1; // Bộ đếm ID

                foreach ($dataRows as $row) {
                    $combinedRow = array_combine(
                        $header, // Header làm key
                        array_pad($row, count($header), null) // Giá trị cho từng key, tự động thêm null nếu thiếu
                    );

                    // Phân tích chuỗi campaign để lấy budget
                    if (preg_match('/^(?:ns\.)?(.*?)\/(\d+)(?:_\((\d+)\))?(?:\s[#-].+)?$/', $combinedRow['campaign'], $matches)) {
                        $combinedRow['domain'] = $matches[1];
                        $combinedRow['budget'] = (int) $matches[2] * 1000; // Budget từ chuỗi campaign
                        $combinedRow['position'] = (int) $matches[3];
                    } else {
                        $combinedRow['domain'] = null;
                        $combinedRow['budget'] = 0;
                        $combinedRow['position'] = null;
                    }

                    // Chuyển đổi cost nếu currency là '$'
                    $convertedCost = isset($combinedRow['cost']) && isset($combinedRow['currency']) && $combinedRow['currency'] === '$'
                        ? floatval(str_replace(',', '.', $combinedRow['cost'])) * 27500
                        : (float)$combinedRow['cost'];

                    $combinedRow['converted_cost'] = $convertedCost; // Lưu cost sau khi chuyển đổi

                    // Gộp theo domain
                    $domain = $combinedRow['domain'];
                    if ($domain) {
                        if (!isset($aggregatedData[$domain])) {
                            // Nếu domain chưa tồn tại, khởi tạo
                            $aggregatedData[$domain] = [
                                'idCounter' => $idCounter++, // Thêm ID tự động tăng
                                'domain' => $domain,
                                'budget' => 0,
                                'converted_cost' => 0,
                                'impressions' => 0,
                                'clicks' => 0,
                                'accounts' => [] // Thêm mảng accounts để lưu trữ tên account

                            ];
                        }

                        // Cộng gộp dữ liệu
                        $aggregatedData[$domain]['budget'] = $combinedRow['budget'];
                        $aggregatedData[$domain]['converted_cost'] += $combinedRow['converted_cost'];
                        $aggregatedData[$domain]['impressions'] += (int) ($combinedRow['impressions'] ?? 0);
                        $aggregatedData[$domain]['clicks'] += (int) ($combinedRow['clicks'] ?? 0);
                        // Thêm accountName vào mảng accounts nếu chưa tồn tại
                        if (!in_array($combinedRow['account'], $aggregatedData[$domain]['accounts'])) {
                            $aggregatedData[$domain]['accounts'][] = substr($combinedRow['account'], 0, 15);
                        }
                    }

                    // // Gửi email nếu campaign chứa ns. và budget < converted_cost
                    // if (str_contains($combinedRow['campaign'], 'ns.') && $combinedRow['budget'] < $combinedRow['converted_cost']) {
                    //     $zaloService = new ZaloService;
                    //     $message = str_replace('<br>', "\n",'Thông báo vượt ngân sách');
                    //     $zaloService->sendMessage('8825240549062391828', $message);

                    // }
                }
            }
            // Chuẩn bị dữ liệu cho Datatable
            $rows = array_map(function ($item) {
                // Lấy giờ hiện tại
                $currentHour = Carbon::now()->hour;

                // Điều kiện trước và sau 13 giờ
                $threshold = $currentHour < 14
                    ? $item['budget'] / 2
                    : $item['budget'];

                // Kiểm tra nếu converted_cost >= threshold
                if (isset($item['converted_cost']) && $item['converted_cost'] >= $threshold) {
                    // Kết hợp tất cả accountName và lấy 15 ký tự đầu
                    $item['account'] = substr(implode(' --- ', $item['accounts']), 0, 15);
                } else {
                    // Loại bỏ những item không thỏa mãn điều kiện
                    return null;
                }

                // Loại bỏ mảng accounts sau khi xử lý
                unset($item['accounts']);

                return $item;
            }, array_values($aggregatedData));

            // Loại bỏ các giá trị null sau khi xử lý
            $rows = array_filter($rows);


            return DataTables::of($rows)
                ->addColumn('account', function ($row) {
                    return $row['account'];
                })
                ->editColumn('domain', function ($row) {
                    return $row['domain'];
                })
                ->editColumn('budget', function ($row) {
                    return number_format($row['budget']);
                })
                ->editColumn('converted_cost', function ($row) {
                    return number_format($row['converted_cost']);
                })
                ->editColumn('impressions', function ($row) {
                    return $row['impressions'];
                })
                ->editColumn('clicks', function ($row) {
                    return $row['clicks'];
                })
                ->make(true);
        }
        return view('admin.intradayPerformance');
    }
    //hiệu suất và chi phí chạy theo ngày
    public function datePerformance(Request $request)
    {
        $date = $request->has('date') ? Carbon::parse($request->date)->format('Y-m-d') : Carbon::today()->toDateString();

        if ($request->ajax()) {
            $techId = $request->input('tech_id');

            $campaignsQuery = Campaign::with([
                'website',
                'budgets' => function ($query) use ($date) {
                    $query->whereDate('date', $date);
                },
                'budgetsAll',
                'user',
                'tech'
            ])
            ->whereIn('status_id', [1, 2])
            ->whereHas('budgets', function ($q) use ($date) {
                $q->whereDate('date', $date);
            });

            if ($techId) {
                $campaignsQuery->where('tech_id', $techId);
            }

            $campaigns = $campaignsQuery->get();

            $campaigns->each(function ($campaign) {
                $actualCost = $campaign->budgetsAll->sum('budget');
                $daysRan = $campaign->budgetsAll->where('calu', '<>', 0)->sum('calu');
                $expectedCost = ($campaign->budgetmonth / 30) * $daysRan;
                $campaign->total_profit = $expectedCost > 0 ? 100 - intval($actualCost / $expectedCost * 100) : 0;
            });

            return DataTables::of($campaigns)
                ->addIndexColumn()
                ->addColumn('website_name', fn($c) => '<a href="' . route('campaigns.budgets', $c->id) . '" target="_blank">' . $c->website->name . '</a><br><small>' . $c->tech?->fullname??''. '</small>')
                ->addColumn('budget', fn($c) => $c->budgetmonth / 30)
                ->addColumn('cost', fn($c) => '<span data-toggle="tooltip" data-placement="top" title="' . $c->budgets->pluck('account')->unique()->first() . '">' . number_format($c->budgets->sum('budget')) . '</span>')
                ->addColumn('profit', fn($c) => $c->budgetmonth ? intval((($c->budgetmonth / 30 - $c->budgets->sum('budget')) / ($c->budgetmonth / 30)) * 100) . ' %' : '0 %')
                ->addColumn('total_profit', fn($c) => $c->total_profit)
                ->addColumn('expired', function ($c) {
                    $res = '<div ';
                    if ($c->typecamp_id == 1) {
                        $res .= 'class="callout callout-info p-1 m-0 text-center">';
                        $start = $c->start ? Carbon::parse($c->start) : null;
                        $end = $c->end ? Carbon::parse($c->end) : null;
                        if ($start && $end) {
                            $days = $start->diffInDays($end) + 1 ?: 1;
                            $budgetCount = $c->budgetsAll->sum('calu');
                            $remainingDays = $days - $budgetCount;
                            if ($budgetCount + 1 >= $days) {
                                $res .= '<h5><span class="badge badge-pill badge-' . ($budgetCount > $days ? 'danger' : 'warning') . '">Hết Hạn ' . $remainingDays . ' Ngày </span></h5>';
                            } else {
                                $res .= $remainingDays . ' Ngày <br>';
                            }
                            $res .= $budgetCount . ' / ' . $days;
                        }
                    } elseif ($c->typecamp_id == 2) {
                        $res .= 'class="callout callout-danger m-0 p-1 text-center">';
                        $totalBudget = $c->budgetsAll->sum('budget');
                        $remainingBudget = $c->payment - $totalBudget;
                        $threshold = $c->budgetmonth / 30 + ($c->budgetmonth / 30 / 2);
                        $res .= number_format($remainingBudget <= $threshold ? $remainingBudget : $c->payment - $totalBudget);
                    }
                    return $res . '</div>';
                })
                ->addColumn('saler', fn($c) => $c->user->fullname ?? 'N/A')
                ->addColumn('warning', function ($c) use ($date) {
                    $revenue = $c->budgetmonth ? $c->budgetmonth / 30 : 0;
                    $cost = $c->budgets->sum('budget');
                    $profit = ($revenue > 0) ? intval(100 - ($cost / $revenue) * 100) : 0;

                    $actualCost = $c->budgetsAll->sum('budget');
                    $daysRan = $c->budgetsAll->where('calu', '<>', 0)->sum('calu');
                    $expectedCost = ($c->budgetmonth / 30) * $daysRan;
                    $totalProfit = ($expectedCost > 0) ? intval(100 - ($actualCost / $expectedCost) * 100) : 0;

                    if ($profit < 0 && $totalProfit < 0) {
                        return '<span class="badge bg-danger">Cần Giảm Chi Phí</span>';
                    } elseif ($profit < 0) {
                        return '<span class="badge bg-warning">Giảm Ngân Sách</span>';
                    } elseif ($totalProfit < 0) {
                        return '<span class="badge bg-info">Tăng Lợi Nhuận</span>';
                    }
                    return '<span class="badge bg-success">Hoạt Động Tốt</span>';
                })
                ->rawColumns(['cost', 'warning', 'website_name', 'expired'])
                ->toJson();
        }

        return view('admin.datePerformance');
    }
    public function getWeeklyBudgetComparison()
    {
        $startDate = Carbon::yesterday()->subDays(6); // Bắt đầu từ 7 ngày trước (bao gồm hôm qua)
        $endDate = Carbon::yesterday(); // Kết thúc là ngày hôm qua
        $weeklyData = [];
        if (Auth::user()->hasRole("admin")) {
            // Lặp qua từng ngày từ 7 ngày trước đến hôm qua
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                // Tổng ngân sách cho ngày hiện tại
                $budgets = Budget::whereDate('date', $date)->get();
                $totalBudgets = $budgets->sum('budget');

                // Trung bình ngân sách hàng ngày
                $averageDailyBudget = $budgets->sum('budgetday');

                $weeklyData[] = [
                    'date' => $date->format('Y-m-d'), // Ngày
                    'totalBudgets' => $totalBudgets, // Tổng ngân sách
                    'averageDailyBudget' => $averageDailyBudget, // Trung bình ngân sách
                ];
            }
        }

        return response()->json($weeklyData);
    }
}
