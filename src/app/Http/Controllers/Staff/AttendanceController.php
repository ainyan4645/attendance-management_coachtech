<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index() {
        $userHasClockedOut = false; // サンプル。後でログインユーザーの退勤判定に置き換え
        return view('staff.attendance', compact('userHasClockedOut'));
    }

    public function list()
    {
        return view('staff.attendance_list');
    }

    public function detail($id)
    {
        return view('staff.attendance_detail', compact('id'));
    }
}
