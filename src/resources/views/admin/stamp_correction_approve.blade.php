@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_approve.css') }}">
@endsection

@section('content')
<main class="attendance-detail">
    <h2 class="attendance-detail-ttl">勤怠詳細</h2>

    <form action="{{ route('admin_stamp_correction_approve', $pendingRequest->id) }}" method="POST">
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

                @php
                    $pendingClockIn = optional(
                        $pendingRequest?->details->firstWhere('field', 'clock_in')
                    )->new_value;

                    $pendingClockOut = optional(
                        $pendingRequest?->details->firstWhere('field', 'clock_out')
                    )->new_value;

                    $pendingRemark = optional(
                        $pendingRequest?->details->firstWhere('field', 'remark')
                    )->new_value;
                @endphp
                <tr class="detail-row">
                    <td class="detail-label">出勤・退勤</td>
                    <td class="detail-cell">
                        <span class="detail-cell-work">
                            {{ $pendingClockIn ?? $attendance?->clock_in?->format('H:i') }}
                        </span>
                        <span class="detail-cell-separator">〜</span>
                        <span class="detail-cell-work">
                            {{ $pendingClockOut ?? $attendance?->clock_out?->format('H:i') }}
                        </span>
                    </td>
                </tr>

                @php
                    $attendanceBreakCount = $attendance?->breakTimes->count() ?? 0;

                    // pending 側の休憩申請インデックス
                    $pendingIndexes = $pendingRequest?->details
                        ->filter(fn($d) => str_starts_with($d->field, 'break_start_'))
                        ->map(function ($d) {
                            return (int) str_replace('break_start_', '', $d->field);
                        }) ?? collect();

                    $pendingMaxIndex = $pendingIndexes->max() ?? 0;

                    // ★ ここを修正：元の勤怠 + 1 を最低表示枠にする
                    $displayCount = max($attendanceBreakCount + 1, $pendingMaxIndex);
                @endphp

                @for ($i = 0; $i < $displayCount; $i++)
                    @php
                        $label = $i === 0 ? '休憩' : '休憩' . ($i + 1);

                        // 元の勤怠の休憩を取得（存在しなければ null）
                        $attendanceBreak = $attendance?->breakTimes[$i] ?? null;

                        // 申請側の休憩
                        $pendingStart = optional(
                            $pendingRequest?->details->firstWhere('field', 'break_start_' . ($i + 1))
                        )->new_value;

                        $pendingEnd = optional(
                            $pendingRequest?->details->firstWhere('field', 'break_end_' . ($i + 1))
                        )->new_value;
                    @endphp

                    <tr class="detail-row">
                        <td class="detail-label">{{ $label }}</td>
                        <td class="detail-cell">
                                <span class="detail-cell-break">
                                    {{ $pendingStart ?? optional($attendanceBreak?->break_start)->format('H:i') ?? '' }}
                                </span>
                                <span class="detail-cell-separator">〜</span>
                                <span class="detail-cell-break">
                                    {{ $pendingEnd ?? optional($attendanceBreak?->break_end)->format('H:i') ?? '' }}
                                </span>
                        </td>
                    </tr>
                @endfor

                <tr class="detail-row">
                    <td class="detail-label">備考</td>
                    <td class="detail-cell">
                            <div class="detail-value">
                                {{ $pendingRemark ?? $attendance->remark ?? '—' }}
                            </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <input type="hidden" name="target_date" value="{{ $date->toDateString() }}">
        <input type="hidden" name="return_url" value="{{ $returnUrl }}">

        <div class="detail-action">
            @if ($isApproved)
                <p class="detail-approved">
                    承認済み
                </p>
            @else
                <button class="detail-submit">承認</button>
            @endif
        </div>
    </form>
</main>
@endsection