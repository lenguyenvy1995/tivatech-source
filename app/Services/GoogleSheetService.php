<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Google\Client;
use Google\Service\Sheets;

class GoogleSheetService
{
    // Sử dụng API Key
    public function fetchWithApiKey(string $spreadsheetId, string $range, string $apiKey)
    {
        $url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/$range?key=$apiKey";
        $response = Http::get($url);

        if ($response->successful()) {
            return $response->json(); // Trả về dữ liệu JSON
        }

        throw new \Exception('Unable to fetch data from Google Sheets.');
    }

    // Sử dụng OAuth
    public function fetchWithOAuth(string $spreadsheetId, string $range, string $credentialsPath)
    {
        $client = new Client();
        $client->setApplicationName("Google Sheets API Integration");
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig($credentialsPath);
        $client->setAccessType('offline');

        $service = new Sheets($client);
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);

        return $response->getValues();
    }

}
