<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\Budget;
use App\Models\Website;
use App\Models\Status;
use Carbon\Carbon;
use App\Services\GoogleSheetService;
use App\Services\ZaloService;

class BudgetController extends Controller
{

    public function store(Request $request)
    {
        Budget::create($request->all());
        return response()->json(['success' => 'Budget added successfully']);
    }

    public function edit($id)
    {
        $budget = Budget::findOrFail($id);
        return response()->json($budget);
    }

    public function update(Request $request, $id)
    {
        $budget = Budget::findOrFail($id);
        $budget->update($request->all());
        return response()->json(['success' => 'Budget updated successfully']);
    }

    public function destroy($id)
    {
        $budget = Budget::findOrFail($id);
        $budget->delete();
        return response()->json(['success' => 'Budget deleted successfully']);
    }
    public function updateCalu(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:budgets,id',
            'calu' => 'required|in:0,0.5,1'
        ]);

        $budget = Budget::findOrFail($request->id);
        $budget->calu = $request->calu;
        $budget->save();

        return response()->json(['message' => 'Cập nhật giá trị calu thành công']);
    }

    public function importFromGoogleSheets(Request $request)
    {

        $spreadsheetId = '19jJk8Awy_n56E7ZlAEnLAkeWvE_q-_5jnd_xomHfuvo'; // Thay bằng ID của Google Sheet
        $range = 'cost'; // Thay bằng phạm vi dữ liệu bạn muốn lấy
        $apiKey =  env('GOOGLE_SHEET_API_KEY'); // Thay bằng API Key của bạn
        $failedRows = []; // Lưu các dòng không hợp lệ

        $GoogleSheetService = new GoogleSheetService;
        $data = $GoogleSheetService->fetchWithApiKey($spreadsheetId, $range, $apiKey);
        if (count($data['values']) < 2) {
            throw new \Exception('Không có dữ liệu để nhập.');
        }
        // Lấy header (dòng đầu tiên)
        $header = array_map('strtolower', $data['values'][0]); // Chuyển header về chữ thường
        $rows = array_slice($data['values'], 1); // Lấy các dòng dữ liệu trừ header
        // Xử lý dữ liệu (giả sử cột: campaign_id, budget, calu)
        foreach ($rows as $row) {
            $row = array_pad($row, count($header), null);
            $combinedRow = array_combine($header, $row);

            $domain = null;
            if (isset($combinedRow['campaign']) && preg_match('/^(?:ns\.)?(.*?)\/(\d+)(?:_\((\d+)\))?(?:\s[#-].+)?$/', $combinedRow['campaign'], $matches)) {
                $domain = $matches[1] ?? null;
                if ($domain) {
                    // Xử lý giá trị 'cost' nếu tồn tại
                    if (isset($combinedRow['cost'])) {
                        // Thay dấu phẩy thành dấu chấm
                        $combinedRow['cost'] = str_replace(',', '.', $combinedRow['cost']);
                    }
                    // Xử lý currency $ -> VND
                    if (isset($combinedRow['currency']) && strtolower($combinedRow['currency']) === '$') {
                        $combinedRow['cost'] = $combinedRow['cost'] * 25500;
                    }
                    // Xử lý currency R$ -> VND
                    if (isset($combinedRow['currency']) && strtolower($combinedRow['currency']) === 'R$') {
                        $combinedRow['cost'] = $combinedRow['cost'] * 5500;
                    }
                    // Kiểm tra domain đã tồn tại trong mảng tạm hay chưa
                    if (isset($domains[$domain])) {
                        // Gộp cost và account
                        $domains[$domain]['cost'] += $combinedRow['cost'];
                        $domains[$domain]['account'] .= ',' . $combinedRow['account'];
                    } else {
                        // Lưu domain vào mảng tạm
                        $domains[$domain] = [
                            'cost' => $combinedRow['cost'],
                            'account' => $combinedRow['account'],
                        ];
                    }
                }
            } else {
                $failedRows[] = [
                    'row' => $combinedRow['campaign'],
                    'reason' => 'Sai cấu trúc chiến dịch',
                ];
            }
        }
        // Lưu các domain đã xử lý vào cơ sở dữ liệu
        foreach ($domains as $domain => $data) {
            $website = Website::where('name', $domain)->first();
            if ($website) {
                $campaign = Campaign::where('website_id', $website->id)
                    ->whereIn('status_id', [1, 2])
                    ->first();
                if ($campaign) {
                    if (isset($combinedRow['date']) && strtotime($combinedRow['date'])) {
                        $date = Carbon::parse($combinedRow['date'])->format('Y-m-d');
                    } else {
                        \Log::warning('Date không hợp lệ hoặc bị thiếu', ['row' => $combinedRow]);
                        continue; // Bỏ qua nếu date không hợp lệ
                    }
                    Budget::updateOrCreate(
                        [
                            'campaign_id' => $campaign->id,
                            'date' => $date, // Hoặc thay bằng ngày cụ thể
                        ],
                        [
                            'account' => $data['account'],
                            'budget' => $data['cost'],
                            'budgetday' => $campaign->budgetmonth/30,
                        ]
                    );
                } else {
                    $failedRows[] = [
                        'domain' => $domain,
                        'reason' => 'Không tìm thấy campaign phù hợp',
                    ];
                }
            } else {
                $failedRows[] = [
                    'domain' => $domain,
                    'reason' => 'Không tìm thấy website',
                ];
            }
        }
    
        \Log::warning($failedRows);

        // Kiểm tra các dòng bị lỗi
        if (!empty($failedRows)) {
            $zaloService = new ZaloService;
            $message = "Cập nhật chi phí chạy:\n";
            foreach ($failedRows as $failedRow) {
                // Kiểm tra nếu key 'row' tồn tại
                if (isset($failedRow['row'])) {
                    $message .= "Dòng: {$failedRow['row']} - Lý do: {$failedRow['reason']}\n";
                } elseif (isset($failedRow['domain'])) {
                    $message .= "Domain: {$failedRow['domain']} - Lý do: {$failedRow['reason']}\n";
                }
            }
        
            $zaloService->sendMessage('8825240549062391828', $message);
        }
        return redirect()->back()->with('success', 'Dữ liệu đã được nhập thành công!');
    }
}
