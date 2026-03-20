<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <title>@yield('title', config('course.title'))</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MCP Cyber Defense Academy — e-learning platform">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- HUD Core CSS -->
    <link href="{{ asset('hud/css/vendor.min.css') }}" rel="stylesheet">
    <link href="{{ asset('hud/css/app.min.css') }}" rel="stylesheet">

    <!-- Bootstrap Icons (CDN to fix local font 404s) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Highlight.js for code syntax highlighting (CDN to fix 404s) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">

    <!-- Custom course styles -->
    <style>
        /* ── Markdown content rendering ──────────────────────────── */
        .md-content h1,
        .md-content h2,
        .md-content h3,
        .md-content h4,
        .md-content h5,
        .md-content h6 {
            color: var(--bs-heading-color);
            margin-top: 1.5rem;
            margin-bottom: .75rem;
            font-weight: 600;
        }

        .md-content h1 {
            font-size: 1.8rem;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
            padding-bottom: .5rem;
        }

        .md-content h2 {
            font-size: 1.4rem;
        }

        .md-content h3 {
            font-size: 1.15rem;
        }

        .md-content p {
            line-height: 1.75;
            margin-bottom: 1rem;
        }

        .md-content ul,
        .md-content ol {
            margin: .75rem 0 1rem 1.5rem;
        }

        .md-content li {
            margin-bottom: .35rem;
            line-height: 1.7;
        }

        .md-content a {
            color: var(--bs-theme);
        }

        .md-content blockquote {
            border-left: 4px solid var(--bs-theme);
            padding: .5rem 1rem;
            background: rgba(255, 255, 255, .04);
            border-radius: 0 6px 6px 0;
            margin: 1rem 0;
            color: rgba(255, 255, 255, .7);
        }

        .md-content pre {
            background: #0d1117;
            border-radius: 8px;
            padding: 1.25rem;
            overflow-x: auto;
            margin: 1rem 0;
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .md-content code {
            font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace;
            font-size: .85em;
        }

        .md-content p code,
        .md-content li code {
            background: rgba(255, 255, 255, .08);
            border-radius: 4px;
            padding: 2px 6px;
            color: #f59e0b;
        }

        .md-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .md-content th,
        .md-content td {
            padding: .6rem .9rem;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .md-content th {
            background: rgba(255, 255, 255, .06);
            font-weight: 600;
        }

        .md-content img {
            max-width: 100%;
            border-radius: 8px;
        }

        .md-content hr {
            border-color: rgba(255, 255, 255, .1);
            margin: 2rem 0;
        }

        /* ── Breadcrumb divider fix ────────────────────────────── */
        :root {
            --bs-breadcrumb-divider: "/";
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "/";
            opacity: .4;
        }

        /* ── Sidebar lesson navigation ──────────────────────────── */
        .lesson-nav-item {
            padding: .3rem .75rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: .83rem;
        }

        .lesson-nav-item:hover {
            background: rgba(255, 255, 255, .06);
        }

        .lesson-nav-item.active {
            background: rgba(var(--bs-theme-rgb), .15);
            color: var(--bs-theme);
        }

        /* ── Module cards ────────────────────────────────────────── */
        .module-card {
            transition: transform .2s, box-shadow .2s;
            cursor: pointer;
        }

        .module-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, .4);
        }

        .module-badge {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
        }

        /* ── Progress bar ────────────────────────────────────────── */
        .section-tab.active {
            border-bottom: 2px solid var(--bs-theme);
            color: var(--bs-theme) !important;
        }
    </style>

    @stack('head')
</head>

<body>
    <div id="app" class="app">

        <!-- ═══════════════════════ HEADER ═══════════════════════ -->
        <div id="header" class="app-header">

            <!-- Desktop toggler -->
            <div class="desktop-toggler">
                <button type="button" class="menu-toggler" data-toggle-class="app-sidebar-collapsed"
                    data-dismiss-class="app-sidebar-toggled" data-toggle-target=".app">
                    <span class="bar"></span><span class="bar"></span><span class="bar"></span>
                </button>
            </div>

            <!-- Mobile toggler -->
            <div class="mobile-toggler">
                <button type="button" class="menu-toggler" data-toggle-class="app-sidebar-mobile-toggled"
                    data-toggle-target=".app">
                    <span class="bar"></span><span class="bar"></span><span class="bar"></span>
                </button>
            </div>

            <!-- Brand -->
            <div class="brand">
                <a href="{{ route('home') }}" class="brand-logo">
                    <span class="brand-img">
                        <span class="brand-img-text text-theme fw-bold">R</span>
                    </span>
                    <span class="brand-text">RGSOC Academy</span>
                </a>
            </div>

            <!-- Header right menu -->
            <div class="menu">
                <!-- Search toggle -->
                <div class="menu-item dropdown">
                    <a href="#" data-toggle-class="app-header-menu-search-toggled" data-toggle-target=".app"
                        class="menu-link">
                        <div class="menu-icon"><i class="bi bi-search nav-icon"></i></div>
                    </a>
                </div>

                <!-- User info pill -->
                <div class="menu-item d-none d-md-block">
                    @auth
                        <span class="badge bg-theme text-dark fs-11px px-3 py-2 rounded-3">
                            <i class="bi bi-mortarboard me-1"></i>{{ Auth::user()->job_title ?: 'Student' }}
                        </span>
                    @endauth
                </div>

                <!-- User avatar + dropdown -->
                @auth
                    <div class="menu-item dropdown dropdown-mobile-full">
                        <a href="#" data-bs-toggle="dropdown" data-bs-display="static" class="menu-link">
                            @if(Auth::user()->avatar)
                                <div class="menu-img online">
                                    <img src="{{ Auth::user()->avatarUrl() }}" alt="{{ Auth::user()->name }}"
                                        style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                                </div>
                            @else
                                <div class="menu-img online d-flex align-items-center justify-content-center"
                                    style="width:34px;height:34px;background:var(--bs-theme);border-radius:50%;font-weight:700;color:#000;font-size:.9rem;">
                                    {{ Auth::user()->initials() }}
                                </div>
                            @endif
                            <div class="menu-text d-sm-block d-none"
                                style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ Auth::user()->name }}
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end me-lg-3 fs-11px mt-1">
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('profile') }}">
                                PROFILE <i class="bi bi-person-circle ms-auto text-theme fs-16px my-n1"></i>
                            </a>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.edit') }}">
                                SETTINGS <i class="bi bi-gear ms-auto text-theme fs-16px my-n1"></i>
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center w-100">
                                    LOGOUT <i class="bi bi-box-arrow-right ms-auto text-theme fs-16px my-n1"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>

            <!-- Search form -->
            <form class="menu-search" method="GET" action="{{ route('course.index') }}">
                <div class="menu-search-container">
                    <div class="menu-search-icon"><i class="bi bi-search"></i></div>
                    <div class="menu-search-input">
                        <input type="text" name="q" class="form-control form-control-lg"
                            placeholder="Search modules...">
                    </div>
                    <div class="menu-search-icon">
                        <a href="#" data-toggle-class="app-header-menu-search-toggled" data-toggle-target=".app"><i
                                class="bi bi-x-lg"></i></a>
                    </div>
                </div>
            </form>
        </div><!-- /header -->

        <!-- ═══════════════════════ SIDEBAR ═══════════════════════ -->
        <div id="sidebar" class="app-sidebar">
            <div class="app-sidebar-content" data-scrollbar="true" data-height="100%">
                <div class="menu">

                    <div class="menu-header">Course Navigation</div>

                    <!-- Dashboard -->
                    <div class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                        <a href="{{ route('home') }}" class="menu-link">
                            <span class="menu-icon"><i class="bi bi-house"></i></span>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </div>

                    <!-- Course Catalog -->
                    <div class="menu-item {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                        <a href="{{ route('courses.index') }}" class="menu-link">
                            <span class="menu-icon"><i class="bi bi-collection"></i></span>
                            <span class="menu-text">Course Catalog</span>
                        </a>
                    </div>

                    <!-- My Notes -->
                    <div class="menu-item {{ request()->routeIs('notes.*') ? 'active' : '' }}">
                        <a href="{{ route('notes.index') }}" class="menu-link">
                            <span class="menu-icon"><i class="bi bi-journal-text"></i></span>
                            <span class="menu-text">My Notes</span>
                        </a>
                    </div>

                    <!-- Diagrams -->
                    <div class="menu-item {{ request()->routeIs('diagrams.*') ? 'active' : '' }}">
                        <a href="{{ route('diagrams.index') }}" class="menu-link">
                            <span class="menu-icon"><i class="bi bi-bezier2"></i></span>
                            <span class="menu-text">Diagrams</span>
                        </a>
                    </div>

                    <!-- My Achievements -->
                    <div class="menu-item {{ request()->routeIs('modules.completions') ? 'active' : '' }}">
                        <a href="{{ route('modules.completions') }}" class="menu-link">
                            <span class="menu-icon"><i class="bi bi-award"></i></span>
                            <span class="menu-text">My Achievements</span>
                        </a>
                    </div>

                    @auth
                        @php
                            $user = Auth::user();
                            $enrolledSlugs = $user->enrolledCourseSlugs();
                            $courseService = app(\App\Services\CourseService::class);
                            $enrolledCourses = [];
                            foreach ($courseService->getCourses() as $c) {
                                if (in_array('*', $enrolledSlugs) || in_array($c['slug'], $enrolledSlugs)) {
                                    $c['modules'] = $courseService->getModules($c['slug']);
                                    $enrolledCourses[] = $c;
                                }
                            }
                        @endphp
                        
                        @if(count($enrolledCourses) > 0)
                            <div class="menu-header mt-3">My Courses</div>
                            @foreach($enrolledCourses as $enrolled)
                                <div class="menu-item has-sub {{ (isset($course) && $course['slug'] === $enrolled['slug']) || (isset($module) && $module['course_slug'] === $enrolled['slug']) ? 'active bg-white bg-opacity-10' : '' }}">
                                    <a href="#" class="menu-link">
                                        <span class="menu-icon"><i class="bi bi-journal-code"></i></span>
                                        <span class="menu-text">{{ $enrolled['title'] }}</span>
                                        <span class="menu-caret"><b class="caret"></b></span>
                                    </a>
                                    <div class="menu-submenu">
                                        @foreach($enrolled['modules'] as $mod)
                                            <div class="menu-item {{ (isset($module) && $module['slug'] === $mod['slug']) ? 'active' : '' }}">
                                                <a href="{{ route('course.module', $mod['slug']) }}" class="menu-link">
                                                    <span class="menu-text">
                                                        @if($mod['type'] === 'workshop')
                                                            <span class="badge me-1" style="background:#f59e0b;color:#000;font-size:.68rem;font-weight:700;">WS</span>
                                                        @else
                                                            <span class="badge bg-theme text-dark module-badge me-1">{{ sprintf('%02d', $mod['number']) }}</span>
                                                        @endif
                                                        {{ $mod['title'] }}
                                                    </span>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endauth

                    {{-- Admin Panel link (admin-only) --}}
                    @auth
                        @if(Auth::user()->isAdmin())
                            <div class="menu-header mt-3">Administration</div>
                            <div class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <a href="{{ route('admin.dashboard') }}" class="menu-link">
                                    <span class="menu-icon"><i class="bi bi-shield-lock"></i></span>
                                    <span class="menu-text">Admin Panel</span>
                                </a>
                            </div>
                            <div class="menu-item {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.courses.index') }}" class="menu-link">
                                    <span class="menu-icon"><i class="bi bi-book-half"></i></span>
                                    <span class="menu-text">Courses</span>
                                </a>
                            </div>
                            <div class="menu-item {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.classes.index') }}" class="menu-link">
                                    <span class="menu-icon"><i class="bi bi-people"></i></span>
                                    <span class="menu-text">Classes</span>
                                </a>
                            </div>
                            <div class="menu-item {{ request()->routeIs('admin.invitations.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.invitations.index') }}" class="menu-link">
                                    <span class="menu-icon"><i class="bi bi-envelope-plus"></i></span>
                                    <span class="menu-text">Invitations</span>
                                </a>
                            </div>
                        @endif
                    @endauth

                </div><!-- /menu -->
            </div>
        </div><!-- /sidebar -->

        <!-- Mobile sidebar backdrop -->
        <button class="app-sidebar-mobile-backdrop" data-toggle-target=".app"
            data-toggle-class="app-sidebar-mobile-toggled"></button>

        <!-- ══════════════════════ MAIN CONTENT ═══════════════════ -->
        <div id="content" class="app-content">
            @yield('content')
        </div>

        <!-- BEGIN theme-panel -->
        <div class="app-theme-panel">
            <div class="app-theme-panel-container">
                <a href="javascript:;" data-toggle="theme-panel-expand" class="app-theme-toggle-btn"><i class="bi bi-sliders"></i></a>
                <div class="app-theme-panel-content">
                    <div class="small fw-bold text-inverse mb-1">Display Mode</div>
                    <div class="card mb-3">
                        <div class="card-body p-2">
                            <div class="row gx-2">
                                <div class="col-6">
                                    <a href="javascript:;" data-toggle="theme-mode-selector" data-theme-mode="dark" class="app-theme-mode-link active">
                                        <div class="img"><img src="{{ asset('hud/img/mode/dark.jpg') }}" class="object-fit-cover" height="76" width="76" alt="Dark Mode"></div>
                                        <div class="text">Dark</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:;" data-toggle="theme-mode-selector" data-theme-mode="light" class="app-theme-mode-link">
                                        <div class="img"><img src="{{ asset('hud/img/mode/light.jpg') }}" class="object-fit-cover" height="76" width="76" alt="Light Mode"></div>
                                        <div class="text">Light</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    </div>

                    <div class="small fw-bold text-inverse mb-1">Direction Mode</div>
                    <div class="card mb-3">
                        <div class="card-body p-2">
                            <div class="row gx-2">
                                <div class="col-6">
                                    <a href="#" class="btn active btn-sm btn-outline-light d-flex align-items-center justify-content-center gap-2 w-100 rounded-0 fw-bold fs-12px" data-toggle="theme-direction-selector" data-theme-direction="ltr">
                                        <i class="bi bi-text-left fs-16px my-n1 ms-n2"></i> LTR
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#" class="btn btn-sm btn-outline-light d-flex align-items-center justify-content-center gap-2 w-100 rounded-0 fw-bold fs-12px" data-toggle="theme-direction-selector" data-theme-direction="rtl">
                                        <i class="bi bi-text-right fs-16px my-n1 ms-n2"></i> RTL
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    </div>

                    <div class="small fw-bold text-inverse mb-1">Theme Color</div>
                    <div class="card mb-3">
                        <div class="card-body p-2">
                            <div class="app-theme-list">
                                <div class="app-theme-list-item"><a href="javascript:;" class="app-theme-list-link bg-pink" data-theme-class="theme-pink" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Pink">&nbsp;</a></div>
                                <div class="app-theme-list-item"><a href="javascript:;" class="app-theme-list-link bg-red" data-theme-class="theme-red" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Red">&nbsp;</a></div>
                                <div class="app-theme-list-item"><a href="javascript:;" class="app-theme-list-link bg-warning" data-theme-class="theme-warning" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Orange">&nbsp;</a></div>
                                <div class="app-theme-list-item"><a href="javascript:;" class="app-theme-list-link bg-yellow" data-theme-class="theme-yellow" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Yellow">&nbsp;</a></div>
                                <div class="app-theme-list-item"><a href="javascript:;" class="app-theme-list-link bg-lime" data-theme-class="theme-lime" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Lime">&nbsp;</a></div>
                                <div class="app-theme-list-item"><a href="javascript:;" class="app-theme-list-link bg-green" data-theme-class="theme-green" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Green">&nbsp;</a></div>
                                <div class="app-theme-list-item active"><a href="javascript:;" class="app-theme-list-link bg-teal" data-theme-class="" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Default">&nbsp;</a></div>
                                <div class="app-theme-list-item"><a href="javascript:;" class="app-theme-list-link bg-info" data-theme-class="theme-info" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Cyan">&nbsp;</a></div>
                                <div class="app-theme-list-item"><a href="javascript:;" class="app-theme-list-link bg-primary" data-theme-class="theme-primary" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Blue">&nbsp;</a></div>
                                <div class="app-theme-list-item"><a href="javascript:;" class="app-theme-list-link bg-purple" data-theme-class="theme-purple" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Purple">&nbsp;</a></div>
                                <div class="app-theme-list-item"><a href="javascript:;" class="app-theme-list-link bg-indigo" data-theme-class="theme-indigo" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Indigo">&nbsp;</a></div>
                                <div class="app-theme-list-item"><a href="javascript:;" class="app-theme-list-link bg-gray-100" data-theme-class="theme-gray-200" data-toggle="theme-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Gray">&nbsp;</a></div>
                            </div>
                        </div>
                        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    </div>

                    <div class="small fw-bold text-inverse mb-1">Theme Cover</div>
                    <div class="card">
                        <div class="card-body p-2">
                            <div class="app-theme-cover">
                                <div class="app-theme-cover-item active">
                                    <a href="javascript:;" class="app-theme-cover-link" style="background-image: url({{ asset('hud/img/cover/cover-raiseguard.png') }});" data-theme-cover-class="" data-toggle="theme-cover-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="RaiseGuard Default">&nbsp;</a>
                                </div>
                                <div class="app-theme-cover-item">
                                    <a href="javascript:;" class="app-theme-cover-link" style="background-image: url({{ asset('hud/img/cover/cover-thumb-2.jpg') }});" data-theme-cover-class="bg-cover-2" data-toggle="theme-cover-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Cover 2">&nbsp;</a>
                                </div>
                                <div class="app-theme-cover-item">
                                    <a href="javascript:;" class="app-theme-cover-link" style="background-image: url({{ asset('hud/img/cover/cover-thumb-3.jpg') }});" data-theme-cover-class="bg-cover-3" data-toggle="theme-cover-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Cover 3">&nbsp;</a>
                                </div>
                                <div class="app-theme-cover-item">
                                    <a href="javascript:;" class="app-theme-cover-link" style="background-image: url({{ asset('hud/img/cover/cover-thumb-4.jpg') }});" data-theme-cover-class="bg-cover-4" data-toggle="theme-cover-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Cover 4">&nbsp;</a>
                                </div>
                                <div class="app-theme-cover-item">
                                    <a href="javascript:;" class="app-theme-cover-link" style="background-image: url({{ asset('hud/img/cover/cover-thumb-5.jpg') }});" data-theme-cover-class="bg-cover-5" data-toggle="theme-cover-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Cover 5">&nbsp;</a>
                                </div>
                                <div class="app-theme-cover-item">
                                    <a href="javascript:;" class="app-theme-cover-link" style="background-image: url({{ asset('hud/img/cover/cover-thumb-6.jpg') }});" data-theme-cover-class="bg-cover-6" data-toggle="theme-cover-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Cover 6">&nbsp;</a>
                                </div>
                                <div class="app-theme-cover-item">
                                    <a href="javascript:;" class="app-theme-cover-link" style="background-image: url({{ asset('hud/img/cover/cover-thumb-7.jpg') }});" data-theme-cover-class="bg-cover-7" data-toggle="theme-cover-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Cover 7">&nbsp;</a>
                                </div>
                                <div class="app-theme-cover-item">
                                    <a href="javascript:;" class="app-theme-cover-link" style="background-image: url({{ asset('hud/img/cover/cover-thumb-8.jpg') }});" data-theme-cover-class="bg-cover-8" data-toggle="theme-cover-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Cover 8">&nbsp;</a>
                                </div>
                                <div class="app-theme-cover-item">
                                    <a href="javascript:;" class="app-theme-cover-link" style="background-image: url({{ asset('hud/img/cover/cover-thumb-9.jpg') }});" data-theme-cover-class="bg-cover-9" data-toggle="theme-cover-selector" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-container="body" data-bs-title="Cover 9">&nbsp;</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END theme-panel -->

        <!-- Scroll to top -->
        <a href="#" data-toggle="scroll-to-top" class="btn-scroll-top fade"><i class="bi bi-arrow-up"></i></a>

    </div><!-- /#app -->

    <!-- js-cookie (needed by HUD theme panel for persistence) -->
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@3/dist/js.cookie.min.js"></script>

    <!-- HUD Core JS -->
    <script src="{{ asset('hud/js/vendor.min.js') }}"></script>
    <script src="{{ asset('hud/js/app.min.js') }}"></script>

    <!-- Highlight.js -->
    <script src="{{ asset('hud/plugins/@highlightjs/cdn-assets/highlight.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Syntax highlight all code blocks
            document.querySelectorAll('pre code').forEach(function (block) {
                hljs.highlightElement(block);
            });
        });
    </script>

    @if($cinematicAnimationsEnabled ?? true)
        <!-- GSAP for Cinematic Animations -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    @endif

    @stack('scripts')
</body>

</html>