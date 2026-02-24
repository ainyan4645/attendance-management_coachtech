<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;

class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = BreakTime::class;
    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => now()->setTime(12, 0),
            'break_end'   => now()->setTime(13, 0),
        ];
    }
}
