<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class StampCorrectionController extends Controller
{
    public function list()
    {
        // 管理者
        if (Auth::guard('admin')->check()) {
            return app(
                \App\Http\Controllers\Admin\StampCorrectionController::class
            )->list();
        }

        // 一般ユーザー
        if (Auth::guard('web')->check()) {
            return app(
                \App\Http\Controllers\Staff\StampCorrectionController::class
            )->list();
        }

        abort(403);
    }
}
