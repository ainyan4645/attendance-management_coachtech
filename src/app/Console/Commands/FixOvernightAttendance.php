<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use Carbon\Carbon;

class FixOvernightAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:fix-overnight';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '日付を跨いだ未完了勤怠を23:59で補正する';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday();

        $attendances = Attendance::whereDate('date', $yesterday)
            ->where(function ($q) {
                $q->whereNull('clock_out')
                ->orWhereHas('breakTimes', function ($q) {
                    $q->whereNull('break_end');
                });
            })
            ->get();

        foreach ($attendances as $attendance) {

            /* 退勤が未入力の場合 → 23:59 */
            if (is_null($attendance->clock_out)) {
                $attendance->clock_out = Carbon::parse(
                    $attendance->date->format('Y-m-d') . ' 23:59'
                );
            }

            /* 休憩が戻っていない場合 → 23:59 */
            foreach ($attendance->breakTimes as $break) {
                if (is_null($break->break_end)) {
                    $break->break_end = Carbon::parse(
                        $attendance->date->format('Y-m-d') . ' 23:59'
                    );
                    $break->save();
                }
            }

            /* 労働時間・休憩時間を再計算（例） */
            $attendance->total_break_minutes =
                $attendance->breakTimes->sum(function ($break) {
                    return $break->break_start && $break->break_end
                        ? $break->break_start->diffInMinutes($break->break_end)
                        : 0;
                });

            $attendance->total_work_minutes =
                $attendance->clock_in
                    ? $attendance->clock_in->diffInMinutes($attendance->clock_out)
                        - $attendance->total_break_minutes
                    : 0;

            $attendance->save();
        }

        $this->info('未完了勤怠を 23:59 で補正しました');

        return 0;
    }
}
