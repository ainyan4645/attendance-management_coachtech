<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceStatusTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 勤務外の場合、勤怠ステータスが正しく表示される
    public function testShowsOffDutyStatus()
    {
        $user = User::factory()->create();

        // ステータスが勤務外のユーザーにログインする
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('勤務外');
    }

    // 出勤中の場合、勤怠ステータスが正しく表示される
    public function testShowsWorkingStatus()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
        ]);

        // ステータスが出勤中のユーザーにログインする
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');
    }

    // 休憩中の場合、勤怠ステータスが正しく表示される
    public function testShowsBreakStatus()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ]);

        // ステータスが休憩中のユーザーにログインする
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩中');
    }

    // 退勤済の場合、勤怠ステータスが正しく表示される
    public function testShowsFinishedStatus()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'clock_out' => now(),
        ]);

        // ステータスが退勤済のユーザーにログインする
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤済');
    }
}
