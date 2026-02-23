<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceCorrectionRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestDetail;
use Carbon\Carbon;

class StampCorrectionController extends Controller
{
    public function update(AttendanceCorrectionRequest $request, int $id)
    {
        $user = auth()->user();

        //勤怠レコードが存在しない場合の修正対象日時取得
        $date = Carbon::parse($request->target_date);

        DB::transaction(function () use ($request, $user, $date, $id) {

            /* 勤怠レコード取得（なければ null） */
            $attendance = $id === 0
                ? null
                : Attendance::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

            /* 修正申請ヘッダ作成 */
            $attendanceRequest = AttendanceRequest::create([
                'attendance_id' => $attendance?->id,
                'target_date'   => $date,
                'user_id'       => $user->id,
                'status'        => 'pending',
                'requested_at'  => now(),
            ]);

            /* 出勤・退勤 */
            $this->storeDetail(
                $attendanceRequest,
                'clock_in',
                optional($attendance?->clock_in)->format('H:i'),
                $request->clock_in
            );

            $this->storeDetail(
                $attendanceRequest,
                'clock_out',
                optional($attendance?->clock_out)->format('H:i'),
                $request->clock_out
            );

            /* 休憩（配列） */
            foreach ($request->break_start ?? [] as $i => $start) {
                $oldStart = optional($attendance?->breakTimes->get($i)?->break_start)
                    ->format('H:i');
                $oldEnd = optional($attendance?->breakTimes->get($i)?->break_end)
                    ->format('H:i');

                $this->storeDetail(
                    $attendanceRequest,
                    "break_start_" . ($i + 1),
                    $oldStart,
                    $start
                );

                $this->storeDetail(
                    $attendanceRequest,
                    "break_end_" . ($i + 1),
                    $oldEnd,
                    $request->break_end[$i] ?? null
                );
            }

            /* 備考 */
            $this->storeDetail(
                $attendanceRequest,
                'remark',
                $attendance?->remark,
                $request->remark
            );
        });

        return redirect()
        ->route('attendance_detail', ['id' => $id]);
    }

    /* 差分がある場合のみ detail を保存　*/
    private function storeDetail(
        AttendanceRequest $request,
        string $field,
        $old,
        $new
    ) {
        if ($old == $new) {
            return;
        }

        AttendanceRequestDetail::create([
            'attendance_request_id' => $request->id,
            'field'     => $field,
            'old_value' => $old,
            'new_value' => $new,
        ]);
    }
}
