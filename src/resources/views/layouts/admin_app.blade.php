<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech勤怠管理アプリ（管理者用）</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
                <img src="{{ asset('image/COACHTECH.png') }}" alt="COACHTECH">
            </div>
            <nav class="header__nav">
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
                <form action="/logout" method="post">
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
