<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <!-- css -->
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">
    @yield('css')
</head>
<body class="body-color">
    <header class="header-inner">
        <a href="/" class="header-logo-inner">
            <img src="{{ asset('img/logo.svg') }}" alt="header-logo" class="header-logo_img">
        </a>
        @if (!isset($hideHeaderNav) || !$hideHeaderNav)
        <nav class="header-nav">
            <ul class="header-nav-inner">
                {{-- 1. 未ログイン（guest） --}}
                @if (!isset($headerType) || $headerType === 'guest')
                    {{-- ログインページなど → ナビなし --}}
                @endif

                {{-- 2. スタッフログイン時 --}}
                @if (isset($headerType) && $headerType === 'staff_logged_in')
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('attendance') }}">勤怠</a>
                    </li>
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('attendance_list') }}">勤怠一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('stamp_correction_list') }}">申請</a>
                    </li>
                    <li class="header-nav-ttl">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="header-nav-txt">ログアウト</button>
                        </form>
                    </li>
                @endif

                {{-- 3. スタッフ退勤後 --}}
                @if (isset($headerType) && $headerType === 'staff_after_clockout')
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('attendance_list') }}">今月の出勤一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('stamp_correction_list') }}">申請一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="header-nav-txt">ログアウト</button>
                        </form>
                    </li>
                @endif

                {{-- 4. 管理者ログイン時 --}}
                @if (isset($headerType) && $headerType === 'admin_logged_in')
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('attendance_list') }}">勤怠一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('staff_list') }}">スタッフ一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('stamp_correction_list') }}">申請一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="header-nav-txt">ログアウト</button>
                        </form>
                    </li>
                @endif

            </ul>
        </nav>
        @endif
    </header>
    @yield('content')
</body>
</html>