<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $headerType = 'staff_after_clockout';
        return view('staff.attendance', compact('headerType'), [
            'today' => now(),
            'now'   => now(),
        ]);
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
