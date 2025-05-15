<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleAdsService;

class KeywordPlannerController extends Controller
{
    public function index(GoogleAdsService $service)
    {
        $locations = $service->getVietnamLocations();

        return view('keywordPlanner.index', compact('locations'));
    }
    public function search(Request $request, GoogleAdsService $service)
    {
        $keywords = json_decode($request->input('keywords'), true);
        $keywordTexts = collect($keywords)->pluck('value')->toArray();
    
        $locationId = (int) $request->input('location');
    
        if (!$locationId || empty($keywordTexts)) {
            return response()->json(['data' => []]);
        }
    
        $data = $service->getSearchVolume($keywordTexts, $locationId);
    
        return response()->json(['data' => $data]);
    }
   
}
