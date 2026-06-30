# coachtech 勤怠管理アプリ

## 概要
会員登録することで勤怠管理ができるアプリです。

一般ユーザー（スタッフ）は会員登録し自分の勤怠を登録、修正申請、勤怠や申請の確認をすることができます。

管理者は一般ユーザーの情報や勤怠、申請の確認、申請の修正、承認をすることができます。

## 特記
- 管理者は本webアプリ上で新規登録することはできません。
- 管理者は一般ユーザーとしてログインすることはできません。
- 夜勤（日をまたぐ勤務）は想定していません。
- 本webアプリ上から勤怠登録をしていない日に新規に修正申請および修正することはできません。
- 全ユーザーに対して、勤怠記録のダミーデータを作成するよう記載（開発プロセス 環境構築）がありますが、管理者の勤怠登録は想定していないため、意図して作成していません。
- 勤怠情報のテーブルはattendance_recordsではなく、attendancesとして作成しています。
- API実装において、既存のテーブル名・モデル名との整合性を優先したため、一部名称が仕様書と異なります。
  - レスポンス項目名
    - 仕様書：total_time, total_break_time, breaks, applications
    - 実装：work_time, break_time, breakTimes, attendanceCorrectRequests
  - リソースクラス名
    - 仕様書:AttendanceBreakResource, ApplicationResource
    - 実装:BreakTimeResource, AttendanceCorrectRequestResource

## 環境構築
**Dockerビルド**
1. `git clone git@github.com:tanakami315/MATTER-attendance-management.git`
2. DockerDesktopアプリを立ち上げる
3. `docker-compose up -d --build`

**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルをコピーして 「.env」ファイルを作成。
4. .envで以下の環境変数を変更
``` textit 
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

MAIL_FROM_ADDRESS=test@example.com
```

5. アプリケーションキーの作成
``` bash
php artisan key:generate
```

6. キャッシュの削除
```bash
php artisan config:clear
```

7. マイグレーションの実行
``` bash
php artisan migrate
```

8. シーディングの実行
``` bash
php artisan db:seed
```

9. 保存したファイルへのリンク作成
```bash
php artisan storage:link
```

## 単体テスト
1. rootユーザ（管理者）でログイン
```MySQLコンテナ上
mysql -u root -p
```

2. テスト用データベースの作成
``` MySQLログイン後
CREATE DATABASE test_1;
```
3. 「.env」ファイルをコピーして 「.env.testing」ファイルを作成。

4. .env.testingで以下の環境変数を変更

```env.testing
APP_ENV=testing
APP_KEY=

DB_DATABASE=test_1
DB_USERNAME=root
DB_PASSWORD=root
```
※ APP_KEYは空のままにしてください。  
後述のコマンドでテスト用キーを生成します。

5. テスト用アプリケーションキーの作成
```bash
php artisan key:generate --env=testing
```

6. キャッシュの削除
```bash
php artisan config:clear
```

7. テスト用テーブルの作成
```bash
php artisan migrate --env=testing
```

8. テストの実行
```bash
vendor/bin/phpunit tests/Feature/RegisterTest.php
vendor/bin/phpunit tests/Feature/LoginTest.php
vendor/bin/phpunit tests/Feature/LogoutTest.php
vendor/bin/phpunit tests/Feature/ItemTest.php
vendor/bin/phpunit tests/Feature/MylistTest.php
vendor/bin/phpunit tests/Feature/SearchTest.php
vendor/bin/phpunit tests/Feature/DetailTest.php
vendor/bin/phpunit tests/Feature/LikeTest.php
vendor/bin/phpunit tests/Feature/CommentTest.php
vendor/bin/phpunit tests/Feature/PurchaseTest.php
vendor/bin/phpunit tests/Feature/PurchaseMethodTest.php
vendor/bin/phpunit tests/Feature/AddressTest.php
vendor/bin/phpunit tests/Feature/ProfileTest.php
vendor/bin/phpunit tests/Feature/ProfileEditTest.php
vendor/bin/phpunit tests/Feature/SellTest.php
vendor/bin/phpunit tests/Feature/VerifyEmailTest.php
```

## 使用技術(実行環境)
- PHP 8.1.34
- Laravel 8.83.8
- MySQL 8.0.26

## ER図
![ER図](ER.png)

## URL
- 開発環境：http://localhost
- Mailhog：http://localhost:8025