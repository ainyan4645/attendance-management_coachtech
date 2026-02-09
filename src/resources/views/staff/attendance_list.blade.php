@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendance_list.css') }}">
@endsection

@section('content')
<main class="attendance">
    <h2 class="attendance-ttl">勤怠一覧</h2>

    <div class="attendance-month-nav">
        <a href="{{ route('attendance_list', ['month' => $prevMonth]) }}" class="nav-btn">
            <img src="{{ asset('img/left.png')}}" alt="back" class="nav-arrow-left">
            <span class="nav-last">前月</span>
        </a>

        <div class="month-display">
            <img src="{{ asset('img/calendar.png') }}" alt="calendar" class="month-display-img">
            {{ $currentMonth->format('Y/m') }}
        </div>

        <a href="{{ route('attendance_list', ['month' => $nextMonth]) }}" class="nav-btn">
            <span class="nav-next">翌月</span>
            <img src="{{ asset('img/right.png')}}" alt="next" class="nav-arrow-right">
        </a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr class="table-row-header">
                <th class="table-head-date">日付</th>
                <th class="table-head">出勤</th>
                <th class="table-head">退勤</th>
                <th class="table-head">休憩</th>
                <th class="table-head">合計</th>
                <th class="table-head-detail">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dates as $date)
            @php
                $attendance = $attendances[$date->toDateString()] ?? null;
            @endphp
            <tr class="table-row">
                <td class="table-cell-date">
                    {{ $date->locale('ja')->isoFormat('MM/DD(ddd)') }}
                </td>
                <td class="table-cell">
                    {{ optional($attendance?->clock_in)->format('H:i') }}
                </td>
                <td class="table-cell">
                    {{ optional($attendance?->clock_out)->format('H:i') }}
                </td>
                <td class="table-cell">
                    {{ $attendance
                        ? $attendance->formatMinutesToTime($attendance->total_break_minutes)
                        : '' }}
                </td>
                <td class="table-cell">
                    {{ $attendance
                        ? $attendance->formatMinutesToTime($attendance->total_work_minutes)
                        : '' }}
                </td>
                <td>
                    <form
                        action="{{ route('attendance_detail', ['id' => $attendance->id ?? 0]) }}"
                        method="GET"
                    >
                        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                        <button type="submit" class="table-cell-detail">
                            詳細
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</main>
@endsection