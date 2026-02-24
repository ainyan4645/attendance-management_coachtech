@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendance.css') }}">
@endsection

@section('content')
<main class="contents">
    {{-- 勤務外タグ --}}
    <div class="tag">
        @switch($status)
            @case('not_working')
                勤務外
                @break
            @case('working')
                出勤中
                @break
            @case('on_break')
                休憩中
                @break
            @case('finished')
                退勤済
                @break
        @endswitch
    </div>

    {{-- 日付 --}}
    <p class="date">
        {{ $today->isoFormat('YYYY年M月D日(dd)') }}
    </p>

    {{-- 時刻 --}}
    <p class="time">
        {{ $now->format('H:i') }}
    </p>

    {{-- 出勤ボタン --}}
    <div class="attendance-register">
        {{-- 出勤前 --}}
        @if ($status === 'not_working')
            <form action="{{ route('attendance.clockIn') }}" method="POST">
                @csrf
                <button class="work-btn">出勤</button>
            </form>
        @endif

        {{-- 出勤中 --}}
        @if ($status === 'working')
            <form action="{{ route('attendance.clockOut') }}" method="POST">
                @csrf
                <button class="work-btn">退勤</button>
            </form>

            <form action="{{ route('attendance.breakStart') }}" method="POST">
                @csrf
                <button class="break-btn">休憩入</button>
            </form>
        @endif

        {{-- 休憩中 --}}
        @if ($status === 'on_break')
            <form action="{{ route('attendance.breakEnd') }}" method="POST">
                @csrf
                <button class="break-btn">休憩戻</button>
            </form>
        @endif

        {{-- 退勤済 --}}
        @if ($status === 'finished')
            <p class="thanks-message">お疲れ様でした。</p>
        @endif
    </div>
</main>
@endsection