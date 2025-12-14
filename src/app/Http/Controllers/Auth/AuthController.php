<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;


class AuthController extends Controller
{
    public function showRegister() {
        $headerType = 'guest'; // 未ログイン
        return view('auth.register', compact('headerType'));
    }

    public function register(RegisterRequest $request) {
        // フォームリクエストでバリデーション
        $validated = $request->validated();

        // CreateNewUser を直接呼び出してユーザー作成
        $creator = new CreateNewUser();
        $user = $creator->create($validated);

        // FortifyがRegister通知 → 認証メール送信（Mailtrapへ）
        event(new Registered($user));

        // ログインして認証待ちページへ
        Auth::login($user);
        return redirect()->route('verification.notice');
    }

    public function showLogin() {
        $headerType = 'guest';
        return view('auth.login', compact('headerType'));
    }

    public function Login(LoginRequest $request) {
        // 認証に必要な最小限だけ取り出す(会員登録→ログイン時用)
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return back()->withErrors(['email' => 'ログイン情報が正しくありません。'])->withInput();
        }

        // メール未認証のチェック
        if (!Auth::user()->hasVerifiedEmail()) {
            Auth::logout(); // ログアウトさせる
            return redirect()->route('verification.notice')->withErrors(['email' => 'メール認証が完了していません。']);
        }

        return redirect('/attendance');
    }

    public function logout(Request $request)
    {
        return redirect()->route('login');
    }

    public function notice() {
        $headerType = 'guest';
        return view('auth.verify', compact('headerType'));
    }

    public function verify(Request $request) {
        // 二重クリックや古いリンクの対策
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/attendance');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect('/attendance');
    }

    // 認証メール再送
    public function resend(Request $request) {
        // 認証済みならpass(再送なし)
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/attendance');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', '認証メールを再送しました。');
    }
}
