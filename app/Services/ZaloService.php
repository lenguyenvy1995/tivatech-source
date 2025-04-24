<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ZaloService
{
    protected $accessToken;
    protected $refreshToken;
    protected $client;

    public function __construct()
    {
        $this->refreshToken = env('ZALO_REFRESH_TOKEN'); // Đảm bảo refresh_token có trong .env
        $this->client = new Client();

        // Kiểm tra nếu access token có trong Cache hoặc đã hết hạn, làm mới token nếu cần
        $this->accessToken = Cache::get('zalo_access_token') ?? $this->refreshAccessToken();
    }

    public function sendMessage($userId, $message)
    {
        // Kiểm tra xem `access_token` có trong Cache không, nếu không thì làm mới
        if (!$this->accessToken) {
            $this->accessToken = $this->refreshAccessToken();
        }

        $url = 'https://openapi.zalo.me/v3.0/oa/message/cs';
        $params = [
            'json' => [
                'recipient' => ['user_id' => $userId],
                'message' => [
                    'text' => $message,
                ],
            ],
            'headers' => [
                'access_token' => $this->accessToken,
            ],
        ];

        try {
            $response = $this->client->post($url, $params);
            $result = json_decode($response->getBody(), true);
            return $result['message'] ?? 'Gửi tin nhắn thành công';
        } catch (\Exception $e) {
            return 'Lỗi khi gửi tin nhắn: ' . $e->getMessage();
        }
    }

    public function refreshAccessToken()
    {
        $url = 'https://oauth.zaloapp.com/v4/oa/access_token';
        $response = $this->client->post($url, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'secret_key' => env('ZALO_APP_SECRET'),
            ],
            'form_params' => [
                'app_id' => env('ZALO_APP_ID'),
                'refresh_token' => $this->refreshToken,
                'grant_type' => 'refresh_token',
            ],
        ]);

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        if (isset($data['access_token'])) {
            $this->accessToken = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 86400; // 24 giờ mặc định

            // Lưu access_token vào Cache
            Cache::put('zalo_access_token', $this->accessToken, $expiresIn);

            // Cập nhật refresh_token mới và lưu vào .env
            if (isset($data['refresh_token'])) {
                $this->updateEnvFile('ZALO_OA_ACCESS_TOKEN', $this->accessToken);
                $this->updateEnvFile('ZALO_REFRESH_TOKEN', $data['refresh_token']);
                $this->refreshToken = $data['refresh_token'];
            }
            return $this->accessToken;
        } else {
            throw new \Exception('Không tìm thấy access_token trong phản hồi của API: ' . $body);
        }
    }
    protected function updateEnvFile($key, $value)
    {
        $path = base_path('.env');
        if (File::exists($path)) {
            // Đọc nội dung hiện tại của file .env
            $env = File::get($path);
            // Tìm và thay thế giá trị của biến môi trường
            $env = preg_replace('/^' . $key . '=.*/m', $key . '=' . $value, $env);
            // Ghi lại file .env với giá trị mới
            File::put($path, $env);
        }
    }
}
