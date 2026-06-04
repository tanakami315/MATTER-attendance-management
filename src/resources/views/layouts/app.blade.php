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
    <link rel="icon" href="./image/favicon.ico" />
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo" href="/">
                <img src="{{ asset('image/COACHTECH.png') }}" alt="COACHTECH">
            </a>
            @if(
                !request()->is('login') &&
                !request()->is('register') &&
                !request()->is('email/verify')
            )
                <nav class="header__nav">
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
                </nav>
            @endif
        </div>
    </header>

    <main>
        @yield('content')
        @yield('js')
    </main>

</body>

</html>
