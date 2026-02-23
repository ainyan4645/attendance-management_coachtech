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
        $requests = [
            [
                'email' => 'reina.n@coachtech.com',
                'date' => '2023-06-01',
                'requested_at' => '2023-06-02 10:00:00',
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
                'date' => '2023-06-01',
                'requested_at' => '2023-08-02 09:00:00',
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
                'date' => '2023-06-02',
                'requested_at' => '2023-07-02 14:00:00',
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
                'status' => 'pending',
                'requested_at' => Carbon::parse($req['requested_at']),
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
