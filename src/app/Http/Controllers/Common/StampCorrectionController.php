<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRequest;

class StampCorrectionController extends Controller
{
    public function list()
    {
        $isAdmin = Auth::guard('admin')->check();

        // 共通クエリベース
        $baseQuery = AttendanceRequest::with([
                'user',
                'details' => function ($q) {
                    $q->where('field', 'remark');
                }
            ])
            ->orderBy('requested_at', 'desc');

        // staffの場合は自分の申請分のみ
        if (!$isAdmin) {
            $baseQuery->where('user_id', Auth::guard('web')->id());
        }

        // ステータス別取得
        $pendingRequests = (clone $baseQuery)
            ->where('status', 'pending')
            ->get();

        $approvedRequests = (clone $baseQuery)
            ->where('status', 'approved')
            ->get();

        // 表示切替
        if ($isAdmin) {
            return view(
                'admin.stamp_correction_list',
                compact('pendingRequests', 'approvedRequests')
            );
        }

        return view(
            'staff.stamp_correction_list',
            compact('pendingRequests', 'approvedRequests')
        );
    }
}
