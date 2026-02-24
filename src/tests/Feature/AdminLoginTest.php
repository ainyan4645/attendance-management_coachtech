<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected $admin;
    protected function setUp(): void
    {
        parent::setUp();
        // 毎回共通で作成するユーザー(admin)
        $this->user = Admin::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function testEmailIsRequired()
    {
        $this->get('/admin/login')->assertStatus(200);
        $this->withMiddleware();

        // メールアドレス以外のユーザー情報を入力する→ログインの処理を行う
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/login');

        $response = $this->followRedirects($response);
        $response->assertSee('メールアドレスを入力してください');
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function testPasswordIsRequired()
    {
        $this->get('/admin/login')->assertStatus(200);
        $this->withMiddleware();

        // パスワード以外のユーザー情報を入力する→ログインの処理を行う
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertRedirect('/admin/login');

        $response = $this->followRedirects($response);
        $response->assertSee('パスワードを入力してください');
    }

    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function testInvalidCredentialsShowError()
    {
        $this->get('/admin/login')->assertStatus(200);
        $this->withMiddleware();

        // 誤ったメールアドレスのユーザー情報を入力する
        $response = $this->post('/admin/login', [
            'email' => 'notfound@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/admin/login');

        $response = $this->followRedirects($response);
        $response->assertSee('ログイン情報が登録されていません');
    }
}
