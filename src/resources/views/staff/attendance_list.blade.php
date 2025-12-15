@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendance_list.css') }}">
@endsection

@section('content')
<main class="attendance">
    <h2 class="attendance-ttl">勤怠一覧</h2>

    <div class="attendance-month-nav">
        <a href="" class="nav-btn">
            <img src="{{ asset('img/left.png')}}" alt="back" class="nav-arrow-left">
            <span class="nav-last">前月</span>
        </a>

        <div class="month-display">
            <img src="{{ asset('img/calendar.png') }}" alt="calendar" class="month-display-img">
            {{ $currentMonth?->format('Y/m') }}
        </div>

        <a href="" class="nav-btn">
            <span class="nav-next">翌月</span>
            <img src="{{ asset('img/right.png')}}" alt="next" class="nav-arrow-right">
        </a>
    </div>

    <table class="attendance-table">
        <thead  class="table-header">
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            <tr class="table-row">
                <td>{{ $attendance->date->format('m/d(D)') }}</td>
                <td>{{ $attendance->start_time }}</td>
                <td>{{ $attendance->end_time }}</td>
                <td>{{ $attendance->break_time }}</td>
                <td>{{ $attendance->total_time }}</td>
                <td>
                    <a href="" class="detail-link">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</main>
@endsection