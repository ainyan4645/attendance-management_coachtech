@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<main class="contents">
    <h1 class="contents-ttl">
        会員登録
    </h1>
    <form class="register" action="/register" method="POST" novalidate>
        @csrf
        <h2 class="user">
            名前
        </h2>
        @error('name')
        <div class="error">{{ $message }}</div>
        @enderror
        <input type="text" name="name" value="{{ old('name') }}" class="user-input">

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

        <h2 class="pwd-confirm">
            パスワード確認
        </h2>
        <input type="password" name="password_confirmation" class="pwd-confirm-input">

        <button class="register-btn" type="submit">登録する</button>
    </form>
    <a href="{{ route('login') }}" class="login-link">ログインはこちら</a>
</main>
@endsection