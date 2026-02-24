@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendance_detail.css') }}">
@endsection

@section('content')
<main class="attendance-detail">
    <h2 class="attendance-detail-ttl">勤怠詳細</h2>
    <form action="{{ route('attendance_correction', ['id' => $attendance->id ?? 0]) }}" method="POST">
        @csrf
        <table class="attendance-detail-table">
            <tbody>
                <tr class="attendance-detail-row">
                    <td class="attendance-detail-label">名前</td>
                    <td class="attendance-detail-name">{{ auth()->user()->name }}</td>
                </tr>

                <tr class="attendance-detail-row">
                    <td class="attendance-detail-label">日付</td>
                    <td class="attendance-detail-cell">
                        <span class="attendance-detail-year">{{ $date->format('Y年') }}</span>
                        <span class="attendance-detail-date">{{ $date->format('n月j日') }}</span>
                    </td>
                </tr>

                @if ($errors->has('clock_in') || $errors->has('clock_out') || $errors->has('clock_time'))
                <tr>
                    <td></td>
                    <td class="attendance-detail-error">
                        @error('clock_in') {{ $message }} @enderror
                        @error('clock_out') {{ $message }} @enderror
                        @error('clock_time') {{ $message }} @enderror
                    </td>
                </tr>
                @endif
                <tr class="attendance-detail-row">
                    <td class="attendance-detail-label">出勤・退勤</td>
                    <td class="attendance-detail-cell">
                        @php
                            $pendingClockIn = optional(
                                $pendingRequest?->details->firstWhere('field', 'clock_in')
                            )->new_value;
                            $pendingClockOut = optional(
                                $pendingRequest?->details->firstWhere('field', 'clock_out')
                            )->new_value;
                        @endphp
                        @if ($hasPendingRequest)
                            <span class="attendance-detail-value">
                                {{ $pendingClockIn ?? optional($attendance?->clock_in)->format('H:i')}}
                            </span>
                            <span class="attendance-detail-separator">〜</span>
                            <span class="attendance-detail-value">
                                {{ $pendingClockOut ?? optional($attendance?->clock_out)->format('H:i')}}
                            </span>
                        @else
                            <input
                                type="text"
                                name="clock_in"
                                value="{{ old('clock_in', optional($attendance?->clock_in)->format('H:i')) }}"
                                class="attendance-detail-input"
                            >
                            <span class="attendance-detail-separator">〜</span>
                            <input
                                type="text"
                                name="clock_out"
                                value="{{ old('clock_out', optional($attendance?->clock_out)->format('H:i')) }}"
                                class="attendance-detail-input"
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

                        // 元データ
                        $attendanceBreak = $attendance->breakTimes[$i] ?? null;

                        // 申請中データ
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

                    {{-- 休憩エラー表示 --}}
                    @if (!$hasPendingRequest && ($errors->has("break_start.$i") || $errors->has("break_end.$i")))
                        <tr>
                            <td></td>
                            <td class="attendance-detail-error">
                                @error("break_start.$i")
                                    {{ $message }}
                                @enderror

                                @error("break_end.$i")
                                    {{ $message }}
                                @enderror
                            </td>
                        </tr>
                    @endif

                    <tr class="attendance-detail-row">
                        <td class="attendance-detail-label">{{ $label }}</td>
                        <td class="attendance-detail-cell">

                            @if ($hasPendingRequest)
                                {{-- 承認待ち：span表示 --}}
                                <span class="attendance-detail-value">
                                    {{ $pendingStart ?? optional($attendanceBreak?->break_start)->format('H:i') ?? '' }}
                                </span>
                                <span class="attendance-detail-separator">〜</span>
                                <span class="attendance-detail-value">
                                    {{ $pendingEnd ?? optional($attendanceBreak?->break_end)->format('H:i') ?? '' }}
                                </span>
                            @else
                                {{-- 編集可：input --}}
                                <input
                                    type="text"
                                    name="break_start[]"
                                    value="{{ old("break_start.$i", optional($attendanceBreak?->break_start)->format('H:i')) }}"
                                    class="attendance-detail-input"
                                >
                                <span class="attendance-detail-separator">〜</span>
                                <input
                                    type="text"
                                    name="break_end[]"
                                    value="{{ old("break_end.$i", optional($attendanceBreak?->break_end)->format('H:i')) }}"
                                    class="attendance-detail-input"
                                >
                            @endif

                        </td>
                    </tr>
                @endfor

                @if ($errors->has('remark'))
                    <td></td>
                    <td class="attendance-detail-error">
                        @error('remark') {{ $message }} @enderror
                    </td>
                @endif
                <tr class="attendance-detail-row">
                    <td class="attendance-detail-label">備考</td>
                    <td class="attendance-detail-cell">
                        @if ($hasPendingRequest)
                            @php
                                $pendingRemark = optional(
                                    $pendingRequest->details->firstWhere('field', 'remark')
                                )->new_value;
                            @endphp
                            <div class="attendance-detail-value">
                                {{ $pendingRemark ?? $attendance->remark ?? '—' }}
                            </div>
                        @else
                            <textarea
                                name="remark"
                                class="attendance-detail-remark"
                            >{{ old('remark', $attendance->remark ?? '') }}</textarea>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="attendance_id" value="{{ $attendance->id ?? null }}">
        <input type="hidden" name="target_date" value="{{ $date->toDateString() }}">
        <div class="attendance-detail-action">
            @if ($hasPendingRequest)
                <p class="attendance-detail-pending">
                    *承認待ちのため修正はできません。
                </p>
            @else
                <button class="attendance-detail-submit">修正</button>
            @endif
        </div>
    </form>
</main>
@endsection