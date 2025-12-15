<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StampCorrectionController extends Controller
{
    public function list()
    {
        $headerType = 'staff_logged_in';
        return view('staff.stamp_correction_list', compact('headerType'));
    }
}
