<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Kiểm tra xem người dùng đã đăng nhập hay chưa
        if (Auth::check()) {
            // Người dùng đã đăng nhập
            return view('dashboard');  // Điều hướng đến trang dashboard
        } else {
            // Người dùng chưa đăng nhập
            return redirect('/');  // Điều hướng đến trang đăng nhập
        }
    }
}