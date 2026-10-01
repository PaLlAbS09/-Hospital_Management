<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &middot; {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @include('partials.theme')
    @include('partials.theme-components')
    @stack('styles')
</head>
<body>
@php
    $guard = \App\Support\Navigation::activeGuard();
    $currentUser = \App\Support\Navigation::user($guard);
    $logoutRoute = ($guard ?? 'patient').'.logout';
@endphp

<div class="hms-shell" id="hms-shell">
    @include('partials.sidebar', ['guard' => $guard, 'currentUser' => $currentUser])
    <div class="hms-sidebar__backdrop" id="hms-sidebar-backdrop"></div>

    <div class="hms-main">
        <header class="hms-topbar">
            <button class="hms-burger" type="button" id="hms-burger" aria-label="Open navigation" aria-controls="hms-shell">
                <i class="bi bi-list"></i>
            </button>
            <div class="me-auto">
                <h1 class="hms-topbar__title">
                    @yield('title', 'Dashboard')
                    <small>@yield('subtitle', 'Hospital Management System')</small>
                </h1>
            </div>

            <div class="d-none d-lg-flex align-items-center gap-2">
                @foreach (\App\Support\Navigation::actions($guard ?? '') as $action)
                    <a href="{{ route($action['route']) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi {{ $action['icon'] }} me-1"></i>{{ $action['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="dropdown">
                <button class="btn btn-light border d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="hms-avatar">{{ $currentUser?->initials ?? 'HM' }}</span>
                    <span class="d-none d-sm-block text-start lh-sm">
                        <span class="d-block fw-semibold small">{{ $currentUser?->display_name ?? 'Signed in' }}</span>
                        <span class="d-block text-muted" style="font-size:.72rem">{{ \App\Support\Navigation::label($guard ?? '') }}</span>
                    </span>
                    <i class="bi bi-chevron-down small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <a class="dropdown-item" href="{{ route('home') }}">
                            <i class="bi bi-globe2 me-2"></i>Public website
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route($logoutRoute) }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Sign out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="hms-content">
            @include('partials.flash')
            @yield('content')
        </main>

        <footer class="hms-footer d-flex flex-wrap justify-content-between gap-2">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }} &middot; All rights reserved.</span>
            <span>
                {{ \App\Support\Navigation::label($guard ?? '') }} session &middot; {{ now()->format('d M Y, H:i') }}
            </span>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const shell = document.getElementById('hms-shell');
        const burger = document.getElementById('hms-burger');
        const backdrop = document.getElementById('hms-sidebar-backdrop');
        const closeBtn = document.getElementById('hms-sidebar-close');

        if (!shell || !burger) return;

        const close = () => shell.classList.remove('nav-open');

        burger.addEventListener('click', () => shell.classList.toggle('nav-open'));
        backdrop?.addEventListener('click', close);
        closeBtn?.addEventListener('click', close);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });
    })();
</script>
@stack('scripts')
</body>
</html>
