<?php

namespace App\Http\Controllers;

use App\Services\GoogleAdsService;
use Illuminate\Http\Request;

class GoogleAdsTestController extends Controller
{
    public function test(GoogleAdsService $googleAdsService)
    {
        $client = $googleAdsService->getClient();

        $googleAdsServiceClient = $client->getGoogleAdsServiceClient();

        $query = 'SELECT customer.id, customer.descriptive_name FROM customer LIMIT 1';
        $response = $googleAdsServiceClient->search('1420991007', $query);

        foreach ($response->iterateAllElements() as $row) {
            dd($row->getCustomer()->getDescriptiveName());
        }
    }
}
