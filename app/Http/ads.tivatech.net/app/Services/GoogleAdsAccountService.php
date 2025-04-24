<?php

namespace App\Services;

use Google\Ads\GoogleAds\Lib\V17\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\V17\Services\CreateCustomerClientRequest;
use Google\Ads\GoogleAds\V17\Resources\Customer;
use Google\Ads\GoogleAds\V17\Resources\Campaign;
use Google\Ads\GoogleAds\V17\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V17\Enums\AdvertisingChannelTypeEnum\AdvertisingChannelType;
use Google\Ads\GoogleAds\V17\Services\CampaignOperation;
use Google\Ads\GoogleAds\V17\Common\ManualCpc;
use Google\Ads\GoogleAds\V17\Enums\BudgetDeliveryMethodEnum\BudgetDeliveryMethod;
use Google\Ads\GoogleAds\V17\Resources\Campaign\NetworkSettings;
use Google\Ads\GoogleAds\V17\Resources\CampaignBudget;
use Google\Ads\GoogleAds\V17\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V17\Services\MutateCampaignCriteriaRequest;
use Google\Ads\GoogleAds\V17\Services\MutateCampaignBudgetsRequest;
use Google\Ads\GoogleAds\V17\Resources\CampaignCriterion;
use Google\Ads\GoogleAds\V17\Services\CampaignCriterionOperation;
use Google\Ads\GoogleAds\V17\Common\LanguageInfo;
use Google\Ads\GoogleAds\V17\Resources\AdGroup;
use Google\Ads\GoogleAds\V17\Services\AdGroupOperation;
use Google\Ads\GoogleAds\V17\Enums\AdGroupStatusEnum\AdGroupStatus;
use Google\Ads\GoogleAds\V17\Enums\AdGroupCriterionStatusEnum\AdGroupCriterionStatus;
use Google\Ads\GoogleAds\V17\Services\MutateAdGroupsRequest;
use Google\Ads\GoogleAds\V17\Resources\AdGroupCriterion;
use Google\Ads\GoogleAds\V17\Common\KeywordInfo;
use Google\Ads\GoogleAds\V17\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V17\Services\AdGroupCriterionOperation;
use Google\Ads\GoogleAds\V17\Services\MutateAdGroupCriteriaRequest;
use Google\Ads\GoogleAds\V17\Resources\AdGroupAd;
use Google\Ads\GoogleAds\V17\Resources\Ad;
use Google\Ads\GoogleAds\V17\Services\AdGroupAdOperation;
use Google\Ads\GoogleAds\V17\Services\MutateAdGroupAdsRequest;
use Google\Ads\GoogleAds\V17\Enums\AdGroupAdStatusEnum\AdGroupAdStatus;
use Google\Ads\GoogleAds\V17\Common\ResponsiveSearchAdInfo;
use  Google\Ads\GoogleAds\V17\Common\AdTextAsset;
use Illuminate\Http\Request;
use Google\Ads\GoogleAds\V17\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\Lib\V17\GoogleAdsException;
use Google\Ads\GoogleAds\Lib\V17\GoogleAdsFailure;
use Google\ApiCore\ApiException;
use Google\Ads\GoogleAds\V17\Services\SearchGoogleAdsRequest;
use Google\Ads\GoogleAds\V17\Enums\CustomerStatusEnum\CustomerStatus;

class GoogleAdsAccountService
{

    protected $googleAdsClient;
    public function __construct()
    {
        // Tạo Google Ads API client với thông tin xác thực từ .env
        $this->googleAdsClient = (new GoogleAdsClientBuilder())
            ->withDeveloperToken(env('GOOGLE_ADS_DEVELOPER_TOKEN'))
            ->withOAuth2Credential((new OAuth2TokenBuilder())
                ->withClientId(env('GOOGLE_ADS_CLIENT_ID'))
                ->withClientSecret(env('GOOGLE_ADS_CLIENT_SECRET'))
                ->withRefreshToken(env('GOOGLE_ADS_REFRESH_TOKEN'))
                ->build())
            ->withLoginCustomerId(env('GOOGLE_ADS_LOGIN_CUSTOMER_ID')) // 
            ->build();
    }
    public function checkAccountStatus($customerId)
    {
        $customerId = str_replace(['-', '.', '_'], '', $customerId);
        try {
            // Khởi tạo Google Ads Service Client cho v17
            $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();
            // Tạo đối tượng SearchGoogleAdsRequest
            $req = new SearchGoogleAdsRequest([
                'customer_id' => $customerId,
                'query' => "SELECT customer.id, customer.status, customer.descriptive_name FROM customer WHERE customer.id = $customerId"
            ]);
            $response = $googleAdsServiceClient->search($req);
            foreach ($response->iterateAllElements() as $googleAdsRow) {
                $customer = $googleAdsRow->getCustomer();
                $status = $customer->getStatus();
                // So sánh với các hằng số được định nghĩa trong enum
                switch ($status) {
                    case CustomerStatus::ENABLED:
                        echo "Tài khoản đang hoạt động.";
                        break;
                    case CustomerStatus::SUSPENDED:
                        echo "Tài khoản đang bị tạm dừng.";
                        break;
                    case CustomerStatus::CANCELED:
                        echo "Tài khoản đã bị xóa hoặc không còn tồn tại.";
                        break;
                    default:
                        echo "Trạng thái tài khoản không xác định.";
                        break;
                }
                if ($status !=  CustomerStatus::ENABLED) {
                    // Tài khoản không ở trạng thái hoạt động
                    // So sánh với các hằng số được định nghĩa trong enum
                    switch ($status) {
                        case CustomerStatus::ENABLED:
                            $status = "Tài khoản đang hoạt động.";
                            break;
                        case CustomerStatus::SUSPENDED:
                            $status = "Tài khoản đang bị tạm dừng.";
                            break;
                        case CustomerStatus::CANCELED:
                            $status = "Tài khoản đã bị xóa hoặc không còn tồn tại.";
                            break;
                        default:
                            $status = "Trạng thái tài khoản không xác định.";
                            break;
                    }
                    return [
                        'status' => 'error',
                        'errors' => ["Tài khoản $customerId không hoạt động hoặc bị vi phạm chính sách. Trạng thái hiện tại: $status"],
                    ];
                }
            }
            return [
                'status' => 'success',
                'message' => ["Tài khoản $customerId đang hoạt động bình thường."],
            ];
        } catch (GoogleAdsException $googleAdsException) {
            // Bắt lỗi chi tiết từ Google Ads API
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                $errorCode = $error->getErrorCode()->getErrorCode();
                $errorMessage = $error->getMessage();
                echo "Error Code: $errorCode. Message: $errorMessage\n";
            }

            return [
                'status' => 'error',
                'errors' => ["Đã xảy ra lỗi khi kiểm tra trạng thái tài khoản $customerId."],
            ];
        } catch (GoogleAdsException $googleAdsException) {
            // Thu thập lỗi chi tiết từ Google Ads
            $errors = [];
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                /** @var GoogleAdsError $error */
                $errorMessage = $error->getMessage();
                $errorCode = $error->getErrorCode()->getErrorCode();
                $errors[] = "Mã lỗi tạo tài khoản: $errorCode - Thông báo: $errorMessage";
            }
            return [
                'status' => 'error',
                'errors' => $errors,
            ];
        } catch (ApiException $apiException) {
            // Bắt lỗi API chung và hiển thị lỗi cho người dùng
            return [
                'status' => 'error',
                'errors' => ['Không thể truy cập tài khoản khách hàng vì tài khoản này chưa được kích hoạt hoặc đã bị vô hiệu hóa'],
            ];
        } catch (\Exception $exception) {
            // Bắt các ngoại lệ chung khác (nếu có)
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi khi kết nối với 12 Google Ads API: ' . $exception->getMessage()],
            ];
        }
    }

}