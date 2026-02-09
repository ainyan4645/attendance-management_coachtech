<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceStoreRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    public function index()
    {
        $today = now();
        $now = now();

        $attendance = Attendance::with('breakTimes')
            ->where('user_id', auth()->id())
            ->where('date', $today->toDatestring())
            ->first();

        if (!$attendance) {
            $status = 'not_working';
            $isFinished = false;
        } elseif ($attendance->clock_out) {
            $status = 'finished';
            $isFinished = true;
        } elseif ($attendance->breakTimes->whereNull('break_end')->isNotEmpty()) {
            $status = 'on_break';
            $isFinished = false;
        } else {
            $status = 'working';
            $isFinished = false;
        }

        return view('staff.attendance', compact('today', 'now', 'status','isFinished'));
    }

    public function clockIn(AttendanceStoreRequest $request)
    {
        Attendance::create([
            'user_id'  => auth()->id(),
            'date'     => now()->toDateString(),
            'clock_in' => now(),
        ]);

        return redirect()->route('attendance');
    }

    public function breakStart(AttendanceStoreRequest $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', now())
            ->firstOrFail();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start'   => now(),
        ]);

        return redirect()->route('attendance');
    }

    public function breakEnd(AttendanceStoreRequest $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', now())
            ->firstOrFail();

        $breakTime = $attendance->breakTimes()
            ->whereNull('break_end')
            ->latest('break_start')
            ->firstOrFail();

        $breakEnd = now();

        DB::transaction(function () use ($attendance, $breakTime, $breakEnd) {
            $breakTime->update([
                'break_end' => $breakEnd,
            ]);

            $minutes = $breakTime->break_start
                ->diffInMinutes($breakEnd);

            $attendance->increment(
                'total_break_minutes',
                $minutes
            );
        });

        return redirect()->route('attendance');
    }

    public function clockOut(AttendanceStoreRequest $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', now())
            ->firstOrFail();

        $clockOut = now();

        $workMinutes =
            $attendance->clock_in->diffInMinutes($clockOut)
            - $attendance->total_break_minutes;

        $attendance->update([
            'clock_out'          => $clockOut,
            'total_work_minutes' => max($workMinutes, 0),
        ]);

        return redirect()->route('attendance');
    }

    public function list(Request $request)
    {
        // 表示対象の月（YYYY-MM）
        $currentMonth = $request->month
            ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
            : Carbon::now()->startOfMonth();

        // 前月・翌月（リンク用）
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // 勤怠取得（ログインユーザー分のみ）
        $attendances = Attendance::where('user_id', auth()->id())
            ->whereBetween('date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->get()
            ->keyBy(fn ($a) => $a->date->toDateString());

        // 表示用：月初〜月末の日付一覧
        $dates = CarbonPeriod::create(
            $currentMonth->copy()->startOfMonth(),
            $currentMonth->copy()->endOfMonth()
        );

        return view('staff.attendance_list', compact(
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'dates',
            'attendances'
        ));
    }

    public function detail(int $id, Request $request)
    {
        /* hidden の date をセッションに保存 */
        if ($request->has('date')) {
            session(['attendance_detail_date' => $request->date]);
        }

        $date = session('attendance_detail_date');

        if (!$date) {
            // 日付が取得できない場合は不正アクセス扱い
            abort(404);
        }

        $date = Carbon::parse($date);

        /* 勤怠取得（存在しない場合は null） */
        $attendance = $id === 0
            ? null
            : Attendance::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

        /* pending判定 */
        $pendingRequest = AttendanceRequest::where('user_id', auth()->id())
            ->where('target_date', $date)
            ->where('status', 'pending')
            ->with('details')
            ->latest()
            ->first();

        return view('staff.attendance_detail', [
            'attendance' => $attendance,
            'date' => $date,
            'hasPendingRequest' => (bool) $pendingRequest,
            'pendingRequest'    => $pendingRequest,
        ]);
    }
}
