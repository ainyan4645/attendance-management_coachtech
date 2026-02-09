<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_break_minutes',
        'total_work_minutes',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }


    // 労働時間計算
    public function calculateWorkMinutes(Carbon $clockOut): int
    {
        return max(
            $this->clock_in->diffInMinutes($clockOut)
            - $this->total_break_minutes,
            0
        );
    }

    // 分 → H:i 表記（例: 510 → 8:30）
    public function formatMinutesToTime(?int $minutes): ?string
    {
        if ($minutes === null) {
            return null;
        }

        $hours = intdiv($minutes, 60);
        $mins  = $minutes % 60;

        return sprintf('%d:%02d', $hours, $mins);
    }
}
