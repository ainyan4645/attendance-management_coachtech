<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class UserAttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function testShowsUserName()
    {
        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-02-10',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance_detail', [
                'id' => $attendance->id,
                'date' => '2026-02-10',
            ]));

        $response->assertStatus(200);
        $response->assertSee('山田 太郎');
    }

    // 勤怠詳細画面の「日付」が選択した日付になっている
    public function testShowsSelectedDate()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-02-10',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance_detail', [
                'id' => $attendance->id,
                'date' => '2026-02-10',
            ]));

        $response->assertSee('2026年');
        $response->assertSee('2月10日');
    }

    // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function testShowsClockInOutTime()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-02-10',
            'clock_in' => '2026-02-10 09:00:00',
            'clock_out' => '2026-02-10 18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance_detail', [
                'id' => $attendance->id,
                'date' => '2026-02-10',
            ]));

        $response->assertSee('出勤・退勤');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function testShowsBreakTime()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-02-10',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-02-10 12:00:00',
            'break_end'   => '2026-02-10 13:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance_detail', [
                'id' => $attendance->id,
                'date' => '2026-02-10',
            ]));

        $response->assertSee('休憩');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
