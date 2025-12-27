<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceStoreRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        $headerType = 'staff_logged_in';

        // 現在の月
        $currentMonth = $request->month
            ? Carbon::parse($request->month . '-01')
            : Carbon::now()->startOfMonth();

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // まだモデルがないのでダミー（空配列）
        $attendances = [];

        return view('staff.attendance_list', compact(
            'headerType',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'attendances'
        ));
    }

    public function detail()
    {
        $headerType = 'staff_logged_in';
        return view('staff.attendance_detail', compact('headerType'));
    }
}
