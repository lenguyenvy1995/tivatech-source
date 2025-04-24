<?php

namespace App\Services;

use Google\Ads\GoogleAds\Examples\Utils\ArgumentNames;
use Google\Ads\GoogleAds\Examples\Utils\ArgumentParser;
use Google\Ads\GoogleAds\Examples\Utils\Helper;
use Google\Ads\GoogleAds\Lib\V17\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V17\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\V17\GoogleAdsException;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Util\V17\ResourceNames;
use Google\Ads\GoogleAds\V17\Common\AdTextAsset;
use Google\Ads\GoogleAds\V17\Common\AppAdInfo;
use Google\Ads\GoogleAds\V17\Common\LanguageInfo;
use Google\Ads\GoogleAds\V17\Common\LocationInfo;
use Google\Ads\GoogleAds\V17\Enums\AdGroupAdStatusEnum\AdGroupAdStatus;
use Google\Ads\GoogleAds\V17\Enums\AdGroupStatusEnum\AdGroupStatus;
use Google\Ads\GoogleAds\V17\Enums\AppCampaignBiddingStrategyGoalTypeEnum\AppCampaignBiddingStrategyGoalType;
use Google\Ads\GoogleAds\V17\Common\TargetCpa;
use Google\Ads\GoogleAds\V17\Enums\AdvertisingChannelSubTypeEnum\AdvertisingChannelSubType;
use Google\Ads\GoogleAds\V17\Enums\AdvertisingChannelTypeEnum\AdvertisingChannelType;
use Google\Ads\GoogleAds\V17\Enums\AppCampaignAppStoreEnum\AppCampaignAppStore;
use Google\Ads\GoogleAds\V17\Enums\BudgetDeliveryMethodEnum\BudgetDeliveryMethod;
use Google\Ads\GoogleAds\V17\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V17\Enums\CriterionTypeEnum\CriterionType;
use Google\Ads\GoogleAds\V17\Errors\GoogleAdsError;
use Google\Ads\GoogleAds\V17\Resources\Ad;
use Google\Ads\GoogleAds\V17\Resources\AdGroup;
use Google\Ads\GoogleAds\V17\Resources\AdGroupAd;
use Google\Ads\GoogleAds\V17\Resources\Campaign;
use Google\Ads\GoogleAds\V17\Resources\Campaign\AppCampaignSetting;
use Google\Ads\GoogleAds\V17\Resources\CampaignBudget;
use Google\Ads\GoogleAds\V17\Resources\CampaignCriterion;
use Google\Ads\GoogleAds\V17\Services\AdGroupAdOperation;
use Google\Ads\GoogleAds\V17\Services\AdGroupOperation;
use Google\Ads\GoogleAds\V17\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V17\Services\CampaignCriterionOperation;
use Google\Ads\GoogleAds\V17\Services\CampaignOperation;
use Google\Ads\GoogleAds\V17\Services\MutateAdGroupAdsRequest;
use Google\Ads\GoogleAds\V17\Services\MutateAdGroupsRequest;
use Google\Ads\GoogleAds\V17\Services\MutateCampaignBudgetsRequest;
use Google\Ads\GoogleAds\V17\Services\MutateCampaignCriteriaRequest;
use Google\Ads\GoogleAds\V17\Services\MutateCampaignsRequest;
use Google\ApiCore\ApiException;
use Google\Ads\GoogleAds\V17\Services\SearchGoogleAdsRequest;
use Illuminate\Http\Request;
use App\Services\GoogleAdsAccountService;


class GoogleAdsAppService
{
    protected $googleAdsClient;
    protected $googleAdsAccountService;

    public function __construct(GoogleAdsAccountService $googleAdsAccountService)
    {
        $this->googleAdsAccountService = $googleAdsAccountService;

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
     * Tạo chiến dịch App Campaign.
     */
    public function createAppCampaign(Request $request, $customerId)
    {
        // try {
        $checkAcc = $this->googleAdsAccountService->checkAccountStatus($customerId);

        if ($checkAcc['status'] == 'success') {
            $customerId = str_replace(['-', '.', '_'], '', $customerId);
            // Validate dữ liệu đầu vào từ form
            $campaignData = $request->validate([
                'name' => 'required|string|max:255',
                'budget_amount_micros' => 'required|numeric|min:0.5',
                'start_date' => 'required|date',
                'app_id' => 'required|string|max:255',
                'app_store' => 'required|string|in:GOOGLE_APP_STORE,APPLE_APP_STORE',
            ]);
            // Sử dụng hàm để tạo tên chiến dịch duy nhất
            $campaignData['name'] = $this->createUniqueCampaignName($customerId, $campaignData['name']);
            // Tạo ngân sách chiến dịch
            $budgetResourceName = $this->createBudget($customerId, $campaignData['budget_amount_micros']);

            // Tạo App Campaign
            $campaignResourceName = $this->createCampaign($customerId, $budgetResourceName, $campaignData);

            // Đặt tiêu chí nhắm mục tiêu cho chiến dịch
            $this->setCampaignTargetingCriteria($customerId, $campaignResourceName);

            // Tạo nhóm quảng cáo
            $adGroupResourceName = $this->createAdGroup($customerId, $campaignResourceName);

            // Tạo quảng cáo App
            $this->createAppAd($customerId, $adGroupResourceName);

            return [
                'status' => 'success',
                'message' => "Chiến dịch App Campaign đã được tạo thành công.",
            ];
        } else {
            return $checkAcc;
        }
        // } catch (GoogleAdsException $googleAdsException) {
        //     // Bắt lỗi chi tiết từ Google Ads API
        //     $errors = [];
        //     foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
        //         $errorMessage = $error->getMessage();
        //         $errorCode = $error->getErrorCode()->getErrorCode();
        //         $errors[] = "Mã lỗi: $errorCode - Thông báo: $errorMessage";
        //     }
        //     return [
        //         'status' => 'error',
        //         'errors' => $errors,
        //     ];
        // } catch (ApiException $apiException) {
        //     // Bắt lỗi API chung và hiển thị lỗi cho người dùng
        //     return [
        //         'status' => 'error',
        //         'errors' => 'Lỗi API: ' . $apiException->getMessage(),
        //     ];
        // } catch (\Exception $exception) {
        //     // Bắt các ngoại lệ chung khác (nếu có)
        //     return [
        //         'status' => 'error',
        //         'errors' => 'Đã xảy ra lỗi: ' . $exception->getMessage(),
        //     ];
        // }
    }
    public function createUniqueCampaignName($customerId, $baseCampaignName)
    {
        // Khởi tạo dịch vụ Google Ads Service Client
        $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();

        // Tạo đối tượng SearchGoogleAdsRequest với customer_id và truy vấn
        $query = "SELECT campaign.name FROM campaign WHERE campaign.status IN ('ENABLED', 'PAUSED', 'REMOVED')";
        $searchRequest = new SearchGoogleAdsRequest([
            'customer_id' => $customerId,
            'query' => $query,
        ]);
        // Thực hiện truy vấn với đối tượng SearchGoogleAdsRequest
        $response = $googleAdsServiceClient->search($searchRequest);

        // Lưu trữ tên các chiến dịch đã tồn tại
        $existingCampaignNames = [];
        foreach ($response->iterateAllElements() as $googleAdsRow) {
            $existingCampaignNames[] = $googleAdsRow->getCampaign()->getName();
        }

        // Kiểm tra và tạo tên chiến dịch duy nhất
        $uniqueCampaignName = $baseCampaignName;
        $counter = 1;
        while (in_array($uniqueCampaignName, $existingCampaignNames)) {
            // Tăng số đếm và kiểm tra lại
            $uniqueCampaignName = $baseCampaignName . ' ' . $counter;
            $counter++;
        }

        return $uniqueCampaignName;
    }
    /**
     * Tạo ngân sách cho chiến dịch App Campaign.
     */
    private function createBudget($customerId, $budgetAmount)
    {
        $campaignBudget = new CampaignBudget([
            'name' => 'App Campaign Budget #' . now()->toDateTimeString(),
            'amount_micros' => $budgetAmount * 1000000, // Chuyển đổi từ USD sang micros
            'delivery_method' => BudgetDeliveryMethod::STANDARD,
            'explicitly_shared' => false, // App Campaign không thể sử dụng ngân sách chia sẻ
        ]);

        $campaignBudgetOperation = new CampaignBudgetOperation();
        $campaignBudgetOperation->setCreate($campaignBudget);

        $campaignBudgetServiceClient = $this->googleAdsClient->getCampaignBudgetServiceClient();
        $response = $campaignBudgetServiceClient->mutateCampaignBudgets(
            new MutateCampaignBudgetsRequest([
                'customer_id' => $customerId,
                'operations' => [$campaignBudgetOperation]
            ])
        );

        return $response->getResults()[0]->getResourceName();
    }

    /**
     * Tạo chiến dịch App Campaign.
     */
    private function createCampaign($customerId, $budgetResourceName, $campaignData)
    {
        $campaign = new Campaign([
            'name' => $campaignData['name'],
            'campaign_budget' => $budgetResourceName,
            'status' => CampaignStatus::ENABLED, // Tạo ở trạng thái tạm dừng để tránh quảng cáo chưa sẵn sàng
            'advertising_channel_type' => AdvertisingChannelType::MULTI_CHANNEL,
            'advertising_channel_sub_type' => AdvertisingChannelSubType::APP_CAMPAIGN,
            'target_cpa' => new TargetCpa(['target_cpa_micros' => 1000000]),
            'app_campaign_setting' => new AppCampaignSetting([
                'app_id' => $campaignData['app_id'],
                'app_store' => AppCampaignAppStore::GOOGLE_APP_STORE,
                'bidding_strategy_goal_type' => AppCampaignBiddingStrategyGoalType::OPTIMIZE_INSTALLS_TARGET_INSTALL_COST
            ]),
            'start_date' =>  $campaignData['start_date'],
        ]);

        $campaignOperation = new CampaignOperation();
        $campaignOperation->setCreate($campaign);

        $campaignServiceClient = $this->googleAdsClient->getCampaignServiceClient();
        $response = $campaignServiceClient->mutateCampaigns(
            new MutateCampaignsRequest([
                'customer_id' => $customerId,
                'operations' => [$campaignOperation],
            ])
        );

        return $response->getResults()[0]->getResourceName();
    }

    // Các phương thức khác cho App Campaign như thêm từ khóa, quảng cáo, v.v. nếu cần.
    /**
     * Đặt tiêu chí nhắm mục tiêu cho App Campaign.
     */
    private function setCampaignTargetingCriteria($customerId, $campaignResourceName)
    {
        $campaignCriterionOperations = [];

        // Nhắm mục tiêu toàn bộ quốc gia
        $campaignCriterion = new CampaignCriterion([
            'campaign' => $campaignResourceName,
            'location' => new LocationInfo([
                'geo_target_constant' => 'geoTargetConstants/1023191' // Mã cho toàn cầu (Global)
            ])
        ]);
        $campaignCriterionOperation = new CampaignCriterionOperation();
        $campaignCriterionOperation->setCreate($campaignCriterion);
        $campaignCriterionOperations[] = $campaignCriterionOperation;

        // Nhắm mục tiêu tất cả các ngôn ngữ (sử dụng mã của các ngôn ngữ phổ biến)
        // Đây là danh sách các mã ngôn ngữ phổ biến, bạn có thể thêm mã khác nếu cần.
        $languageIds = [1000, 1001, 1002, 1003, 1018, 1019, 1020]; // Các mã ngôn ngữ phổ biến

        foreach ($languageIds as $languageId) {
            $campaignCriterion = new CampaignCriterion([
                'campaign' => $campaignResourceName,
                'language' => new LanguageInfo([
                    'language_constant' => ResourceNames::forLanguageConstant($languageId)
                ])
            ]);
            $campaignCriterionOperation = new CampaignCriterionOperation();
            $campaignCriterionOperation->setCreate($campaignCriterion);
            $campaignCriterionOperations[] = $campaignCriterionOperation;
        }

        // Gửi yêu cầu nhắm mục tiêu tiêu chí chiến dịch
        $campaignCriterionServiceClient = $this->googleAdsClient->getCampaignCriterionServiceClient();
        $campaignCriterionServiceClient->mutateCampaignCriteria(
            new MutateCampaignCriteriaRequest([
                'customer_id' => $customerId,
                'operations' => $campaignCriterionOperations,
            ])
        );
    }
    /**
     * Tạo nhóm quảng cáo cho App Campaign.
     */
    private function createAdGroup($customerId, $campaignResourceName)
    {
        $adGroup = new AdGroup([
            'name' => 'App Ad Group #',
            'status' => AdGroupStatus::ENABLED,
            'campaign' => $campaignResourceName
        ]);

        $adGroupOperation = new AdGroupOperation();
        $adGroupOperation->setCreate($adGroup);

        $adGroupServiceClient = $this->googleAdsClient->getAdGroupServiceClient();
        $response = $adGroupServiceClient->mutateAdGroups(
            new MutateAdGroupsRequest([
                'customer_id' => $customerId,
                'operations' => [$adGroupOperation],
            ])
        );

        return $response->getResults()[0]->getResourceName();
    }
    /**
     * Tạo App ad cho nhóm quảng cáo.
     */
    private function createAppAd($customerId, $adGroupResourceName)
    {
        $adGroupAd = new AdGroupAd([
            'status' => AdGroupAdStatus::ENABLED,
            'ad_group' => $adGroupResourceName,
            'ad' => new Ad([
                'app_ad' => new AppAdInfo([
                    'headlines' => [
                        new AdTextAsset(['text' => 'A cool puzzle game']),
                        new AdTextAsset(['text' => 'Remove connected blocks'])
                    ],
                    'descriptions' => [
                        new AdTextAsset(['text' => '3 difficulty levels']),
                        new AdTextAsset(['text' => '4 colorful fun skins'])
                    ]
                ])
            ])
        ]);

        $adGroupAdOperation = new AdGroupAdOperation();
        $adGroupAdOperation->setCreate($adGroupAd);

        $adGroupAdServiceClient = $this->googleAdsClient->getAdGroupAdServiceClient();
        $adGroupAdServiceClient->mutateAdGroupAds(
            new MutateAdGroupAdsRequest([
                'customer_id' => $customerId,
                'operations' => [$adGroupAdOperation],
            ])
        );
    }
}
