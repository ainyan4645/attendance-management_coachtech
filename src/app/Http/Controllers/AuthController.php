<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register()
    {
        $headerType = 'guest'; // 未ログイン
        return view('auth.register', compact('headerType'));
    }

    public function login()
    {
        $headerType = 'guest';
        return view('auth.login', compact('headerType'));
    }

    public function adminLogin()
    {
        $headerType = 'guest';
        return view('auth.admin_login', compact('headerType'));
    }
}
