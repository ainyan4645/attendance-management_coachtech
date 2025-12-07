<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $headerType = 'staff_after_clockout';
        return view('staff.attendance', compact('headerType'));
    }

    public function list()
    {
        $headerType = 'staff_logged_in';
        return view('staff.attendance_list', compact('headerType'));
    }

    public function detail()
    {
        $headerType = 'staff_logged_in';
        return view('staff.attendance_detail', compact('headerType'));
    }
}
