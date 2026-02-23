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
        $attendances = Attendance::where('date', '<', today())
            ->where(function ($q) {
                $q->whereNull('clock_out')
                ->orWhereHas('breakTimes', function ($q) {
                    $q->whereNull('break_end');
                });
            })
            ->with('breakTimes')
            ->get();

        foreach ($attendances as $attendance) {

            $endOfDay = $attendance->date->copy()->endOfDay();

            /* 退勤補完 */
            if (is_null($attendance->clock_out)) {
                $attendance->clock_out = $endOfDay;
            }

            /* 休憩補完 */
            foreach ($attendance->breakTimes as $break) {
                if (is_null($break->break_end)) {
                    $break->break_end = $endOfDay;
                    $break->save();
                }
            }

            /* 休憩再計算 */
            $attendance->total_break_minutes =
                $attendance->breakTimes->sum(function ($break) {
                    return $break->break_start && $break->break_end
                        ? $break->break_start->diffInMinutes($break->break_end)
                        : 0;
                });

            /* 労働時間再計算 */
            $attendance->total_work_minutes =
                $attendance->clock_in
                    ? max(
                        $attendance->clock_in->diffInMinutes($attendance->clock_out)
                        - $attendance->total_break_minutes,
                        0
                    )
                    : 0;

            $attendance->save();
        }

        $this->info('未完了勤怠を補完しました');

        return Command::SUCCESS;
    }
}
