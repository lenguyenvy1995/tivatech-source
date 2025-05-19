<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdsTransparencyController extends Controller
{
    public function index()
    {
        return view('ads_transparency.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'advertiser' => 'required|string',
            'country' => 'required|string',
        ]);

        $response = Http::get('https://ads-transparency.google.com/api/v1/ads/search', [
            'advertiser_name' => $request->advertiser,
            'country' => $request->country,
            'limit' => 20,
        ]);

        if ($response->failed()) {
            return back()->with('error', 'Không thể kết nối đến Transparency API');
        }

        $ads = $response->json('ads') ?? [];

        return view('ads_transparency.index', [
            'ads' => $ads,
            'keyword' => $request->advertiser,
            'country' => $request->country,
        ]);
    }
    public function ajax(Request $request)
    {
        $request->validate([
            'advertiser' => 'required|string',
        ]);
    
        $advertiserId = $request->advertiser;
    
        $response = Http::get('https://serpapi.com/search.json', [
            'engine' => 'google_ads_transparency_center',
            'advertiser_id' => $advertiserId,
            'region' => 2840,
            'api_key' => env('SERPAPI_KEY', '59b6888be49c9c8757320af80887a6dff0a59f130148f847d3d5c848e6689dea'),
        ]);
    
        return response()->json([
            'ads' => $response->json('ad_creatives') ?? [],
        ]);
    }

    public function suggest(Request $request)
    {
        $request->validate(['q' => 'required|string']);

        $response = Http::get('https://serpapi.com/search', [
            'engine' => 'google_ads_transparency_center',
            'q' => $request->q,
            'api_key' => env('SERPAPI_KEY'),
        ]);

        return response()->json($response->json('advertisers') ?? []);
    }
}