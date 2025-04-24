<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAdsController;
use App\Http\Controllers\GoogleAdsAppController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\GoogleOAuthController;
use Google\Api\Control;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;

Route::get('auth/google', [LoginController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/search', [GoogleAdsController::class, 'getTopAds']);


Route::get('/', function () {
    return view('index');
});

Route::name('admin.')->prefix('admin')->group(function () {
    Route::name('ads.')->prefix('ads')->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        //tạo tài khoản
        Route::get('/create-account', function () {
            return view('admin.ads.create_account');
        })->name('create-account');
        Route::post('/create-account-post', [GoogleAdsController::class, 'createCustomer'])->name('create-account-post');

        //tạo chiến dịch search
        Route::get('/create-campaign', function () {
            return view('admin.ads.create_campaign');
        })->name('create-campaign');
        Route::post('/create-campaign-post', [GoogleAdsController::class, 'createAll'])->name('create-campaign-post');
        //tạo chiến dịch
        Route::get('/create-campaignapp', function () {
            return view('admin.ads.create_campaignapp');
        })->name('create-campaignapp');
        Route::post('/create-campaignapp-post', [GoogleAdsAppController::class, 'createAppCampaign'])->name('app-campaign.create.post');

        //tạo nhóm quảng cáo
        Route::get('/create-adgroup', function () {
            return view('admin.ads.create_adgroup');
        })->name('create-adgroup');
        Route::post('/create-adgroup-post', [GoogleAdsController::class, 'createAdGroup'])->name('create-adgroup-post');
    });
});
Route::get('logout', function (Request $request) {

    // Xoá thông tin xác thực người dùng khỏi session
    Auth::logout();

    // Xoá tất cả session
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Chuyển hướng người dùng về trang chủ hoặc trang đăng nhập
    return redirect('/');
});
