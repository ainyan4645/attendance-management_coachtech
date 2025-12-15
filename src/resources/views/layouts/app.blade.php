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
        <div class="header-logo-inner">
            <img src="{{ asset('img/logo.png') }}" alt="header-logo" class="header-logo_img">
        </div>
        @if (!isset($hideHeaderNav) || !$hideHeaderNav)
        <nav class="header-nav">
            <ul class="header-nav-inner">
                {{-- 未ログイン --}}
                @if(!Auth::guard('web')->check() && !Auth::guard('admin')->check())
                    {{-- ナビなし --}}
                @endif

                {{-- 一般ユーザー --}}
                @if(Auth::guard('web')->check())
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('attendance') }}">勤怠</a>
                    </li>
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('attendance_list') }}">勤怠一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="">申請</a>
                    </li>
                    <li class="header-nav-ttl">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="header-nav-logout">ログアウト</button>
                        </form>
                    </li>
                @endif

                {{-- スタッフ退勤後 --}}
                @if (isset($headerType) && $headerType === 'staff_after_clockout')
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('attendance_list') }}">今月の出勤一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="">申請一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="header-nav-logout">ログアウト</button>
                        </form>
                    </li>
                @endif

                {{-- 管理者 --}}
                @if (Auth::guard('admin')->check())
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('admin_attendance_list') }}">勤怠一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('staff_list') }}">スタッフ一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <a class="header-nav-txt" href="{{ route('admin_stamp_correction_list') }}">申請一覧</a>
                    </li>
                    <li class="header-nav-ttl">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="header-nav-logout">ログアウト</button>
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