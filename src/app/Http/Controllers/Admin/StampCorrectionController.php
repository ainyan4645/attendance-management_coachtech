<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StampCorrectionController extends Controller
{
    public function approveScreen($attendance_correct_request_id)
    {
        // ① 申請取得
        $pendingRequest = AttendanceRequest::with([
            'user',
            'details',
            'attendance.breakTimes'
        ])->findOrFail($attendance_correct_request_id);

        // ② 元の勤怠取得
        $attendance = $pendingRequest->attendance;

        // ③ 日付（対象勤怠日）
        $date = $pendingRequest->target_date;

        // ④ ステータス判定
        $isApproved = $pendingRequest->status === 'approved';

        // ⑤ 戻りURL
        $returnUrl = route('stamp_correction_list');

        return view(
            'admin.stamp_correction_approve',
            compact(
                'pendingRequest',
                'attendance',
                'date',
                'isApproved',
                'returnUrl'
            )
        );
    }

    public function approve($attendance_correct_request_id)
    {
        DB::transaction(function () use ($attendance_correct_request_id) {

            $request = AttendanceRequest::with([
                'details',
                'attendance.breakTimes'
            ])->findOrFail($attendance_correct_request_id);

            $attendance = $request->attendance;

            // 出退勤 差分更新
            foreach ($request->details as $detail) {

                if ($detail->field === 'clock_in' && $detail->new_value) {
                    $attendance->clock_in = Carbon::parse(
                        $attendance->date->format('Y-m-d') . ' ' . $detail->new_value
                    );
                }

                if ($detail->field === 'clock_out' && $detail->new_value) {
                    $attendance->clock_out = Carbon::parse(
                        $attendance->date->format('Y-m-d') . ' ' . $detail->new_value
                    );
                }
            }

            $attendance->save();

            // 休憩 差分更新
            $existingBreaks = $attendance->breakTimes()
                ->orderBy('id')
                ->get()
                ->values();

            foreach ($request->details as $detail) {

                if (!preg_match('/break_(start|end)_(\d+)/', $detail->field, $matches)) {
                    continue;
                }

                $type  = $matches[1];
                $index = (int)$matches[2] - 1;

                if (!isset($existingBreaks[$index])) {

                    $existingBreaks[$index] = $attendance->breakTimes()->create([
                        'break_start' => null,
                        'break_end'   => null,
                    ]);
                }

                if ($detail->new_value !== null && $detail->new_value !== '') {
                    $existingBreaks[$index]->{"break_$type"} =
                        Carbon::parse(
                            $attendance->date->format('Y-m-d') . ' ' . $detail->new_value
                        );
                } else {
                    $existingBreaks[$index]->{"break_$type"} = null;
                }

                $existingBreaks[$index]->save();
            }

            // 再計算
            $attendance->load('breakTimes');

            $totalBreakMinutes = 0;

            foreach ($attendance->breakTimes as $break) {

                if ($break->break_start && $break->break_end) {

                    $minutes = $break->break_start->diffInMinutes($break->break_end, false);

                    // マイナス防止
                    if ($minutes > 0) {
                        $totalBreakMinutes += $minutes;
                    }
                }
            }

            $attendance->total_break_minutes = $totalBreakMinutes;

            if ($attendance->clock_in && $attendance->clock_out) {

                $workMinutes =
                    $attendance->clock_in->diffInMinutes($attendance->clock_out, false)
                    - $totalBreakMinutes;

                // マイナス防止
                $attendance->total_work_minutes = max($workMinutes, 0);
            } else {
                $attendance->total_work_minutes = 0;
            }

            $attendance->save();

            // ステータス更新
            $request->status = 'approved';
            $request->save();
        });

        return redirect()
            ->route('admin_stamp_correction_detail', $attendance_correct_request_id);
    }
}
