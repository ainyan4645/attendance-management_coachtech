<?php

namespace Database\Factories;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestDetail;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceRequestDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = AttendanceRequestDetail::class;
    public function definition()
    {
        return [
            'attendance_request_id' => AttendanceRequest::factory(),
            'field' => 'remark',
            'old_value' => '',
            'new_value' => $this->faker->text(20),
        ];
    }
}
