<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class DateTimeDisplayTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 現在の日時情報がUIと同じ形式で出力されている
    public function testDateTimeDisplay()
    {
        // 時刻を固定
        Carbon::setTestNow(
            Carbon::create(2026, 2, 25, 14, 30, 0)
        );

        // ユーザログイン→勤怠打刻画面を開く
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        // 画面に表示されている日時情報を確認する
        $expectedDate = '2026年2月25日(水)';
        $expectedTime = '14:30';

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}
