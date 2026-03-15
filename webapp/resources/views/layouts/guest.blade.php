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
    <!-- Bootstrap Icons (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        }

        .login-content,
        .register-content {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem 2rem;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
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

<body class="pace-top theme-teal" style="background-image: url({{ asset('hud/img/cover/cover-raiseguard.png') }}); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;">
    <div id="app" class="app app-full-height app-without-header">
        @yield('content')
        
        <!-- BEGIN theme-cover -->
        <div class="app-theme-cover border-0">
            <div class="app-theme-cover-item active">
                <a href="javascript:;" class="app-theme-cover-link" style="background-image: url({{ asset('hud/img/cover/cover-raiseguard.png') }});" data-theme-cover-class="" data-toggle="theme-cover-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Default">&nbsp;</a>
            </div>
        </div>
        <!-- END theme-cover -->
    </div>
    <!-- HUD Core JS -->
    <script src="{{ asset('hud/js/vendor.min.js') }}"></script>
    <script src="{{ asset('hud/js/app.min.js') }}"></script>
</body>

</html>