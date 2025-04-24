<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class ZaloWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Ghi lại thông tin Webhookd vào log để kiểm tra
        Log::info('Zalo Webhook Data:', $request->all());

        // Kiểm tra sự kiện từ Zalo, ví dụ 'user_send_text' khi người dùng gửi tin nhắn đến OA
        if ($request->event_name === 'user_send_text') {
            $userId = $request->sender['id']; // Lấy user_id từ sender.id
            $message = $request->message['text']; // Lấy nội dung tin nhắn
            // Log lại để kiểm tra
            Log::info('User ID:', [$userId]);
            Log::info('Message:', [$message]);
        }
        return response()->json(['status' => 'success']);
    }
}
