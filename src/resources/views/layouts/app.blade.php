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
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo">
                <img src="{{ asset('image/COACHTECH.png') }}" alt="COACHTECH">
            </a>
            <nav class="header__nav">
                @if(session('login_type')==='staff')
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
                @elseif(session('login_type')==='admin')
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
                    <form action="/logout"
                        method="post"
                    >
                        @csrf
                        <input type="hidden" name="login_type" value="admin">
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
