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
    <script src="https://kit.fontawesome.com/42694f25bf.js" crossorigin="anonymous"></script>
    <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo">
                <img src="{{ asset('image/COACHTECH.png') }}" alt="COACHTECH">
            </a>
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
        </div>
    </header>
    
    <main>
        @yield('content')
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

        <script>
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-bottom-right",
            }

            @if(Session::has('flashSuccess'))
            toastr.success("{{ session('flashSuccess') }}");
            @endif
        </script>
            @yield('js')
    </main>

</body>

</html>
