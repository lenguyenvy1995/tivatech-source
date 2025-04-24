<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;
use App\Models\QuoteDomain;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\ZaloService;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($quote_request_id)
    {
        $quoteRequest = QuoteRequest::findOrFail($quote_request_id);
        return view('quotes.create', compact('quoteRequest'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
     
        $validated = $request->validate([
            'quote_request_id' => 'required|exists:quote_requests,id',
            'estimated_cost' => 'required|string',
        ]);

        $quote = new Quote();
        $quote->quote_request_id = $request['quote_request_id'];
        $quote->user_id = auth()->id();
        $quote->estimated_cost = $request['estimated_cost'];
        $quote->details = $request['details'];
        $quote->status = 'sent';
        $quote->save();

        $quoteRequest = QuoteRequest::find($request['quote_request_id']);
        $quoteRequest->status = 'quoted';
        $quoteRequest->save();
        $message = str_replace('<br>', "\n", '<br>BÁO GIÁ TRỌN GÓI QUẢNG CÁO GOOGLE CÔNG TY TIVATECH <br>1. Từ khóa:<br>' . $quoteRequest->keywords . '<br>===============<br>2. Giá:<br>' . $quote->estimated_cost . '<br>3. Website: https://' . $quoteRequest->quoteDomain->name . '<br>4. Khu vực: ' . $quoteRequest->region . '<br>5. Loại từ khóa: ' . implode(', ', $quoteRequest->keyword_type) . '<br>6. Hình thức: ' . implode(', ', $quoteRequest->campaign_type) . '<br>7. Thời gian: 6h-22h<br>8. Thiết bị hiển thị: Trên tất cả các thiết bị <br>.' . route('baogia', $quoteRequest->id));
        $zaloService=new zaloService;
        $zaloService->sendMessage($quoteRequest->user->zalo_user_id,$message);
        return redirect()->route('quote-requests.all')->with('success', 'Báo giá đã được tạo.');
    }
    public function countQuotesByQuoteDomain($domainName)
    {
        $quoteDomain = QuoteDomain::where('name', $domainName)->first();

        if (!$quoteDomain) {
            return response()->json(['message' => 'Quote domain không tồn tại.'], 404);
        }

        $quoteCount = $quoteDomain->quotes()->count();

        return response()->json([
            'quote_domain' => $quoteDomain->name,
            'quote_count' => $quoteCount,
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $quoteRequest = QuoteRequest::with(['quoteDomain'])->findOrFail($id);
        $quote = $quoteRequest->quote; // Quan hệ 'quote' cần được định nghĩa trong model QuoteRequest
        return view('quotes.show', data: compact('quoteRequest', 'quote'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Tìm yêu cầu báo giá
        $quoteRequest = QuoteRequest::with(['quoteDomain'])->findOrFail($id);

        // Kiểm tra quyền truy cập
        if (!Auth::user()->hasRole(['admin', 'role-quote-manager']) && $quoteRequest->user_id != Auth::id()) {
            abort(403, 'Bạn không có quyền chỉnh sửa báo giá này.');
        }

        // Tìm báo giá liên quan (giả sử mỗi yêu cầu báo giá có một báo giá)
        $quote = $quoteRequest->quote; // Quan hệ 'quote' cần được định nghĩa trong model QuoteRequest

        // Nếu chưa có báo giá, có thể chuyển hướng hoặc hiển thị thông báo
        if (!$quote) {
            return redirect()->route('quote-requests.index')->with('error', 'Không tìm thấy báo giá cho yêu cầu này.');
        }

        return view('quotes.edit', compact('quoteRequest', 'quote'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Tìm yêu cầu báo giá
        $quoteRequest = QuoteRequest::findOrFail($id);

        // Kiểm tra quyền truy cập
        if (!Auth::user()->hasRole(['admin', 'role-quote-manager']) && $quoteRequest->user_id != Auth::id()) {
            abort(403, 'Bạn không có quyền chỉnh sửa báo giá này.');
        }
        // Tìm báo giá liên quan
        $quote = $quoteRequest->quote;
        // Nếu chưa có báo giá, chuyển hướng với thông báo lỗi
        if (!$quote) {
            return redirect()->route('quote_requests.all')->with('error', 'Không tìm thấy báo giá cho yêu cầu này.');
        }

        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'estimated_cost' => 'required|string|max:1000',
        ]);
        // Cập nhật báo giá
        $quote->user_id = Auth::id();
        $quote->fill($request->all());
        $quote->save();
        $message = str_replace('<br>', "\n", '<br>BÁO GIÁ TRỌN GÓI QUẢNG CÁO GOOGLE CÔNG TY TIVATECH <br>1. Từ khóa:<br>' . $quoteRequest->keywords . '<br>===============<br>2. Giá update:<br>' . $quote->estimated_cost . '<br>3. Website: https://' . $quoteRequest->quoteDomain->name . '<br>4. Khu vực: ' . $quoteRequest->region . '<br>5. Loại từ khóa: ' . implode(', ', $quoteRequest->keyword_type) . '<br>6. Hình thức: ' . implode(', ', $quoteRequest->campaign_type) . '<br>7. Thời gian: 6h-22h<br>8. Thiết bị hiển thị: Trên tất cả các thiết bị <br>.' . route('baogia', $quoteRequest->id));
        $zaloService=new zaloService;
        $zaloService->sendMessage($quoteRequest->user->zalo_user_id,$message);

        return redirect()->route('quote-requests.all')->with('success', 'Cập nhật báo giá thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
