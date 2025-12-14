<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function attendanceList()
    {
        $today = Carbon::today();
        // まだ勤怠が無いので空コレクション
        $attendances = collect();

        return view('admin.attendance_list', compact('today', 'attendances'));
    }

    public function detail()
    {
        return view('admin.attendance_detail');
    }

    public function staffList()
    {
        return view('admin.staff_list');
    }

    public function staffAttendance()
    {
        return view('admin.attendance_staff');
    }
}
