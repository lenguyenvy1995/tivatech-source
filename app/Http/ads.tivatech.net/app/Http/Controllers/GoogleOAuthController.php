<?php

namespace App\Http\Controllers;

use Google_Client;
use Illuminate\Http\Request;

class GoogleOAuthController extends Controller
{
    // Bước 1: Chuyển hướng người dùng đến trang đăng nhập của Google
    public function redirectToGoogle()
    {
        // Tạo một đối tượng Google_Client
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->addScope('https://www.googleapis.com/auth/adwords');
        $client->setAccessType('offline'); // Để yêu cầu refresh token
        
        // Chuyển hướng người dùng đến Google để đăng nhập
        return redirect()->away($client->createAuthUrl());
    }

    // Bước 2: Xử lý callback từ Google và lấy Refresh Token
    public function handleGoogleCallback(Request $request)
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->addScope('https://www.googleapis.com/auth/adwords');
        $client->setAccessType('offline');

        // Kiểm tra nếu có mã xác thực
        if ($request->has('code')) {
            // Lấy mã xác thực (authorization code) từ URL
            $token = $client->fetchAccessTokenWithAuthCode($request->input('code'));

            // Kiểm tra nếu có lỗi trong quá trình xác thực
            if (isset($token['error'])) {
                return redirect('/')->withErrors(['error' => $token['error_description']]);
            }

            // Lấy Refresh Token
            $refreshToken = $client->getRefreshToken();

            // Hiển thị Refresh Token
            return response()->json([
                'access_token' => $token['access_token'],
                'refresh_token' => $refreshToken,
                'expires_in' => $token['expires_in']
            ]);
        }

        return redirect('/')->withErrors(['error' => 'Không có mã xác thực!']);
    }
}