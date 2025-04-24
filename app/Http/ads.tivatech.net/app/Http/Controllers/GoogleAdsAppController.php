<?php

namespace App\Http\Controllers;

use App\Services\GoogleAdsAppService;
use Illuminate\Http\Request;

class GoogleAdsAppController extends Controller
{
    protected $googleAdsAppService;

    public function __construct(GoogleAdsAppService $googleAdsAppService)
    {
        $this->googleAdsAppService = $googleAdsAppService;
    }

    // Hiển thị form tạo App Campaign
    public function create()
    {
        return view('admin.google-ads.app-campaign.create');
    }

    // Xử lý việc tạo App Campaign
    public function createAppCampaign(Request $request)
    {
        $validatedData = $request->validate([
            'account_ids' => 'required|string',
            'name' => 'required|string|max:255',
            'budget_amount_micros' => 'required|numeric|min:0.5', // Ngân sách tối thiểu là 0.5 USD
            // Các quy tắc khác cho dữ liệu App Campaign...
        ]);
        $accountIds = array_filter(array_map('trim', explode("\n", $request->input('account_ids'))));
        // Khởi tạo một mảng để lưu trữ kết quả xử lý cho từng tài khoản
        $results = [];

        // Lặp qua từng account_id và tạo chiến dịch cho từng tài khoản
        foreach ($accountIds as $accountId) {
            $item = $this->googleAdsAppService->createAppCampaign($request, $accountId);
            if ($item['status'] == 'success') {
                $results[] = [
                    'account_id' => $accountId,
                    'status' => 'success',
                    'message' => $item['message']
                ];
            } else {
                $results[] = [
                    'account_id' => $accountId,
                    'status' => 'error',
                    'errors' => $item['errors'] ?? ['Đã xảy ra lỗi không xác định.']
                ];
            }
        }

        // Kiểm tra xem có lỗi nào không
        $hasErrors = array_filter($results, fn($result) => $result['status'] == 'error');

        if ($hasErrors) {
            // Tạo thông báo lỗi
            $errorMessages = [];
            foreach ($hasErrors as $error) {
                $errorMessages[] = "Tài khoản {$error['account_id']}: " . implode(', ', $error['errors']);
            }
            return redirect()->back()->withInput()->withErrors($errorMessages);
        }
        return redirect()->back()->withInput()->with('success', $item['message']);
    }
}