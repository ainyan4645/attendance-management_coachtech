@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify.css') }}">
@endsection

@section('content')
<main class="verify-contents">
    <div class="verify-box">
        <p class="verify-text">登録していただいたメールアドレスに認証メールを送付しました。<br>メール認証を完了してください。</p>

        <form method="POST" action="{{ route('verification.auto') }}" class="verify-form">
            @csrf
            <button type="submit" class="verify-btn">認証はこちらから</button>
        </form>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="resend-link">認証メールを再送する</button>
        </form>
    </div>
</main>
@endsection
