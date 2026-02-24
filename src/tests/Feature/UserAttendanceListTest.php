<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class UserAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 自分が行った勤怠情報が全て表示されている
    public function testShowsOwnAttendances()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // 自分の勤怠
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-10',
            'clock_in' => '2026-02-10 09:00:00',
            'clock_out' => '2026-02-10 18:00:00',
        ]);

        // 他人の勤怠
        Attendance::create([
            'user_id' => $otherUser->id,
            'date' => '2026-02-15',
            'clock_in' => '2026-02-15 10:00:00',
            'clock_out' => '2026-02-15 19:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        // 自分の打刻は表示される
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        // 他人の打刻は表示されない
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    // 勤怠一覧画面に遷移した際に現在の月が表示される
    public function testShowsCurrentMonth()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 1));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('2026/02');
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function testShowsPreviousMonth()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 1));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('前月');
        $response = $this->actingAs($user)
            ->get(route('attendance_list', ['month' => '2026-01']));

        $response->assertSee('2026/01');
    }


    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function testShowsNextMonth()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 1));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('翌月');
        $response = $this->actingAs($user)
            ->get(route('attendance_list', ['month' => '2026-03']));

        $response->assertSee('2026/03');
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function testCanViewAttendanceDetail()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-10',
            'clock_in' => '2026-02-10 09:00:00',
        ]);

        // 一覧ページ
        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('詳細');

        // 詳細ページへ遷移
        $detailResponse = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}?date=2026-02-10");

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');
        $detailResponse->assertSee('2026年');
        $detailResponse->assertSee('2月10日');
    }
}
