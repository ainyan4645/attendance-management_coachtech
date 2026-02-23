# 勤怠管理アプリ(模擬案件)

## 環境構築
### Dockerビルド
1. git clone https://github.com/ainyan4645/attendance-management_coachtech.git
2. cd attendance-management_coachtech
3. docker-compose up -d --build

### Laravel環境構築
1. cd src
2. cp .env.example .env
3. cp .env.test_example .env.testing
4. docker-compose exec php bash
5. composer install
6. php artisan key:generate
7. php artisan key:generate --env=testing
8. php artisan migrate
9. php artisan db:seed
10. php artisan storage:link
11. mailtrapアカウントを作成→ [https://mailtrap.io/](https://mailtrap.io/)<br>(`sign in`をクリックし会員登録、`Email Sandbox`を選択し利用開始する)
12. Email Sandbox の `Start Testing` をクリックし、SMTPタブで表示されているUsername と Password を .env にコピペする
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=あなたのMailtrap_Username
MAIL_PASSWORD=あなたのMailtrap_Password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```


## 使用技術
- php 8.2
- Laravel 8.0
- MySQL 8.0
- nginx 1.24

## ER図

## 開発艦橋(URL)

## 機能確認用ユーザ


## 自動勤怠補正(スケジューラ)について

日付を跨いだ未完了勤怠（退勤未打刻・休憩未終了）を自動補完するため、
Laravel Scheduler を使用しています。

### 対象データ
・当日より前の日付
・退勤時刻が未入力
・休憩終了時刻が未入力

### 処理内容
・退勤時刻を 23:59 に自動補完
・未終了の休憩を 23:59 で補完
・休憩時間を再計算
・労働時間（勤務時間 − 休憩時間）を再計算

### 実行方法
Docker起動時に scheduler サービスで
```php artisan schedule:work```
を常駐実行しています。

毎日 00:05 に attendance:fix-overnight コマンドを実行しています。

Docker起動時に scheduler サービスが自動実行されます。

### 手動実行（動作確認用）
php artisan attendance:fix-overnight
