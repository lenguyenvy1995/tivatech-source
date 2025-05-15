<?php

namespace App\Services;

use App\Models\GoogleAdsConfig;
use Google\Ads\GoogleAds\Lib\V19\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\V19\Services\SuggestGeoTargetConstantsRequest;
use Google\Ads\GoogleAds\V19\Services\GeoTargetConstantSuggestion;
use Google\Ads\GoogleAds\V19\Services\GeoTargetConstantServiceClient;
use Google\Ads\GoogleAds\V19\Services\SuggestGeoTargetConstantsRequest\LocationNames;
use Google\Ads\GoogleAds\V19\Services\SearchGeoTargetConstantsRequest;
use Google\Ads\GoogleAds\V19\Services\SearchGoogleAdsStreamRequest;
use Google\Ads\GoogleAds\V19\Services\KeywordPlanIdeaServiceClient;
use Google\Ads\GoogleAds\V19\Services\KeywordAndUrlSeed;
use Google\Ads\GoogleAds\V19\Services\GenerateKeywordIdeasRequest;
use Google\Ads\GoogleAds\V19\Services\KeywordSeed;
use Google\Ads\GoogleAds\V19\Services\GenerateKeywordHistoricalMetricsRequest;
use Google\Protobuf\StringValue;
use Google\Ads\GoogleAds\V19\Enums\KeywordPlanNetworkEnum\KeywordPlanNetwork;
use Google\Ads\GoogleAds\V19\Enums\KeywordPlanCompetitionLevelEnum\KeywordPlanCompetitionLevel;
class GoogleAdsService
{
    protected $config;

    public function __construct()
    {
        $this->config = GoogleAdsConfig::first(); // hoặc where('active', 1) nếu có nhiều config
    }

    public function getClient()
    {
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->withClientId($this->config->client_id)
            ->withClientSecret($this->config->client_secret)
            ->withRefreshToken($this->config->refresh_token)
            ->build();

        return (new GoogleAdsClientBuilder())
            ->withDeveloperToken($this->config->developer_token)
            ->withLoginCustomerId($this->config->login_customer_id)
            ->withOAuth2Credential($oAuth2Credential)
            ->build();
    }
    //lấy danh sách địa điểm tại Việt Nam

    public function getVietnamLocations()
    {
        $client = $this->getClient();
        $geoService = $client->getGeoTargetConstantServiceClient();

        $locationNames = new LocationNames([
            'names' => ['Vietnam', 'Hồ Chí Minh', 'Hà Nội', 'Đà Nẵng']
        ]);

        $request = new SuggestGeoTargetConstantsRequest([
            'locale' => 'vi',
            'location_names' => $locationNames
        ]);

        $response = $geoService->suggestGeoTargetConstants($request);

        $results = [];

        foreach ($response->getGeoTargetConstantSuggestions() as $suggestion) {
            $geo = $suggestion->getGeoTargetConstant();

            if (in_array($geo->getTargetType(), ['Province', 'Country'])) {
                $results[] = [
                    'id' => $geo->getId(),
                    'name' => $geo->getName(),
                    'target_type' => $geo->getTargetType(),
                ];
            }
        }

        return $results;
    }
    //lấy danh sách từ khóa liên quan đến từ khóa và url
    public function getSearchVolume(array $keywords, int $locationId)
    {
        $client = $this->getClient();
        $keywordPlanService = $client->getKeywordPlanIdeaServiceClient();

        $keywordObjects = [];
        foreach ($keywords as $kw) {
            if (is_array($kw) && isset($kw['value'])) {
                $keywordObjects[] = (string) $kw['value'];
            } elseif (is_string($kw)) {
                $keywordObjects[] = $kw;
            }
        }
        $request = new GenerateKeywordHistoricalMetricsRequest([
            // 'customer_id' => $this->config->login_customer_id,
            
            'customer_id' =>'2531113556',
            'keywords' => $keywordObjects,
            'geo_target_constants' => [
                sprintf('geoTargetConstants/%s', $locationId),
            ],
            'language' => 'languageConstants/1000', // Vietnamese
            'keyword_plan_network' => KeywordPlanNetwork::GOOGLE_SEARCH,
        ]);

        $response = $keywordPlanService->generateKeywordHistoricalMetrics($request);

        $results = [];

        foreach ($response->getResults() as $result) {
            $metrics = $result->getKeywordMetrics();

            if ($metrics === null) {
                continue;
            }

            $results[] = [
                'keyword' => $result->getText(),
                'avg_monthly_searches' => $metrics->getAvgMonthlySearches(),
                'competition' => KeywordPlanCompetitionLevel::name($metrics->getCompetition()),
                'low_bid' => $metrics->hasLowTopOfPageBidMicros() ? number_format(intval($metrics->getLowTopOfPageBidMicros() / 1_000_000)) : null,
                'high_bid' => $metrics->hasHighTopOfPageBidMicros() ? number_format(intval($metrics->getHighTopOfPageBidMicros() / 1_000_000)) : null,
                'source' => 'historical',
            ];
        }

        // Get additional keyword ideas related to the first keyword
        if (count($keywordObjects) > 0) {
            $keywordIdeaService = $client->getKeywordPlanIdeaServiceClient();

            $ideaRequest = new GenerateKeywordIdeasRequest([
                'customer_id' =>'2531113556',
                'keyword_seed' => new KeywordSeed([
                    'keywords' => $keywordObjects, // chỉ cần chuỗi
                ]),
                'geo_target_constants' => [
                    sprintf('geoTargetConstants/%s', $locationId),
                ],
                'language' => 'languageConstants/1000',
                'include_adult_keywords' => false,
            ]);

            $ideaResponse = $keywordIdeaService->generateKeywordIdeas($ideaRequest);

            foreach ($ideaResponse->iterateAllElements() as $idea) {
                $metrics = $idea->getKeywordIdeaMetrics();
                if ($metrics && $metrics->getAvgMonthlySearches() > 10) {
                    $results[] = [
                        'keyword' => $idea->getText(),
                        'avg_monthly_searches' => $metrics->getAvgMonthlySearches(),
                        'competition' => KeywordPlanCompetitionLevel::name($metrics->getCompetition()),
                        'low_bid' => $metrics->hasLowTopOfPageBidMicros() ? number_format(intval($metrics->getLowTopOfPageBidMicros() / 1_000_000)) : null,
                        'high_bid' => $metrics->hasHighTopOfPageBidMicros() ? number_format(intval($metrics->getHighTopOfPageBidMicros() / 1_000_000)) : null,
                        'source' => 'suggested',
                    ];
                }
            }
        }

        // Loại bỏ từ khóa trùng nhau, ưu tiên bản từ HistoricalMetrics
        $results = collect($results)
            ->unique('keyword')
            ->values()
            ->all();

        return $results;
    }
}
