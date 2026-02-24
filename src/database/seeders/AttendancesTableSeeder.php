<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $baseMonth = now()->subMonth(2); // 先々月
        $date = $baseMonth->copy()->startOfMonth();

        // 1日 全員分
        $users = User::whereIn('email', [
            'taro.y@coachtech.com',
            'issei.m@coachtech.com',
            'keikichi.y@coachtech.com',
            'tomomi.a@coachtech.com',
            'norio.n@coachtech.com',
        ])->get();

        foreach ($users as $user) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
                'clock_in' => $date->copy()->setTime(9, 0),
                'clock_out' => $date->copy()->setTime(18, 0),
                'total_break_minutes' => 60,
                'total_work_minutes' => 480,
            ]);

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => $date->copy()->setTime(12, 0),
                'break_end' => $date->copy()->setTime(13, 0),
            ]);
        }

        // 2日 山田花子の勤怠
        $hanako = User::where('email', 'hanako.y@coachtech.com')->first();

        if ($hanako) {
            $date = $baseMonth->copy()->day(2);

            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $hanako->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                    'total_break_minutes' => 60,
                    'total_work_minutes' => 480,
                ]
            );

            BreakTime::firstOrCreate([
                'attendance_id' => $attendance->id,
                'break_start' => $date->copy()->setTime(12, 0),
                'break_end' => $date->copy()->setTime(13, 0),
            ]);
        }

        // 西 伶奈：（4,7,17,25日以外）
        $daysInMonth = $baseMonth->daysInMonth;

        $excludeDays = [4, 7, 17, 25];
        $reina = User::where('email', 'reina.n@coachtech.com')->first();

        for ($day = 1; $day <= $daysInMonth; $day++) {
            if (in_array($day, $excludeDays)) {
                continue;
            }

            $date = $baseMonth->copy()->day($day);

            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $reina->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                    'total_break_minutes' => 60,
                    'total_work_minutes' => 480,
                ]
            );

            BreakTime::firstOrCreate([
                'attendance_id' => $attendance->id,
                'break_start' => $date->copy()->setTime(12, 0),
                'break_end' => $date->copy()->setTime(13, 0),
            ]);
        }
    }
}
