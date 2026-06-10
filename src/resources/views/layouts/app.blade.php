@php
use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo">
                <img src="{{ asset('image/COACHTECH.png') }}" alt="COACHTECH">
            </a>
            <nav class="header__nav">
                @if(request()->is(
                    'attendance',
                    'attendance/list',
                    'attendance/detail/*'
                ))
                    <a
                        href="/attendance"
                        class="header__nav-link
                            header__nav-link--common"
                    >
                        勤怠
                    </a>
                    <a
                        href="/attendance/list"
                        class="header__nav-link
                            header__nav-link--common"
                    >
                        勤怠一覧
                    </a>
                    <a
                        href="/stamp_correction_request/list"
                        class="header__nav-link
                            header__nav-link--common"
                    >
                        申請
                    </a>
                    <a
                        href="/my_report"
                        class="header__nav-link
                            header__nav-link--common"
                    >
                        レポート
                    </a>

                @elseif(request()->is(
                    'admin/attendance/list',
                    'admin/attendance/*',
                    'admin/staff/list',
                    'admin/attendance/staff/*',
                    'admin/staff/edit/*',
                    'stamp_correction_request/list',
                    'stamp_correction_request/approve/*'
                ))
                    <a
                        href="/admin/attendance/list"
                        class="header__nav-link
                            header__nav-link--common"
                    >
                        勤怠一覧
                    </a>
                    <a
                        href="/admin/staff/list"
                        class="header__nav-link
                            header__nav-link--common"
                    >
                        スタッフ一覧
                    </a>
                    <a
                        href="/stamp_correction_request/list"
                        class="header__nav-link
                            header__nav-link--common"
                    >
                        申請一覧
                    </a>
                @endif

                @if(!request()->is(
                    'login',
                    'register',
                    'verify/email',
                    'admin/login'
                ))
                    <form action="/logout"
                        method="post"
                    >
                        @csrf
                        <button
                            class="header__nav-link
                                header__nav-link--logout"
                            type="submit"
                        >
                            ログアウト
                        </button>
                    </form>
                @endif
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
        @yield('js')
    </main>

</body>

</html>
