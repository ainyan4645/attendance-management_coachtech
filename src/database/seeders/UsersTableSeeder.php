<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            ['name' => '山田 太郎', 'email' => 'taro.y@coachtech.com'],
            ['name' => '西 伶奈', 'email' => 'reina.n@coachtech.com'],
            ['name' => '増田 一世', 'email' => 'issei.m@coachtech.com'],
            ['name' => '山本 敬吉', 'email' => 'keikichi.y@coachtech.com'],
            ['name' => '秋田 朋美', 'email' => 'tomomi.a@coachtech.com'],
            ['name' => '中西 教夫', 'email' => 'norio.n@coachtech.com'],
            ['name' => '山田 花子', 'email' => 'hanako.y@coachtech.com'], // 申請用
        ];

        foreach ($users as $user) {
            $password = 'password';

            if ($user['email'] === 'reina.n@coachtech.com') {
                $password = 'reina1234'; //　固定パスワード
            }

            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($password),
            ]);
        }
    }
}
