<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header/common.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header-utilities d-flex align-items-center justify-content-between">
                <a class="header__logo" href="/">
                    <img src="{{ asset('images/logo.png') }}" alt="COACHTECH ロゴ" height="40">
                </a>

                @php
                $admin = Auth::guard('admin')->user();
                $user = Auth::guard('web')->user();
                @endphp

                <nav>
                    <ul class="header-nav d-flex align-items-center list-unstyled mb-0">

                        @if ($admin)
                        <li class="header-nav__item">
                            <a class="header-nav__link" href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
                        </li>
                        <li class="header-nav__item">
                            <a class="header-nav__link" href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
                        </li>
                        <li class="header-nav__item">
                            <a class="header-nav__link" href="{{ route('admin.requests.index') }}">申請一覧</a>
                        </li>
                        <form method="POST" action="/admin/signout">
                            @csrf
                            <button type="submit" class="header-nav__link">
                                ログアウト
                            </button>
                        </form>



                        @elseif ($user)
                        <li class="header-nav__item">
                            <a class="header-nav__link" href="{{ route('attendance.index') }}">勤怠</a>
                        </li>
                        <li class="header-nav__item">
                            <a class="header-nav__link" href="{{ route('attendance.list') }}">勤怠一覧</a>
                        </li>
                        <li class="header-nav__item">
                            <a class="header-nav__link" href="{{ route('stamp_correction_request.list') }}">申請</a>
                        </li>
                        <li class="header-nav__item">
                            <form class="form" action="/logout" method="post">
                                @csrf
                                <button class="header-nav__link">ログアウト</button>
                            </form>
                        </li>
                        @endif

                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>