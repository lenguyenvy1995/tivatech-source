<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class LoginController extends Controller
{
    public function redirectToGoogle()
    {
        // Yêu cầu quyền offline để lấy refresh token
        return Socialite::driver('google')
            ->with(['access_type' => 'offline','prompt' => 'consent'])
            ->stateless() // Sử dụng stateless để bỏ qua việc kiểm tra session state
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            // Lấy thông tin người dùng từ Google
            $user = Socialite::driver('google')->stateless()->user();

            // Lấy refresh token từ phản hồi của Google
            $refreshToken = $user->refreshToken;

            // Kiểm tra người dùng trong cơ sở dữ liệu
            $findUser = User::where('email', $user->email)->first();

            if ($findUser) {
                Auth::login($findUser);
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id' => $user->id,
                    'password' => encrypt('123456dummy')
                ]);

                Auth::login($newUser);
            }
            return redirect()->route('admin.ads.dashboard');
         } catch (\Exception $e) {
             return back()->withErrors(['error' => 'Có lỗi xảy ra trong quá trình xác thực!']);
         }
    }
}