<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StampCorrectionController extends Controller
{
    public function list()
    {
        return view('admin.stamp_correction_list');
    }

    public function approve()
    {
        return view('admin.stamp_correction_approve');
    }
}
