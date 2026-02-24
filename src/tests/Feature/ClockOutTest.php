<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class ClockOutTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 退勤ボタンが正しく機能する
    public function testCanClockOut()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25, 9, 0, 0));

        $user = User::factory()->create();

        // 出勤中
        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        // 退勤ボタンが表示されている
        $response->assertSee('退勤');

        Carbon::setTestNow(Carbon::create(2026, 2, 25, 18, 0, 0));
        // 退勤処理
        $this->actingAs($user)->post('/attendance/clock-out');

        $response = $this->actingAs($user)->get('/attendance');
        // ステータスが退勤済になる
        $response->assertSee('退勤済');
    }

    // 退勤時刻が勤怠一覧画面で確認できる
    public function testClockOutTimeIsRecorded()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25, 9, 0, 0));

        $user = User::factory()->create();

        // 出勤
        $this->actingAs($user)->post('/attendance/clock-in');
        Carbon::setTestNow(Carbon::create(2026, 2, 25, 18, 0, 0));
        // 退勤
        $this->actingAs($user)->post('/attendance/clock-out');

        // 一覧取得
        $response = $this->actingAs($user)->get('/attendance/list');

        // 月表示
        $response->assertSee('2026/02');
        // 日付表示
        $response->assertSee('02/25(水)');

        // (出勤時刻)
        $response->assertSee('09:00');
        // 退勤時刻
        $response->assertSee('18:00');
    }
}
