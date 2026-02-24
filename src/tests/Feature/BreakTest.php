<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 休憩ボタンが正しく機能する
    public function testCanStartBreak()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25, 10, 0, 0));

        $user = User::factory()->create();

        // 出勤中状態を作る
        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        // 「休憩入」ボタン表示確認
        $response->assertSee('休憩入');

        // 休憩開始
        $this->actingAs($user)->post('/attendance/break-start');

        $response = $this->actingAs($user)->get('/attendance');
        // 「休憩中」ステータス確認
        $response->assertSee('休憩中');
    }

    // 休憩は一日に何回でもできる
    public function testCanBreakMultipleTimes()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25, 10, 0, 0));

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
        ]);

        // 1回目
        $this->actingAs($user)->post('/attendance/break-start');
        $this->actingAs($user)->post('/attendance/break-end');

        // 再び休憩入ボタンが表示される
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    // 休憩戻ボタンが正しく機能する
    public function testCanEndBreak()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25, 10, 0, 0));

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

        // 「休憩戻」ボタンが表示される
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance/break-end');

        $response = $this->actingAs($user)->get('/attendance');
        // 出勤中ステータスが表示される
        $response->assertSee('出勤中');
    }

    // 休憩戻は一日に何回でもできる
    public function testCanEndBreakMultipleTimes()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25, 10, 0, 0));

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
        ]);

        // 1回目
        $this->actingAs($user)->post('/attendance/break-start');
        $this->actingAs($user)->post('/attendance/break-end');

        // 2回目
        $this->actingAs($user)->post('/attendance/break-start');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

    // 休憩時刻が勤怠一覧画面で確認できる
    public function testBreakTimeIsRecorded()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25, 10, 0, 0));

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
        ]);

        // 休憩開始
        $this->actingAs($user)->post('/attendance/break-start');

        Carbon::setTestNow(Carbon::create(2026, 2, 25, 10, 30, 0));

        // 休憩終了
        $this->actingAs($user)->post('/attendance/break-end');

        $response = $this->actingAs($user)->get('/attendance/list');

        // 日付が表示されている
        $response->assertSee('02/25(水)');
        // 休憩(合計)時刻が正確に記録されている
        $response->assertSee('0:30');
    }
}
