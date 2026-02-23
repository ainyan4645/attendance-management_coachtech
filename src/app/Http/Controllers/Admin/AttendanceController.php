<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use App\Models\User;
use App\Http\Requests\AttendanceCorrectionRequest;
use Illuminate\Support\Facades\DB;


class AttendanceController extends Controller
{
    public function attendanceList(Request $request)
    {
        // 表示対象日
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        // その日の勤怠 + ユーザーを取得
        $attendances = Attendance::with('user')
            ->whereDate('date', $date)
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance_list', [
            'date'        => $date,
            'attendances' => $attendances,
        ]);
    }

    public function detail(int $id, Request $request)
    {
        /* hidden の date をセッションに保存 */
        if ($request->has('date')) {
            session(['admin_attendance_detail_date' => $request->date]);
        }

        $date = session('admin_attendance_detail_date');

        if (!$date) {
            abort(404);
        }

        $date = Carbon::parse($date);

        if ($id !== 0) {
            $attendance = Attendance::with(['user','breakTimes'])
                ->findOrFail($id);
            $user = $attendance->user;
        } else {
            $attendance = null;
            $user = User::findOrFail($request->user_id);
        }

        // pending/approved判定（そのユーザー・その日分 最新）
        $pendingRequest = AttendanceRequest::where('user_id', $user->id)
            ->where('target_date', $date)
            ->with('details')
            ->latest()
            ->first();

        $hasPendingRequest = (bool) $pendingRequest && $pendingRequest->status === 'pending';

        // 修正フォーム押下後に戻る元画面
        $returnUrl = $request->input('return_url') ?? url()->previous();

        return view('admin.attendance_detail', [
            'attendance'         => $attendance,
            'user'               => $user,
            'date'               => $date,
            'hasPendingRequest'  => $hasPendingRequest,
            'pendingRequest'     => $pendingRequest,
            'returnUrl'          => $returnUrl,
        ]);
    }

    public function update(AttendanceCorrectionRequest $request, int $id)
    {
        DB::transaction(function () use ($request, $id) {
            // 勤怠取得
            $attendance = Attendance::with('breakTimes')->findOrFail($id);

            // 対象日
            $date = Carbon::parse($request->target_date);

            // 出勤・退勤時刻を対象日と組み合わせて保存
            $attendance->clock_in = $request->clock_in
                ? Carbon::parse($request->target_date . ' ' . $request->clock_in)
                : null;

            $attendance->clock_out = $request->clock_out
                ? Carbon::parse($request->target_date . ' ' . $request->clock_out)
                : null;

            // 備考
            $attendance->remark = $request->remark;

            // 保存（まだ total_work_minutes は未計算）
            $attendance->save();

            /** 既存休憩を全削除 */
            $attendance->breakTimes()->delete();

            /** 休憩を再登録 */
            foreach ($request->break_start ?? [] as $i => $start) {
                $end = $request->break_end[$i] ?? null;

                if (!$start && !$end) {
                    continue;
                }

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start'   => $start
                        ? Carbon::parse($request->target_date . ' ' . $start)
                        : null,
                    'break_end'     => $end
                        ? Carbon::parse($request->target_date . ' ' . $end)
                        : null,
                ]);
            }

            /** 勤務時間・休憩時間を再計算 */
            $attendance->refresh(); // breakTimes を再取得

            $totalBreakMinutes = 0;
            foreach ($attendance->breakTimes as $break) {
                if ($break->break_start && $break->break_end) {
                    $totalBreakMinutes += $break->break_start->diffInMinutes($break->break_end);
                }
            }

            $attendance->total_break_minutes = $totalBreakMinutes;

            if ($attendance->clock_in && $attendance->clock_out) {
                $attendance->total_work_minutes =
                    max($attendance->clock_in->diffInMinutes($attendance->clock_out) - $totalBreakMinutes, 0);
            } else {
                $attendance->total_work_minutes = 0;
            }

            $attendance->save();
        });

        // 元の画面に戻る
        $returnUrl = $request->input('return_url') ?? url()->previous();
        return redirect($returnUrl);
    }

    public function staffList()
    {
        $users = User::all();

        return view('admin.staff_list', compact('users'));
    }

    public function staffAttendance(Request $request, $id)
    {
        // 対象スタッフ取得
        $user = User::findOrFail($id);

        // 表示対象月（YYYY-MM）
        $currentMonth = $request->month
            ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
            : Carbon::now()->startOfMonth();

        // 前月・翌月（リンク用）
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // 指定スタッフの勤怠取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->get()
            ->keyBy(fn ($a) => $a->date->toDateString());

        // 月初〜月末の日付一覧
        $dates = CarbonPeriod::create(
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth()
        );

        return view('admin.attendance_staff', compact(
            'user',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'dates',
            'attendances'
        ));
    }

    public function exportCSV(Request $request, $id)
    {
        $user = User::findOrFail($id);  /* ユーザ確認 */

        $month = Carbon::parse($request->month);    /* 日付型に変換 */

        $start = $month->copy()->startOfMonth();    /* 月の開始日/終了日取得 */
        $end   = $month->copy()->endOfMonth();

        // 該当月の勤怠取得
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        $period = \Carbon\CarbonPeriod::create($start, $end);

        $fileName = $user->name . '_' . $month->format('Ym') . '.csv';

        return response()->streamDownload(function () use ($period, $attendances) {

            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));    /* Excel文字化け対策（UTF-8 BOM） */

            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']); /* ヘッダー */

            foreach ($period as $date) {
                $attendance = $attendances[$date->format('Y-m-d')] ?? null;

                fputcsv($handle, [
                    $date->locale('ja')->isoFormat('MM/DD(ddd)'),
                    optional($attendance?->clock_in)->format('H:i'),
                    optional($attendance?->clock_out)->format('H:i'),
                    $attendance
                        ? $attendance->formatMinutesToTime($attendance->total_break_minutes)
                        : '',
                    $attendance
                        ? $attendance->formatMinutesToTime($attendance->total_work_minutes)
                        : '',
                ]);
            }

            fclose($handle);

        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
