@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendance.css') }}">
@endsection

@section('content')
<main class="contents">
    {{-- 勤務外タグ --}}
    <div class="tag">
        勤務外
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
    <form action="" method="">
        @csrf
        <button class="work-btn">
            出勤
        </button>
    </form>
</main>
@endsection