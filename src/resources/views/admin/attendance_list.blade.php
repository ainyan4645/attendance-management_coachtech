@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_list.css') }}">
@endsection

@section('content')
<main class="attendance">
    <h2 class="attendance-ttl">
        {{ $date->isoFormat('YYYY年M月D日') }}の勤怠
    </h2>

    <div class="attendance-month-nav">
        <a href="{{ route('admin_attendance_list', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="nav-btn">
            <img src="{{ asset('img/left.png')}}" alt="back" class="nav-arrow-left">
            <span class="nav-last">前日</span>
        </a>

        <div class="month-display">
            <form method="GET" action="{{ route('admin_attendance_list') }}">
                <label for="date-picker">
                    <img src="{{ asset('img/calendar.png') }}" alt="calendar" class="month-display-img">
                </label>
                <input
                    type="date"
                    id="date-picker"
                    name="date"
                    value="{{ $date->toDateString() }}"
                    onchange="this.form.submit()"
                    class="month-display-picker"
                >
            </form>
            {{ $date->isoFormat('YYYY/MM/DD') }}
        </div>

        <a href="{{ route('admin_attendance_list', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="nav-btn">
            <span class="nav-next">翌日</span>
            <img src="{{ asset('img/right.png')}}" alt="next" class="nav-arrow-right">
        </a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr class="table-row-header">
                <th class="table-name">名前</th>
                <th class="table-clock-in">出勤</th>
                <th class="table-clock-out">退勤</th>
                <th class="table-break">休憩</th>
                <th class="table-total">合計</th>
                <th class="table-detail">詳細</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($attendances as $attendance)
            <tr class="table-row">
                <td class="table-cell-name">
                    {{ $attendance->user->name }}
                </td>
                <td class="table-cell">
                    {{ optional($attendance->clock_in)->format('H:i') }}
                </td>
                <td class="table-cell">
                    {{ optional($attendance->clock_out)->format('H:i') }}
                </td>
                <td class="table-cell">
                    {{ $attendance->formatMinutesToTime($attendance->total_break_minutes) }}
                </td>
                <td class="table-cell">
                    {{ $attendance->formatMinutesToTime($attendance->total_work_minutes) }}
                </td>
                <td>
                    <form
                        action="{{ route('admin_attendance_detail', ['id' => $attendance->id]) }}"
                        method="GET"
                    >
                    @csrf
                        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                        <input type="hidden" name="return_url" value="{{ url()->current() }}">
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