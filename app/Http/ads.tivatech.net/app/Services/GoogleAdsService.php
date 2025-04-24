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
use App\Services\GoogleAdsAccountService;
use Google\Ads\GoogleAds\V17\Enums\AdNetworkTypeEnum\AdNetworkType;
use Google\Ads\GoogleAds\V17\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V17\Services\GoogleAdsServiceClient;
class GoogleAdsService
{
    protected $googleAdsClient;
    protected $googleAdsAccountService;
    public function __construct(GoogleAdsAccountService $GoogleAdsAccountService)
    {
        $this->googleAdsAccountService = $GoogleAdsAccountService;
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

    /**∏∏
     * Tạo tài khoản khách hàng mới dưới tài khoản MCC
     * @param string $descriptiveName Tên tài khoản mới
     * @return string $customerResourceName
     */

    //tạo tài khoản
    public function createCustomerAccount($descriptiveName, $currency, $time_zone)
    {
        // Tạo đối tượng Customer mới với thông tin cần thiết
        $customer = new Customer([
            'descriptive_name' => $descriptiveName,
            'currency_code' => $currency,
            'time_zone' => $time_zone,
            'tracking_url_template' => '{lpurl}?device={device}',
            'final_url_suffix' => 'keyword={keyword}&matchtype={matchtype}&adgroupid={adgroupid}'
        ]);

        // Lấy đối tượng CustomerServiceClient
        $customerServiceClient = $this->googleAdsClient->getCustomerServiceClient();

        // Tạo yêu cầu CreateCustomerClientRequest
        $createCustomerClientRequest = new CreateCustomerClientRequest([
            'customer_id' => env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'), // ID của tài khoản MCC
            'customer_client' => $customer,
        ]);

        // Gửi yêu cầu tạo tài khoản khách hàng dưới MCC
        $response = $customerServiceClient->createCustomerClient($createCustomerClientRequest);

        // Trả về thông tin tài khoản mới tạo
        return $response->getResourceName();
    }
  public function getTopAds(Request $request)
{
 $keyword = $request->input('keyword', 'cửa cuốn');
    $query = "SELECT ad_group_ad.ad.final_urls FROM ad_group_ad WHERE ad_group_ad.status = 'ENABLED' AND segments.keyword.info.text = '$keyword' LIMIT 10";

    // Tạo một đối tượng SearchGoogleAdsRequest với customer ID và truy vấn
    $searchRequest = new SearchGoogleAdsRequest([
        'customer_id' => env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'),
        'query' => $query,
    ]);
    $response = $this->googleAdsClient->getGoogleAdsServiceClient()->search($searchRequest);

    $domains = [];
    foreach ($response->iterateAllElements() as $googleAdsRow) {
        $urls = $googleAdsRow->getAdGroupAd()->getAd()->getFinalUrls();
        foreach ($urls as $url) {
            $domain = parse_url($url, PHP_URL_HOST); // Trích xuất domain từ URL
            $domains[] = $domain;
        }
    }

    $uniqueDomains = array_unique($domains); // Lọc các domain trùng lặp
	  return $uniqueDomains;
    
}

    public function createFullCampaign(Request $request, $accountId)
    {
        $customerId = str_replace(['-', '.', '_'], '', $accountId);
        $createdKeywords = [];
        $createdAds = [];
        $keywordErrors = [];
        try {
            $checkAcc=$this->googleAdsAccountService->checkAccountStatus($customerId);

            if ($checkAcc['status'] == 'success') {
                // 1. Tạo chiến dịch
                $campaignResourceName = $this->createCampaign($request, $customerId);

                // 2. Tạo nhóm quảng cáo
                $adGroupResourceName = $this->createAdGroup($request, $customerId, $campaignResourceName);

                // 3. Thêm từ khóa vào nhóm quảng cáo
                if ($request->keywords!=null) {
                    $keywords = array_filter(array_map('trim', explode("\n", $request->keywords)));
                    foreach ($keywords as $keyword) {
                        $keywordResourceName = $this->addKeyword($customerId, $adGroupResourceName, $keyword);
    
                        // Kiểm tra nếu có lỗi trong quá trình tạo từ khóa
                        if (isset($keywordResourceName['status']) && $keywordResourceName['status'] === 'error') {
                            // Lưu lỗi và tiếp tục với từ khóa tiếp theo
                            $keywordErrors[] = [
                                'keyword' => $keyword,
                                'errors' => $keywordResourceName['errors']
                            ];
                            continue;
                        }
    
                        // Lưu lại từ khóa đã tạo thành công
                        $createdKeywords[] = $keywordResourceName;
                    }
                }
              

                // 4. Tạo mẫu quảng cáo trong nhóm quảng cáo
                $responseAd = $this->createResponsiveSearchAd($request, $customerId, $adGroupResourceName);

                // Kiểm tra nếu có lỗi trong quá trình tạo quảng cáo
                if (isset($responseAd['status']) && $responseAd['status'] === 'error') {
                    throw new \Exception("Lỗi quảng cáo: " . implode(", ", $responseAd['errors']));
                }

                // Lưu lại quảng cáo đã tạo để rollback nếu cần
                $createdAds[] = $responseAd;

                // Trả về thông báo thành công và các từ khóa đã tạo lỗi nếu có
                return [
                    'status' => 'success',
                    'message' => 'Chiến dịch và các phần đã được thêm thành công.',
                    'keyword_errors' => $keywordErrors, // Trả về lỗi từ khóa nếu có
                ];
            }
            else{
                return $checkAcc;
            }
        } catch (GoogleAdsException $googleAdsException) {
            // Bắt lỗi chi tiết từ Google Ads
            $errors = [];
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                $errorMessage = $error->getMessage();
                $errorCode = $error->getErrorCode()->getErrorCode();
                $errors[] = "Mã lỗi: $errorCode - Thông báo: $errorMessage";
            }

            // Thực hiện rollback các đối tượng đã tạo nếu có lỗi xảy ra
            $this->rollbackGoogleAds($customerId, $campaignResourceName ?? null, $adGroupResourceName ?? null, $createdKeywords, $createdAds);
            return [
                'status' => 'error',
                'errors' => $errors,
            ];
        } catch (\Exception $exception) {
            // Bắt các ngoại lệ chung khác
            $this->rollbackGoogleAds($customerId, $campaignResourceName ?? null, $adGroupResourceName ?? null, $createdKeywords, $createdAds);
            return [
                'status' => 'error',
                'errors' => ["Đã xảy ra lỗi không xác định: " . $exception->getMessage()],
            ];
        }
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
    public function createCampaign(Request $request, $customerId)
    {
        try {
            // Thêm ngân sách chiến dịch
            $budgetResourceName = $this->addCampaignBudget($request->budget, $this->googleAdsClient, $customerId);
            $request['campaign_name'] = $this->createUniqueCampaignName($customerId, $request['campaign_name']);
            // Tạo chiến dịch mới
            $campaignOperations = [];
            $campaign = new Campaign([
                'name' =>  $request->campaign_name,
                'advertising_channel_type' => AdvertisingChannelType::SEARCH,
                'status' => CampaignStatus::ENABLED,
                'manual_cpc' => new ManualCpc([
                    'enhanced_cpc_enabled' => false, // Tắt CPC thủ công nâng cao
                ]),
                'campaign_budget' => $budgetResourceName,
                'network_settings' => new NetworkSettings([
                    'target_google_search' => true,
                    'target_search_network' => false,
                    'target_content_network' => false,
                    'target_partner_search_network' => false,
                ]),
                'start_date' =>  $request['start_date'],
            ]);

            $campaignOperation = new CampaignOperation();
            $campaignOperation->setCreate($campaign);
            $campaignOperations[] = $campaignOperation;
            // Tạo đối tượng MutateCampaignsRequest
            $mutateCampaignsRequest = new MutateCampaignsRequest([
                'customer_id' => $customerId,
                'operations' => $campaignOperations,
            ]);

            // Gửi yêu cầu tạo chiến dịch
            $campaignServiceClient = $this->googleAdsClient->getCampaignServiceClient();
            $response = $campaignServiceClient->mutateCampaigns($mutateCampaignsRequest);


            // Set language targeting for English and Vietnamese
            $languageOperations = [];

            $languages = [
                ['id' => 1000, 'name' => 'English'],
                // ['id' => 1016, 'name' => 'Vietnamese']
            ]; // 1000 for English, 1016 for Vietnamese

            foreach ($languages as $language) {
                $languageInfo = new LanguageInfo([
                    'language_constant' => 'languageConstants/' . $language['id'],
                ]);

                $campaignCriterion = new CampaignCriterion([
                    'campaign' => $response->getResults()[0]->getResourceName(),
                    'language' => $languageInfo,
                ]);

                $campaignCriterionOperation = new CampaignCriterionOperation();
                $campaignCriterionOperation->setCreate($campaignCriterion);
                $languageOperations[] = $campaignCriterionOperation;
            }

            // Create the MutateCampaignCriteriaRequest object
            $mutateCampaignCriteriaRequest = new MutateCampaignCriteriaRequest([
                'customer_id' => $customerId,
                'operations' => $languageOperations,
            ]);

            // Send the request to add language targeting
            $campaignCriterionServiceClient = $this->googleAdsClient->getCampaignCriterionServiceClient();
            $campaignCriterionServiceClient->mutateCampaignCriteria($mutateCampaignCriteriaRequest);
            return $response->getResults()[0]->getResourceName();
        } catch (GoogleAdsException $googleAdsException) {
            // Thu thập lỗi chi tiết từ Google Ads
            $errors = [];
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                /** @var GoogleAdsError $error */
                $errorMessage = $error->getMessage();
                $errorCode = $error->getErrorCode()->getErrorCode();
                $errors[] = "Mã lỗi: $errorCode - Thông báo: $errorMessage";
            }
            return redirect()->back()->withInput()->withErrors($errors);
        } catch (ApiException $apiException) {
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi không xác định ' . $apiException->getMessage()],
            ];
        } catch (\Exception $exception) {
            // Bắt các ngoại lệ chung khác (nếu có)
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi khi kết nối với Google Ads API để tạo chiến dịch :' .  $exception->getMessage()],
            ];
        }
    }
    //tạo nhóm quảng cáo
    public function createAdGroup(Request $request, $customerId, $campaignResourceName)
    {
        try {
            // Tạo nhóm quảng cáo mới
            $adGroup = new AdGroup([
                'name' => $request->adgroup_name,
                'campaign' => $campaignResourceName, // Sử dụng chiến dịch đã tạo
                'status' => AdGroupStatus::ENABLED,
                'cpc_bid_micros' => $request->cpc_bid . '000000', // 1 USD
            ]);

            // Tạo một operation cho nhóm quảng cáo
            $adGroupOperation = new AdGroupOperation();
            $adGroupOperation->setCreate($adGroup);

            // Tạo MutateAdGroupsRequest
            $mutateAdGroupsRequest = new MutateAdGroupsRequest([
                'customer_id' => $customerId,
                'operations' => [$adGroupOperation],
            ]);

            // Gửi yêu cầu tạo nhóm quảng cáo
            $adGroupServiceClient = $this->googleAdsClient->getAdGroupServiceClient();
            $response = $adGroupServiceClient->mutateAdGroups($mutateAdGroupsRequest);
            return $response->getResults()[0]->getResourceName(); // Trả về resource name của nhóm quảng cáo
        } catch (ApiException $apiException) {
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi không xác định ' . $apiException->getMessage()],
            ];
        } catch (\Exception $exception) {
            // Bắt các ngoại lệ chung khác (nếu có)
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi khi kết nối với 14 Google Ads API' .  $exception->getMessage()],
            ];
        }
    }

    //tạo từ khóa
    public function addKeyword($customerId, $adGroupResourceName, $keywordText)
    {
        try {
            // Tạo KeywordInfo
            $keywordInfo = new KeywordInfo([
                'text' => $keywordText,
                'match_type' => KeywordMatchType::EXACT, // Chọn loại từ khóa: Exact, Phrase, Broad
            ]);

            // Tạo AdGroupCriterion để thêm từ khóa vào nhóm quảng cáo
            $adGroupCriterion = new AdGroupCriterion([
                'ad_group' => $adGroupResourceName,
                'status' => AdGroupCriterionStatus::ENABLED,
                'keyword' => $keywordInfo,
                'cpc_bid_micros' => 1000000, // Giá thầu CPC
            ]);

            // Tạo operation cho từ khóa
            $adGroupCriterionOperation = new AdGroupCriterionOperation();
            $adGroupCriterionOperation->setCreate($adGroupCriterion);

            // Tạo MutateAdGroupCriteriaRequest
            $mutateAdGroupCriteriaRequest = new MutateAdGroupCriteriaRequest([
                'customer_id' => $customerId,
                'operations' => [$adGroupCriterionOperation],
            ]);

            // Gửi yêu cầu thêm từ khóa
            $adGroupCriterionServiceClient = $this->googleAdsClient->getAdGroupCriterionServiceClient();
            $response = $adGroupCriterionServiceClient->mutateAdGroupCriteria($mutateAdGroupCriteriaRequest);

            return $response->getResults()[0]->getResourceName(); // Trả về resource name của từ khóa đã tạo
        } catch (GoogleAdsException $googleAdsException) {
            // Xử lý lỗi từ Google Ads API, ghi lại lỗi chi tiết hơn
            $errors = [];
            foreach ($googleAdsException->getGoogleAdsFailure()->getErrors() as $error) {
                $errorMessage = $error->getMessage();
                $errorCode = $error->getErrorCode()->getErrorCode();
                $errors[] = "Mã lỗi: $errorCode - Thông báo: $errorMessage";
            }

            // Trả về lỗi chi tiết để người dùng biết lý do thất bại
            return [
                'status' => 'error',
                'errors' => $errors,
            ];
        } catch (\Exception $exception) {
            // Bắt các ngoại lệ chung khác (nếu có)
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi không xác định khi thêm từ khóa: ' . $exception->getMessage()],
            ];
        }
    }
    public function createResponsiveSearchAd(Request $request, $customerId, $adGroupResourceName)
    {
        try {
            // Khởi tạo mảng để lưu các AdTextAsset cho tiêu đề và mô tả
            $headlines = [];
            $descriptions = [];

            // Duyệt qua tiêu đề từ request và loại bỏ các tiêu đề trùng lặp
            $uniqueHeadlines = array_unique($request->ad_headline);
            foreach ($uniqueHeadlines as $ad_headline) {
                $headlines[] = new AdTextAsset(['text' => $ad_headline]);
            }

            // Duyệt qua mô tả từ request và loại bỏ các mô tả trùng lặp
            $uniqueDescriptions = array_unique($request->ad_description);
            foreach ($uniqueDescriptions as $ad_description) {
                $descriptions[] = new AdTextAsset(['text' => $ad_description]);
            }

            // Tạo mẫu quảng cáo tìm kiếm đáp ứng (Responsive Search Ad)
            $responsiveSearchAdInfo = new ResponsiveSearchAdInfo([
                'headlines' => $headlines, // Mảng tiêu đề đã loại bỏ bản sao
                'descriptions' => $descriptions, // Mảng mô tả đã loại bỏ bản sao
            ]);

            // Tạo đối tượng Ad
            $ad = new Ad([
                'responsive_search_ad' => $responsiveSearchAdInfo,
                'final_urls' => [
                    $request->final_url
                ],
            ]);

            // Tạo AdGroupAd
            $adGroupAd = new AdGroupAd([
                'ad_group' => $adGroupResourceName,
                'status' => AdGroupAdStatus::ENABLED,
                'ad' => $ad,
            ]);

            // Tạo operation cho mẫu quảng cáo
            $adGroupAdOperation = new AdGroupAdOperation();
            $adGroupAdOperation->setCreate($adGroupAd);

            // Tạo MutateAdGroupAdsRequest
            $mutateAdGroupAdsRequest = new MutateAdGroupAdsRequest([
                'customer_id' => $customerId,
                'operations' => [$adGroupAdOperation],
            ]);

            // Gửi yêu cầu tạo quảng cáo
            $adGroupAdServiceClient = $this->googleAdsClient->getAdGroupAdServiceClient();
            $response = $adGroupAdServiceClient->mutateAdGroupAds($mutateAdGroupAdsRequest);
        } catch (ApiException $apiException) {
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi không xác định ' . $apiException->getMessage()],
            ];
        } catch (\Exception $exception) {
            // Bắt các ngoại lệ chung khác (nếu có)
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi khi kết nối với Google Ads API mẫu quảng cáo ' .  $exception->getMessage()],
            ];
        }
    }
    /**
     * Thêm ngân sách chiến dịch.
     */
    private function addCampaignBudget($budget, $googleAdsClient, $customerId)
    {
        try {
            // Tạo ngân sách chiến dịch
            $budget = new CampaignBudget([
                'name' => 'Laravel Campaign Budget #' . now()->toDateTimeString(),
                'amount_micros' => $budget . '000000', // Ngân sách 0.5 USD
                'delivery_method' => BudgetDeliveryMethod::STANDARD,
            ]);

            // Tạo chiến dịch ngân sách
            $campaignBudgetOperation = new CampaignBudgetOperation();
            $campaignBudgetOperation->setCreate($budget);

            // Tạo đối tượng MutateCampaignBudgetsRequest
            $mutateCampaignBudgetsRequest = new MutateCampaignBudgetsRequest([
                'customer_id' => $customerId,
                'operations' => [$campaignBudgetOperation],
            ]);

            // Gửi yêu cầu tạo ngân sách
            $campaignBudgetServiceClient = $googleAdsClient->getCampaignBudgetServiceClient();
            $response = $campaignBudgetServiceClient->mutateCampaignBudgets($mutateCampaignBudgetsRequest);

            return $response->getResults()[0]->getResourceName();
        } catch (ApiException $apiException) {
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi không xác định ' . $apiException->getMessage()],
            ];
        } catch (\Exception $exception) {
            // Bắt các ngoại lệ chung khác (nếu có)
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi khi kết nối với 3 Google Ads thêm budget API' .  $exception->getMessage()],
            ];
        }
    }

    // Hàm rollback để xóa các đối tượng đã tạo nếu có lỗi xảy ra
    private function rollbackGoogleAds($customerId, $campaignResourceName = null, $adGroupResourceName = null, $createdKeywords = [], $createdAds = [])
    {
        try {
            // Nếu đã tạo quảng cáo, xóa quảng cáo trước
            foreach ($createdAds as $adResourceName) {
                $this->removeAd($customerId, $adResourceName);
            }
            // Nếu đã tạo nhóm quảng cáo, xóa nhóm quảng cáo
            if ($adGroupResourceName) {
                $this->removeAdGroup($customerId, $adGroupResourceName);
            }

            // Nếu đã tạo chiến dịch, xóa chiến dịch
            if ($campaignResourceName) {
                $this->removeCampaign($customerId, $campaignResourceName);
            }
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi khi kết nối với Google 3 Ads 2 API'],
            ];
        } catch (\Exception $e) {
            // Bạn có thể log lỗi rollback ở đây nếu cần thiết
            // Log::error('Lỗi khi rollback Google Ads: ' . $e->getMessage());
            return [
                'status' => 'error',
                'errors' => ['Đã xảy ra lỗi khi kết nối với Google 5 Ads4 API'],
            ];
            printf("Lỗi khi xóa quảng cáo với resource name '%s': %s\n",);
        }
    }
    private function removeAd($customerId, $adResourceName)
    {
        try {
            // Khởi tạo dịch vụ AdGroupAdServiceClient
            $adGroupAdServiceClient = $this->googleAdsClient->getAdGroupAdServiceClient();

            // Tạo AdGroupAdOperation để xóa quảng cáo
            $adGroupAdOperation = new AdGroupAdOperation();
            $adGroupAdOperation->setRemove($adResourceName); // Đặt resource name của quảng cáo cần xóa

            // Gửi yêu cầu xóa quảng cáo
            $response = $adGroupAdServiceClient->mutateAdGroupAds($customerId, [$adGroupAdOperation]);

            // In ra tên tài nguyên của quảng cáo đã xóa
            printf("Quảng cáo với resource name '%s' đã được xóa.\n", $adResourceName);
        } catch (GoogleAdsException $googleAdsException) {
            printf(
                "Lỗi khi xóa quảng cáo với resource name '%s': %s\n",
                $adResourceName,
                $googleAdsException->getMessage()
            );
        }
    }

    private function removeAdGroup($customerId, $adGroupResourceName)
    {
        try {
            // Khởi tạo dịch vụ AdGroupServiceClient
            $adGroupServiceClient = $this->googleAdsClient->getAdGroupServiceClient();

            // Tạo AdGroupOperation để xóa nhóm quảng cáo
            $adGroupOperation = new AdGroupOperation();
            $adGroupOperation->setRemove($adGroupResourceName); // Đặt resource name của nhóm quảng cáo cần xóa

            // Tạo đối tượng MutateAdGroupsRequest
            $mutateAdGroupsRequest = new MutateAdGroupsRequest([
                'customer_id' => $customerId, // Đặt customer ID
                'operations' => [$adGroupOperation], // Truyền mảng các operations (ở đây chỉ có 1 operation)
            ]);

            // Gửi yêu cầu xóa nhóm quảng cáo
            $response = $adGroupServiceClient->mutateAdGroups($mutateAdGroupsRequest);

            // In ra tên tài nguyên của nhóm quảng cáo đã xóa
            printf("Nhóm quảng cáo với resource name '%s' đã được xóa.\n", $adGroupResourceName);
        } catch (GoogleAdsException $googleAdsException) {
            printf(
                "Lỗi khi xóa nhóm quảng cáo với resource name '%s': %s\n",
                $adGroupResourceName,
                $googleAdsException->getMessage()
            );
        } catch (\Exception $exception) {
            printf(
                "Đã xảy ra lỗi không xác định khi xóa nhóm quảng cáo với resource name '%s': %s\n",
                $adGroupResourceName,
                $exception->getMessage()
            );
        }
    }
    private function removeCampaign($customerId, $campaignResourceName)
    {
        try {
            // Khởi tạo dịch vụ CampaignServiceClient
            $campaignServiceClient = $this->googleAdsClient->getCampaignServiceClient();

            // Tạo CampaignOperation để xóa chiến dịch
            $campaignOperation = new CampaignOperation();
            $campaignOperation->setRemove($campaignResourceName); // Đặt resource name của chiến dịch cần xóa

            // Tạo đối tượng MutateCampaignsRequest
            $mutateCampaignsRequest = new MutateCampaignsRequest([
                'customer_id' => $customerId, // Đặt customer ID
                'operations' => [$campaignOperation], // Truyền mảng các operations (ở đây chỉ có 1 operation)
            ]);

            // Gửi yêu cầu xóa chiến dịch
            $response = $campaignServiceClient->mutateCampaigns($mutateCampaignsRequest);

            // In ra tên tài nguyên của chiến dịch đã xóa
            printf("Chiến dịch với resource name '%s' đã được xóa.\n", $campaignResourceName);
        } catch (GoogleAdsException $googleAdsException) {
            printf(
                "Lỗi khi xóa chiến dịch với resource name '%s': %s\n",
                $campaignResourceName,
                $googleAdsException->getMessage()
            );
        } catch (\Exception $exception) {
            printf(
                "Đã xảy ra lỗi không xác định khi xóa chiến dịch với resource name '%s': %s\n",
                $campaignResourceName,
                $exception->getMessage()
            );
        }
    }
}
