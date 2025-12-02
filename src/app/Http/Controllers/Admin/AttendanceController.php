<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function attendanceList()
    {
        return view('admin.attendance_list');
    }

    public function detail($id)
    {
        return view('admin.attendance_detail', compact('id'));
    }

    public function staffList()
    {
        return view('admin.staff_list');
    }

    public function staffAttendance($id)
    {
        return view('admin.attendance_staff', compact('id'));
    }
}
