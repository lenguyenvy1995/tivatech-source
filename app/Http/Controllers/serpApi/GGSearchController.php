<?php

namespace App\Http\Controllers\serpApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SerpApi\GoogleSearchResults; // Sử dụng lớp từ gói

class GGSearchController extends Controller
{
    protected $googleSearch;

    public function __construct()
    {
        // Khởi tạo GoogleSearchResults với API key từ .env
        $this->googleSearch = new GoogleSearchResults(env('SERPAPI_API_KEY'));
    }
    public function search(Request $request)
    {
        $q = $request->input('query');
        $query=[
            'q' => $q,
            'location' =>$request->input('location'), // Vị trí cụ thể
            'hl' => 'vi',  
            'gl' => 'vn',  
            'device' => 'mobile',  
            "google_domain" => "google.com.vn",
        ];

        try {
            // Thực hiện tìm kiếm
            $results = $this->googleSearch->get_json($query);
            return view('serpapi.results', compact('results'))->render();
        } catch (\Exception $e) {
            // Xử lý ngoại lệ
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
