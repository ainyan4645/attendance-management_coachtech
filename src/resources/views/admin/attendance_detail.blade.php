@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_detail.css') }}">
@endsection

@section('content')
<main class="attendance-detail">
    <h2 class="attendance-detail-ttl">勤怠詳細</h2>

    <form action="{{ route('admin_attendance_update', ['id' => $attendance->id ?? 0]) }}" method="POST">
        @csrf

        <table class="detail-table">
            <tbody>
                <tr class="detail-row">
                    <td class="detail-label">名前</td>
                    <td class="detail-name">
                        {{ $attendance?->user->name ?? $user->name  ?? '' }}
                    </td>
                </tr>

                <tr class="detail-row">
                    <td class="detail-label">日付</td>
                    <td class="detail-cell">
                        <span class="detail-cell-year">{{ $date->format('Y年') }}</span>
                        <span class="detail-cell-date">{{ $date->format('n月j日') }}</span>
                    </td>
                </tr>

                @if ($errors->has('clock_in') || $errors->has('clock_out') || $errors->has('clock_time'))
                <tr>
                    <td></td>
                    <td colspan="2" class="detail-error">
                        @error('clock_in') {{ $message }} @enderror
                        @error('clock_out') {{ $message }} @enderror
                        @error('clock_time') {{ $message }} @enderror
                    </td>
                </tr>
                @endif
                <tr class="detail-row">
                    <td class="detail-label">出勤・退勤</td>
                    <td class="detail-cell">
                    @php
                        $pendingClockIn = optional(
                            $pendingRequest?->details->firstWhere('field', 'clock_in')
                        )->new_value;
                        $pendingClockOut = optional(
                            $pendingRequest?->details->firstWhere('field', 'clock_out')
                        )->new_value;
                    @endphp

                    @if ($hasPendingRequest)
                        <span class="detail-cell-work">
                            {{ $pendingClockIn ?? $attendance?->clock_in?->format('H:i') }}
                        </span>
                        <span class="detail-cell-separator">〜</span>
                        <span class="detail-cell-work">
                            {{ $pendingClockOut ?? $attendance?->clock_out?->format('H:i') }}
                        </span>
                    @else
                        <input
                            type="text"
                            name="clock_in"
                            value="{{ old('clock_in', optional($attendance?->clock_in)->format('H:i')) }}"
                            class="detail-cell-input"
                        >
                        <span class="detail-cell-separator">〜</span>
                        <input
                            type="text"
                            name="clock_out"
                            value="{{ old('clock_out', optional($attendance?->clock_out)->format('H:i')) }}"
                            class="detail-cell-input"
                        >
                    @endif
                    </td>
                </tr>

                @php
                    $baseBreakCount = $attendance?->breakTimes->count() ?? 0;
                    $displayCount = $baseBreakCount + 1;
                @endphp

                @for ($i = 0; $i < $displayCount; $i++)
                    @php
                        $label = $i === 0 ? '休憩' : '休憩' . ($i + 1);

                        $attendanceBreak = $attendance->breakTimes[$i] ?? null;

                        $pendingStart = optional(
                            $pendingRequest?->details->firstWhere('field', 'break_start_' . ($i + 1))
                        )->new_value;

                        $pendingEnd = optional(
                            $pendingRequest?->details->firstWhere('field', 'break_end_' . ($i + 1))
                        )->new_value;
                    @endphp
                    @if (
                        $hasPendingRequest
                        && empty($pendingStart)
                        && empty($pendingEnd)
                        && empty($attendanceBreak)
                    )
                        @continue
                    @endif

                    {{-- エラー表示 --}}
                    @if (!$hasPendingRequest && ($errors->has("break_start.$i") || $errors->has("break_end.$i")))
                        <tr>
                            <td></td>
                            <td class="detail-error">
                                @error("break_start.$i") {{ $message }} @enderror
                                @error("break_end.$i") {{ $message }} @enderror
                            </td>
                        </tr>
                    @endif

                    <tr class="detail-row">
                        <td class="detail-label">{{ $label }}</td>
                        <td class="detail-cell">

                            @if ($hasPendingRequest)
                                {{-- span表示 --}}
                                <span class="detail-cell-break">
                                    {{ $pendingStart ?? optional($attendanceBreak?->break_start)->format('H:i') ?? '' }}
                                </span>
                                <span class="detail-cell-separator">〜</span>
                                <span class="detail-cell-break">
                                    {{ $pendingEnd ?? optional($attendanceBreak?->break_end)->format('H:i') ?? '' }}
                                </span>
                            @else
                                {{-- input表示 --}}
                                <input
                                    type="text"
                                    name="break_start[]"
                                    value="{{ old("break_start.$i", optional($attendanceBreak?->break_start)->format('H:i')) }}"
                                    class="detail-cell-input"
                                >
                                <span class="detail-cell-separator">〜</span>
                                <input
                                    type="text"
                                    name="break_end[]"
                                    value="{{ old("break_end.$i", optional($attendanceBreak?->break_end)->format('H:i')) }}"
                                    class="detail-cell-input"
                                >
                            @endif

                        </td>
                    </tr>
                @endfor

                @if ($errors->has('remark'))
                <tr>
                    <td></td>
                    <td class="detail-error">
                        @error('remark') {{ $message }} @enderror
                    </td>
                </tr>
                @endif
                <tr class="detail-row">
                    <td class="detail-label">備考</td>
                    <td class="detail-cell">
                        @if ($hasPendingRequest)
                            @php
                                $pendingRemark = optional(
                                    $pendingRequest?->details->firstWhere('field', 'remark')
                                )->new_value;
                            @endphp
                            <div class="detail-value">
                                {{ $pendingRemark ?? $attendance->remark ?? '—' }}
                            </div>
                        @else
                            <textarea
                                name="remark"
                                class="detail-remark"
                            >{{ old('remark', $attendance->remark ?? '') }}</textarea>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <input type="hidden" name="target_date" value="{{ $date->toDateString() }}">
        <input type="hidden" name="return_url" value="{{ $returnUrl }}">

        <div class="detail-action">
            @if ($hasPendingRequest)
                <p class="detail-pending">
                    *承認待ちのため修正はできません。
                </p>
            @else
                <button class="detail-submit">修正</button>
            @endif
        </div>
    </form>
</main>
@endsection