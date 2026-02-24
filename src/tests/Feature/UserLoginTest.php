<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected $user;
    protected function setUp(): void
    {
        parent::setUp();
        // 毎回共通で作成するユーザー
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function testEmailIsRequired()
    {
        $this->get('/login')->assertStatus(200);
        $this->withMiddleware();

        // メールアドレス以外のユーザー情報を入力する→ログインの処理を行う
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');

        $response = $this->followRedirects($response);
        $response->assertSee('メールアドレスを入力してください');
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function testPasswordIsRequired()
    {
        $this->get('/login')->assertStatus(200);
        $this->withMiddleware();

        // パスワード以外のユーザー情報を入力する→ログインの処理を行う
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertRedirect('/login');

        $response = $this->followRedirects($response);
        $response->assertSee('パスワードを入力してください');
    }

    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function testInvalidCredentialsShowError()
    {
        $this->get('/login')->assertStatus(200);
        $this->withMiddleware();

        // 誤ったメールアドレスのユーザー情報を入力する
        $response = $this->post('/login', [
            'email' => 'notfound@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');

        $response = $this->followRedirects($response);
        $response->assertSee('ログイン情報が登録されていません');
    }
}
