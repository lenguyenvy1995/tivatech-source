<?php
namespace App\Http\Controllers;

use Google\Ads\GoogleAds\Lib\V17\GoogleAdsClientBuilder;
use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Ads\GoogleAds\V17\Resources\Customer;
use Google\Ads\GoogleAds\V17\Services\CustomerOperation;
use Illuminate\Http\Request;

class GoogleAdsAccountController extends Controller
{
  
    public function createAccount(Request $request)
    {
        try {
            // Lấy các thông tin từ tệp .env
            $developerToken = env('GOOGLE_ADS_DEVELOPER_TOKEN');
            $clientId = env('GOOGLE_ADS_CLIENT_ID');
            $clientSecret = env('GOOGLE_ADS_CLIENT_SECRET');
            $refreshToken = env('GOOGLE_ADS_REFRESH_TOKEN');
            $loginCustomerId = env('GOOGLE_ADS_LOGIN_CUSTOMER_ID');
    
            // Cấu hình OAuth2Credential
            $oauth2Credentials = new \Google\Auth\Credentials\UserRefreshCredentials(
                ['https://www.googleapis.com/auth/adwords'],
                [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'refresh_token' => $refreshToken
                ]
            );
    
            // Tạo Google Ads Client từ các giá trị cấu hình trong .env
            $googleAdsClient = (new GoogleAdsClientBuilder())
                ->withDeveloperToken($developerToken)
                ->withOAuth2Credential($oauth2Credentials)
                ->withLoginCustomerId($loginCustomerId)
                ->build();
    
            // Tạo một đối tượng Customer (tài khoản Google Ads)
            $customer = new Customer([
                'descriptive_name' => $request->input('account_name'),
                'currency_code' => 'USD',  // Đơn vị tiền tệ
                'time_zone' => 'America/New_York'  // Múi giờ của tài khoản
            ]);
    
            // Tạo đối tượng CustomerOperation để thực hiện thao tác tạo tài khoản
            $operation = new CustomerOperation();
            $operation->setCreate($customer);
    
            // Thực hiện thao tác tạo tài khoản
            $customerServiceClient = $googleAdsClient->getCustomerServiceClient();
            $response = $customerServiceClient->mutateCustomers($operation);
    
            // Trả về kết quả JSON
            return response()->json([
                'message' => 'Tài khoản Google Ads đã được tạo thành công!',
                'account_id' => $response->getResult()->getResourceName()
            ], 200);
    
        } catch (\Exception $e) {
            // In ra lỗi cụ thể
            return response()->json([
                'error' => 'Có lỗi xảy ra: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()  // Thêm chi tiết lỗi nếu cần
            ], 500);
        }
    }
}