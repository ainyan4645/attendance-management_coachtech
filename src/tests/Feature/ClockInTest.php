<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class ClockInTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 出勤ボタンが正しく機能する
    public function testCanClockIn()
    {
        $user = User::factory()->create();

        // 出勤ボタンがあること確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        // 出勤処理
        $this->actingAs($user)->post('/attendance/clock-in');

        // ステータスが「出勤中」になる
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    // 出勤は一日一回のみできる
    public function testCannotClockInTwice()
    {
        $user = User::factory()->create();

        // 既に退勤済の状態を作る
        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        // 出勤ボタンが表示されない
        $response->assertDontSee('<button class="work-btn">出勤</button>', false);
    }

    // 出勤時刻が勤怠一覧画面で確認できる
    public function testClockInTimeIsRecorded()
    {
        // 時刻固定
        Carbon::setTestNow(Carbon::create(2026, 2, 25, 9, 0, 0));

        $user = User::factory()->create();

        // 出勤処理
        $this->actingAs($user)->post('/attendance/clock-in');

        // DB確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => today(),
        ]);

        // 一覧画面確認
        $response = $this->actingAs($user)->get('/attendance/list');

        // 月が表示されている
        $response->assertSee('2026/02');
        // 日付が表示されている
        $response->assertSee('02/25(水)');
        // 出勤時刻 が表示されている
        $response->assertSee('09:00');
    }
}
