<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign In') &middot; {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @include('partials.theme')
    @include('partials.theme-components')
    @stack('styles')
</head>
<body>
<header class="bg-white border-bottom">
    <div class="container py-3 d-flex align-items-center justify-content-between">
        <a href="{{ route('home') }}" class="hms-brand">
            <span class="hms-brand__mark"><i class="bi bi-heart-pulse-fill"></i></span>
            <span class="hms-brand__text">
                {{ config('app.name') }}
                <small>Healthcare Portal</small>
            </span>
        </a>
        <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to website
        </a>
    </div>
</header>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8 col-xl-7">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>
    </div>
</main>

<footer class="border-top bg-white py-3">
    <div class="container text-center text-muted small">
        &copy; {{ date('Y') }} {{ config('app.name') }} &middot; Secure role based access
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
