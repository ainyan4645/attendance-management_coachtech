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
<body class="@guest bg-guest @else bg-auth @endguest">
    <header class="header-inner">
        <a href="/" class="header-logo-inner">
            <img src="{{ asset('img/logo.svg') }}" alt="header-logo" class="header-logo_img">
        </a>
        @if (!isset($hideHeaderNav) || !$hideHeaderNav)
        <nav class="header-nav">
            <ul class="header-nav-inner">
                @auth('admin')
                    {{-- adminログイン時 --}}
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
                @endauth

                @auth('web')
                    {{-- staffログイン時 --}}
                    @if (isset($userHasClockedOut) && $userHasClockedOut)
                        {{-- 退勤後の画面 --}}
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
                    @else
                        {{-- 通常ログイン中 --}}
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
                @endauth
            </ul>
        </nav>
        @endif
    </header>
    @yield('content')
</body>
</html>