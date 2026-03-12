<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <!-- HUD Core CSS -->
    <link href="{{ asset('hud/css/vendor.min.css') }}" rel="stylesheet">
    <link href="{{ asset('hud/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('hud/plugins/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">
    <style>
        :root {
            --bs-breadcrumb-divider: "/";
        }

        .login,
        .register {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0d1117;
        }

        .login-content,
        .register-content {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem 2rem;
        }

        .login .brand-logo,
        .register .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            margin-bottom: 2rem;
            text-decoration: none;
        }

        .login .brand-img,
        .register .brand-img {
            width: 38px;
            height: 38px;
            background: rgba(var(--bs-theme-rgb), .15);
            border-radius: .5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--bs-theme);
        }

        .login .brand-text,
        .register .brand-text {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--bs-theme);
        }
    </style>
</head>

<body class="pace-top">
    <div id="app" class="app app-full-height app-without-header">
        @yield('content')
    </div>
    <!-- HUD Core JS -->
    <script src="{{ asset('hud/js/vendor.min.js') }}"></script>
    <script src="{{ asset('hud/js/app.min.js') }}"></script>
</body>

</html>