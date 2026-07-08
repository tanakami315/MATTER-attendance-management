# coachtech 勤怠管理アプリ

## 概要
会員登録することで勤怠管理ができるアプリです。

一般ユーザー（スタッフ）は打刻により勤怠情報を登録し、登録した勤怠情報の修正申請をすることができます。
また、登録した勤怠情報や申請した内容を確認することができます。

管理者は一般ユーザーの情報や勤怠情報、申請内容の確認ができます。また勤怠情報の修正や申請の承認をすることができます。

## 仕様に関して特記
- 管理者は本webアプリ上で新規登録することはできません。
- 管理者は一般ユーザーとしてログインすることはできません。
- 夜勤（日をまたぐ勤務）は想定していません。
- 本webアプリ上から勤怠登録をしていない日の修正や修正の申請をすることはできません。
- 全ユーザーに対して勤怠記録のダミーデータを作成するよう仕様書に記載がありますが、本アプリでは管理者は勤怠登録を行わない仕様としているため、管理者の勤怠データは意図的に作成していません。
- 勤怠情報のテーブルはattendance_recordsではなく、attendancesとして作成しています。
- 例外的に休憩時間について、テーブルはbreaks、モデルはBreakTimeとして作成しています。
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
2. Docker Desktopを立ち上げる
3. `docker-compose up -d --build`

**Laravel環境構築**
1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルをコピーして 「.env」ファイルを作成。
4. .envで以下の環境変数を設定
``` text
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

## 単体テスト
1. rootユーザ（管理者）でログイン
```MySQLコンテナ上
mysql -u root -p
```

2. テスト用データベースの作成
``` MySQLログイン後
CREATE DATABASE test;
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
vendor/bin/phpunit
```

## 使用技術(実行環境)
- PHP 8.1.34
- Laravel 8.83.8
- Laravel Fortify
- Laravel Sanctum
- MySQL 8.0.26
- nginx:1.21.1
- Docker
- Docker Compose
- MailHog

## ER図
![ER図](ER.png)

## ダミーデータ
- ユーザー
  - ユーザー1（一般）: user1@example.com / password / メール認証済み
  - ユーザー2（一般）: user2@example.com / password / メール認証済み
  - ユーザー3（管理者）: user3@example.com / password / メール認証済み

- 勤怠記録
  - ユーザー1
    - 過去 5 ヶ月: 各月平日 15 日 = 75 日 の通常勤務（9:00-18:00）
    - 当月 17 日 :
      - 通常 10 日
      - 残業（9:00-20:00）3 日
      - 遅刻（9:30-18:00）2日
      - 早退（9:00-17:00）1 日
      - 長時間労働（8:00-21:00）1日
    - 休憩時間：全日固定12:00-13:00（1 時間）
  - ユーザー2
    - 過去 5 ヶ月: 各月平日 15 日 = 75 日 の通常勤務（9:00-18:00）（ただし、後述の修正申請のダミーデータを作成することで長時間労働日が1日作成される。）
    - 当月 16 日 :
      - 通常 5 日
      - 残業（9:00-20:00）3 日
      - 遅刻（9:30-18:00）2日
      - 遅刻（13:00-18:00）2日
      - 早退（9:00-17:00）2 日
      - 早退（9:00-12:00）1 日
      - 長時間労働（9:00-21:00）1日
    - 休憩時間
      - 通常は 12:00-13:00（1 時間）
      - 13時以降の出勤日、12時までの出勤日は休憩なし、
      - 長時間労働日（9:00-21:00）は2回目の休憩18:00-18:15（15分間）を追加
  - ユーザー3
    - 管理者は勤怠記録登録をしない想定のためデータ無し

- 勤怠修正申請
  - attendance_id:80
    - ユーザー1
    - 勤務時間：8:00-18:00
    - 休憩時間：12:30-13:30
    - ステータス：承認待ち
  - attendance_id:150
    - ユーザー2
    - 勤務時間：9:00-22:00
    - 休憩時間：12:30-13:30、19:00-19:30
    - ステータス：承認済み
  - attendance_id:177
    - ユーザー2
    - 勤務時間：9:00-18:00
    - 休憩時間：12:30-13:30、19:00-19:30
    - ステータス：承認待ち

## URL
- 開発環境: http://localhost
  - 一般ユーザーログイン画面: http://localhost/login
  - 管理者ログイン画面: http://localhost/admin/login
- Mailhog: http://localhost:8025