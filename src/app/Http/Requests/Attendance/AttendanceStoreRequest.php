<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Attendance;

class AttendanceStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    protected string $message = '不正な勤怠操作です。';

    public function authorize(): bool
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', now())
            ->first();

        return match ($this->route()->getName()) {

            'attendance.clockIn'
                => $this->allow(
                    !$attendance,
                    '本日はすでに出勤しています。'
                ),

            'attendance.breakStart'
                => $this->allow(
                    $attendance
                    && !$attendance->clock_out
                    && !$attendance->breakTimes()->whereNull('break_end')->exists(),
                    '休憩を開始できません。'
                ),

            'attendance.breakEnd'
                => $this->allow(
                    $attendance
                    && $attendance->breakTimes()->whereNull('break_end')->exists(),
                    '休憩中ではありません。'
                ),

            'attendance.clockOut'
                => $this->allow(
                    $attendance
                    && !$attendance->clock_out
                    && !$attendance->breakTimes()->whereNull('break_end')->exists(),
                    '休憩中は退勤できません。'
                ),

            default => false,
        };
    }

    protected function allow(bool $condition, string $message): bool
    {
        if (!$condition) {
            $this->message = $message;
        }

        return $condition;
    }

    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            $this->message
        );
    }

    public function rules(): array
    {
        return [];
    }
}
