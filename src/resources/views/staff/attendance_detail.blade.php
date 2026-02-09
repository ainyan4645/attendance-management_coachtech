@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendance_detail.css') }}">
@endsection

@section('content')
<main class="attendance-detail">
    <h2 class="attendance-detail-ttl">勤怠詳細</h2>
    <form action="{{ route('attendance_update', ['id' => $attendance->id ?? 0]) }}" method="POST">
        @csrf
        <table class="attendance-detail-table">
            <tbody>
                <tr class="attendance-detail-row">
                    <td class="attendance-detail-label">名前</td>
                    <td class="attendance-detail-name">{{ auth()->user()->name }}</td>
                </tr>

                <tr class="attendance-detail-row">
                    <td class="attendance-detail-label">日付</td>
                    <td class="attendance-detail-year">{{ $date->format('Y年') }}</td>
                    <td class="attendance-detail-date">{{ $date->format('n月j日') }}</td>
                </tr>

                <tr class="attendance-detail-row">
                    <td class="attendance-detail-label">出勤・退勤</td>
                    <td class="attendance-detail-work">
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
                            <p>〜</p>
                            <span class="attendance-detail-value">
                                {{ $pendingClockOut ?? optional($attendance?->clock_out)->format('H:i')}}
                            </span>
                        @else
                            <input
                                type="text"
                                name="clock_in"
                                value="{{ optional($attendance?->clock_in)->format('H:i') }}"
                                class="attendance-detail-input"
                            >
                            <p>〜</p>
                            <input
                                type="text"
                                name="clock_out"
                                value="{{ optional($attendance?->clock_out)->format('H:i') }}"
                                class="attendance-detail-input"
                            >
                        @endif
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

                @foreach ($attendance?->breakTimes ?? [] as $index => $break)
                @php
                    $label = $index === 0 ? '休憩' : '休憩' . ($index + 1);
                @endphp
                <tr class="attendance-detail-row">
                    <td class="attendance-detail-label">
                        {{ $label }}
                    </td>
                    <td class="attendance-detail-break">
                        @if ($hasPendingRequest)
                            <span class="attendance-detail-value">
                                {{ optional($break->break_start)->format('H:i') ?? '—' }}
                            </span>
                            <p>〜</p>
                            <span class="attendance-detail-value">
                                {{ optional($break->break_end)->format('H:i') ?? '—' }}
                            </span>
                        @else
                            <input
                                type="text"
                                name="break_start[]"
                                value="{{ optional($break->break_start)->format('H:i') }}"
                                class="attendance-detail-input"
                            >
                            <p>〜</p>
                            <input
                                type="text"
                                name="break_end[]"
                                value="{{ optional($break->break_end)->format('H:i') }}"
                                class="attendance-detail-input"
                            >
                        @endif
                    </td>
                </tr>

                @if ($errors->has("break_start.$index") || $errors->has("break_end.$index"))
                <tr>
                    <td></td>
                    <td class="attendance-detail-error">
                        @error("break_start.$index") {{ $message }} @enderror
                        @error("break_end.$index") {{ $message }} @enderror
                    </td>
                </tr>
                @endif
                @endforeach

                @if (! $hasPendingRequest)
                    @php
                        $newIndex = $attendance?->breakTimes->count() ?? 0;
                        $label = $newIndex === 0 ? '休憩' : '休憩' . ($newIndex + 1);
                    @endphp
                    <tr class="attendance-detail-row">
                        <td class="attendance-detail-label">
                            {{ $label }}
                        </td>
                        <td class="attendance-detail-break">
                            <input
                                type="text"
                                name="break_start[]"
                                class="attendance-detail-input"
                            >
                            <p>〜</p>
                            <input
                                type="text"
                                name="break_end[]"
                                class="attendance-detail-input"
                            >
                        </td>
                    </tr>
                    @if ($errors->has("break_start.$newIndex") || $errors->has("break_end.$newIndex"))
                    <tr>
                        <td></td>
                        <td class="attendance-detail-error">
                            @error("break_start.$newIndex") {{ $message }} @enderror
                            @error("break_end.$newIndex") {{ $message }} @enderror
                        </td>
                    </tr>
                    @endif
                @endif

                <tr class="attendance-detail-row">
                    <td class="attendance-detail-label">備考</td>
                    <td>
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
                            >{{ $attendance->remark ?? '' }}</textarea>
                            @error('remark')
                                <p class="attendance-detail-error">{{ $message }}</p>
                            @enderror
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

    </table>
</main>
@endsection