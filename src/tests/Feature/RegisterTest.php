<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 名前が未入力の場合、バリデーションメッセージが表示される
    public function testNameIsRequired()
    {
        // 会員登録ページを開く
        $this->get('/register')->assertStatus(200);

        // ミドルウェア有効化（セッションやCSRF保護を有効にする）
        $this->withMiddleware();

        // 名前を空で登録
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test1@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // バリデーションエラー → リダイレクト
        $response->assertRedirect('/register');

        // 遷移後の画面にエラーメッセージがあるか確認
        $response = $this->followRedirects($response);
        $response->assertSee('お名前を入力してください');
    }

    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function testEmailIsRequired()
    {
        $this->get('/register')->assertStatus(200);

        $this->withMiddleware();

        // メールアドレスを空で登録
        $response = $this->post('/register', [
            'name' => 'テストユーザー1',
            'email' => '',
            'password' => 'password456',
            'password_confirmation' => 'password456',
        ]);

        $response->assertRedirect('/register');

        $response = $this->followRedirects($response);
        $response->assertSee('メールアドレスを入力してください');
    }

    // パスワードが8文字未満の場合、バリデーションメッセージが表示される
    public function testPasswordTooShort()
    {
        $this->get('/register')->assertStatus(200);

        $this->withMiddleware();

        // 7文字のパスワードで登録を試みる
        $response = $this->post('/register', [
            'name' => 'テストユーザー3',
            'email' => 'test3@example.com',
            'password' => 'pass123', // 7文字
            'password_confirmation' => 'pass123',
        ]);

        $response->assertRedirect('/register');

        $response = $this->followRedirects($response);
        $response->assertSee('パスワードは8文字以上で入力してください');
    }

    // パスワードが一致しない場合、バリデーションメッセージが表示される
    public function testPasswordConfirmationMismatch()
    {
        $this->get('/register')->assertStatus(200);

        $this->withMiddleware();

        // 確認用パスワードと異なる入力で登録を試みる
        $response = $this->post('/register', [
            'name' => 'テストユーザー4',
            'email' => 'test4@example.com',
            'password' => 'password012',
            'password_confirmation' => 'password999', // 不一致
        ]);

        $response->assertRedirect('/register');

        $response = $this->followRedirects($response);
        $response->assertSee('パスワードと一致しません');
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function testPasswordIsRequired()
    {
        $this->get('/register')->assertStatus(200);

        $this->withMiddleware();

        // パスワードを空で登録
        $response = $this->post('/register', [
            'name' => 'テストユーザー2',
            'email' => 'test2@example.com',
            'password' => '',
            'password_confirmation' => 'password789',
        ]);

        $response->assertRedirect('/register');

        $response = $this->followRedirects($response);
        $response->assertSee('パスワードを入力してください');
    }

    // フォームに内容が入力されていた場合、データが正常に保存される
    public function testRegistersSuccessfully()
    {
        $this->get('/register')->assertStatus(200);

        $this->withMiddleware();

        // 登録処理を送信
        $response = $this->post('/register', [
            'name' => '登録テストユーザー',
            'email' => 'success@example.com',
            'password' => 'password987',
            'password_confirmation' => 'password987',
        ]);

        // DBにユーザー情報が保存されていることを確認
        $this->assertDatabaseHas('users', [
            'name' => '登録テストユーザー',
            'email' => 'success@example.com',
        ]);
    }
}
