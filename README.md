# 勤怠管理アプリ(模擬案件)

## 環境構築
### Dockerビルド
1. git clone https://github.com/ainyan4645/attendance-management_coachtech.git
2. cd attendance-management_coachtech
3. docker-compose up -d --build

### Laravel環境構築
1. cd src
2. cp .env.example .env
3. cp .env.example .env.testing
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

13. php artisan config:clear

 ※permissionエラーが出る場合は `/flea-market_coachtech` ディレクトリで以下のコマンドを実行してください。
 ```bash
 sudo chmod -R 777 src/*
 ```


## 使用技術
- php 8.2
- Laravel 8.0
- MySQL 8.0
- nginx 1.24

## ER図
![ER図](./attendance-management_coachtech_ER.drawio.svg)

## 開発環境(URL)
- 管理者ログイン画面： http://localhost/admin/login
- 一般ユーザーログイン画面： http://localhost/login
- phpMyAdmin： http://localhost:8080/

## 機能確認用アカウント
- 管理者<br>
メールアドレス： admin@example.com<br>
パスワード： password123

- 一般ユーザー<br>
メールアドレス： reina.n@coachtech.com<br>
パスワード： reina1234<br>
※figmaUIにある西伶奈さんの勤怠を使用しています。

## ダミーデータについて
勤怠記録情報については、figmaの情報を元に作成し、<br>
2023年6月の情報はダミーデータ生成した月の先々月に入れています。<br>
(ダミーの申請日時が6,7,8月であったため)<br>
管理者の勤怠一覧画面からは、カレンダーアイコンをクリックすると表示月日を調整できます。

## PHPUnitを利用したテストに関して
.env.testingファイルの一部項目を以下のように変更
```
APP_ENV=test

DB_DATABASE=test_database
DB_USERNAME=root
DB_PASSWORD=root
```
以下のコマンドを入力:
```
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database test_database;
exit;
exit

docker-compose exec php bash
php artisan config:clear
php artisan migrate:fresh --env=testing
./vendor/bin/phpunit
```
以下のコマンドでテスト実行ができます。<br>
```
php artisan test
```

## 自動勤怠補正(スケジューラ)について
日付を跨いだ未完了勤怠（退勤未打刻・休憩未終了）を自動補完するため、
Laravel Scheduler を使用しています。

### 対象データ
- 当日より前の日付
- 退勤時刻が未入力
- 休憩終了時刻が未入力

### 処理内容
- 退勤時刻を 23:59 に自動補完
- 未終了の休憩を 23:59 で補完
- 休憩時間を再計算
- 労働時間（勤務時間 − 休憩時間）を再計算

### 実行方法
Docker起動時に scheduler サービスで
```php artisan schedule:work```
を常駐実行しています。<br>
毎日 00:05 に attendance:fix-overnight コマンドを実行しています。<br>
Docker起動時に scheduler サービスが自動実行されます。

### 手動実行（動作確認用）
php artisan attendance:fix-overnight
