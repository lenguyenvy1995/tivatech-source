<?php

namespace App\Http\Controllers;

use App\Services\GoogleAdsService;
use App\Services\GoogleAdsAccountService;
use Illuminate\Http\Request;
use Google\Ads\GoogleAds\Lib\V17\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V17\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;

use Google\Ads\GoogleAds\V17\\Enums\AdNetworkTypeEnum\AdNetworkType;
use Google\Ads\GoogleAds\V17\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V17\Services\SearchGoogleAdsRequest;
use Google\Ads\GoogleAds\V17\Services\GoogleAdsServiceClient;
use Google\ApiCore\ApiException;

class GoogleAdsController extends Controller
{

    protected $googleAdsService;
    protected $googleAdsAccountService;
    protected $googleAdsClient;

    public function __construct(GoogleAdsService $googleAdsService)
    {
        $this->googleAdsService = $googleAdsService;
        // Tạo Google Ads API client với thông tin xác thực từ .env
        $this->googleAdsClient = (new GoogleAdsClientBuilder())
            ->withDeveloperToken(env('GOOGLE_ADS_DEVELOPER_TOKEN'))
            ->withOAuth2Credential((new OAuth2TokenBuilder())
                ->withClientId(env('GOOGLE_ADS_CLIENT_ID'))
                ->withClientSecret(env('GOOGLE_ADS_CLIENT_SECRET'))
                ->withRefreshToken(env('GOOGLE_ADS_REFRESH_TOKEN'))
                ->build())
            ->withLoginCustomerId(env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'))
            ->build();
    }
    /**
     * Tạo một tài khoản khách hàng mới dưới MCC
     */
	public function getTopAds(Request $request)
    {
       $this->googleAdsService->getTopAds();
		return 'thành công';
    }
    public function createCustomer(Request $request)
    {

        // Validate dữ liệu form
        $request->validate([
            'quantity' => 'required|integer|min:1|max:500',
            'account_name' => 'required|string|max:255',
            'currency' => 'required|string',
            'time_zone' => 'required|string',
        ]);
        for ($i = 0; $i < $request->quantity; $i++) {
            // Lấy tên tài khoản từ request, hoặc dùng tên mặc định
            $descriptiveName = $request->input('account_name') ? $request->input('account_name') . ' ' . now() : 'New Customer ' . now();
            // Gọi service để tạo tài khoản khách hàng mới
            $this->googleAdsService->createCustomerAccount($descriptiveName, $request->currency, $request->time_zone);
        }

        return back()->with('success', 'CHÚC MỪNG BẠN ĐÃ TẠO THÀNH CÔNG TÀI KHOẢN !!!');
    }
    public function createAll(Request $request)
    {
        // // Validate dữ liệu đầu vào
        $validatedData = $request->validate([
            'account_ids' => 'required|string',
            'campaign_name' => 'required|string|max:100',
            'budget' => 'required|numeric|min:0.05',
            'advertising_channel_type' => 'required|string',
            'adgroup_name' => 'required|string|max:100',
            'cpc_bid' => 'required|numeric|min:0.5',
            'status' => 'required|string',
            'ad_headline' => 'required|array|min:3',
            'ad_headline.*' => 'nullable|string|max:30',
            'ad_description' => 'array|min:2',
            'ad_description.*' => 'nullable|string|max:90',
            'final_url' => 'required|url',
        ], [
            'ad_headline.min' => 'Bạn phải nhập ít nhất 3 tiêu đề quảng cáo.',
            'ad_description.min' => 'Bạn phải nhập ít nhất 2 mô tả quảng cáo.',
        ]);
        // Lọc và chuẩn hóa các giá trị tiêu đề và mô tả quảng cáo
        $filteredheadlines = array_filter($validatedData['ad_headline'], fn($ad_headline) => !is_null($ad_headline) && $ad_headline !== '');
        $request->merge(['ad_headline' => $filteredheadlines]);
        $filteredDescriptions = array_filter($validatedData['ad_description'], fn($description) => !is_null($description) && $description !== '');
        $request->merge(['ad_description' => $filteredDescriptions]);
        // Khởi tạo một mảng để lưu trữ kết quả xử lý cho từng tài khoản
        // Lấy danh sách các account_id từ textarea, mỗi dòng là một account_id
        $accountIds = array_filter(array_map('trim', explode("\n", $request->input('account_ids'))));

        // Khởi tạo một mảng để lưu trữ kết quả xử lý cho từng tài khoản
        $results = [];

        // Lặp qua từng account_id và tạo chiến dịch cho từng tài khoản
        foreach ($accountIds as $accountId) {
            $item = $this->googleAdsService->createFullCampaign($request, $accountId);
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
