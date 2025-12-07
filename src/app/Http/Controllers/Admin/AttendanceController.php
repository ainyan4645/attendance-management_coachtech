<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function attendanceList()
    {
        $headerType = 'admin_logged_in';
        return view('admin.attendance_list', compact('headerType'));
    }

    public function detail()
    {
        $headerType = 'admin_logged_in';
        return view('admin.attendance_detail', compact('headerType'));
    }

    public function staffList()
    {
        $headerType = 'admin_logged_in';
        return view('admin.staff_list', compact('headerType'));
    }

    public function staffAttendance()
    {
        $headerType = 'admin_logged_in';
        return view('admin.attendance_staff', compact('headerType'));
    }
}
