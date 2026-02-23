<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestDetail;
use Carbon\Carbon;

class AttendanceRequestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $baseMonth = now()->subMonth(2);    // 勤怠対象：先々月
        $lastMonth       = now()->subMonth();   // 先月
        $thisMonth       = now();               // 当月

        $requests = [
            [
                'email' => 'reina.n@coachtech.com',
                'date' => $baseMonth->copy()->day(1)->toDateString(),
                'requested_at' => $baseMonth->copy()->day(2)->setTime(10, 0),
                'status' => 'pending',
                'details' => [
                    [
                        'field' => 'clock_in',
                        'old' => '09:00',
                        'new' => '09:30',
                    ],
                    [
                        'field' => 'remark',
                        'old' => null,
                        'new' => '遅延のため',
                    ],
                ],
            ],
            [
                'email' => 'reina.n@coachtech.com',
                'date' => $baseMonth->copy()->day(2)->toDateString(),
                'requested_at' => $baseMonth->copy()->day(4)->setTime(11, 0),
                'status' => 'pending',
                'details' => [
                    [
                        'field' => 'clock_in',
                        'old' => '09:00',
                        'new' => '09:30',
                    ],
                    [
                        'field' => 'remark',
                        'old' => null,
                        'new' => '遅延のため',
                    ],
                ],
            ],
            [
                'email' => 'reina.n@coachtech.com',
                'date' => $baseMonth->copy()->day(3)->toDateString(),
                'requested_at' => $baseMonth->copy()->day(6)->setTime(12, 0),
                'status' => 'approved',
                'approved_at' => $thisMonth->copy()->day(1),
                'details' => [
                    [
                        'field' => 'clock_in',
                        'old' => '09:00',
                        'new' => '09:30',
                    ],
                    [
                        'field' => 'remark',
                        'old' => null,
                        'new' => '遅延のため',
                    ],
                ],
            ],
            [
                'email' => 'reina.n@coachtech.com',
                'date' => $baseMonth->copy()->day(5)->toDateString(),
                'requested_at' => $baseMonth->copy()->day(6)->setTime(12, 0),
                'status' => 'approved',
                'approved_at' => $thisMonth->copy()->day(3),
                'details' => [
                    [
                        'field' => 'clock_in',
                        'old' => '09:00',
                        'new' => '09:30',
                    ],
                    [
                        'field' => 'remark',
                        'old' => null,
                        'new' => '遅延のため',
                    ],
                ],
            ],
            [
                'email' => 'taro.y@coachtech.com',
                'date' => $baseMonth->copy()->day(1)->toDateString(),
                'requested_at' => $thisMonth->copy()->day(2)->setTime(9, 0),
                'status' => 'pending',
                'details' => [
                    [
                        'field' => 'clock_in',
                        'old' => '09:00',
                        'new' => '09:20',
                    ],
                    [
                        'field' => 'remark',
                        'old' => null,
                        'new' => '遅延のため',
                    ],
                ],
            ],
            [
                'email' => 'hanako.y@coachtech.com',
                'date' => $baseMonth->copy()->day(2)->toDateString(),
                'requested_at' => $lastMonth->copy()->day(2)->setTime(14, 0),
                'status' => 'pending',
                'details' => [
                    [
                        'field' => 'clock_in',
                        'old' => '09:00',
                        'new' => '10:00',
                    ],
                    [
                        'field' => 'remark',
                        'old' => null,
                        'new' => '遅延のため',
                    ],
                ],
            ],
        ];

        foreach ($requests as $req) {
            $user = User::where('email', $req['email'])->first();
            if (!$user) {
                continue;
            }

            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $req['date'])
                ->first();

            // 親：attendance_requests
            $attendanceRequest = AttendanceRequest::create([
                'attendance_id' => $attendance?->id,
                'target_date'   => $req['date'],
                'user_id' => $user->id,
                'requested_at' => Carbon::parse($req['requested_at']),
                'status'        => $req['status'] ?? 'pending',
                'approved_at'   => isset($req['approved_at']) ? Carbon::parse($req['approved_at']) : null,
            ]);

            // 子：attendance_request_details
            foreach ($req['details'] as $detail) {
                AttendanceRequestDetail::create([
                    'attendance_request_id' => $attendanceRequest->id,
                    'field' => $detail['field'],
                    'old_value' => $detail['old'],
                    'new_value' => $detail['new'],
                ]);
            }
        }
    }
}
