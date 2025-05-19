<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoogleAdsSearchController extends Controller
{
    public function index()
    {
        $locations = ['Hà Nội', 'TPHCM', 'Đà Nẵng', 'Bình Dương', 'Đồng Nai'];
        return view('ads_search.index', compact('locations'));
    }

    public function fetch(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string',
            'location' => 'required|string',
        ]);

        $keyword = $request->keyword . ' ' . $request->location;

        $response = Http::get('https://serpapi.com/search.json', [
            'engine' => 'google',
            'q' => $keyword,
           'api_key' => env('SERPAPI_KEY', '59b6888be49c9c8757320af80887a6dff0a59f130148f847d3d5c848e6689dea'),
        ]);

        $ads = $response->json('ads') ?? [];

        return view('ads_search.result', compact('ads', 'keyword'));
    }
    public function ajax(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string',
            'location' => 'required|string',
            'device' => 'required|string|in:desktop,mobile',
        ]);
    
        $keyword = $request->keyword;
    
        $locationMap = [
            'Hà Nội' => 'Hanoi,Vietnam',
            'TPHCM' => 'Ho Chi Minh City,Vietnam',
            'Đà Nẵng' => 'Da Nang,Vietnam',
            'Bình Dương' => 'Binh Duong,Vietnam',
            'Đồng Nai' => 'Dong Nai,Vietnam',
        ];
    
        $location_name = $locationMap[$request->location] ?? 'Vietnam';
    
        $response = Http::get('https://serpapi.com/search.json', [
            'engine' => 'google',
            'q' => $keyword,
            'location' => $location_name,
            'device' => $request->device,
            'api_key' => env('SERPAPI_KEY', '59b6888be49c9c8757320af80887a6dff0a59f130148f847d3d5c848e6689dea'),
        ]);
        if ($response->failed()) {
            return response()->json([
                'error' => 'Failed to fetch data from SerpApi',
            ], 500);
        }
        if ($response->json('error')) {
            return response()->json([
                'error' => $response->json('error'),
            ], 400);
        }
        return response()->json([
            'ads' => $response->json('ads') ?? [],
        ]);
    }
}
