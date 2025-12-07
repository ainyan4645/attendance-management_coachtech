@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/admin_login.css') }}">
@endsection

@section('content')
<main class="contents">
    <h1 class="contents-ttl">
        管理者ログイン
    </h1>
    <form class="admin-login" action="/login" method="POST" novalidate>
        @csrf
        <h2 class="email">
            メールアドレス
        </h2>
        @error('email')
        <div class="error">{{ $message }}</div>
        @enderror
        <input type="email" name="email" value="{{ old('email') }}" class="email-input">
        <h2 class="pwd">
            パスワード
        </h2>
        @error('password')
        <div class="error">{{ $message }}</div>
        @enderror
        <input type="password" name="password" class="pwd-input">
        <button class="login-btn">管理者ログインする</button>
    </form>
</main>
@endsection