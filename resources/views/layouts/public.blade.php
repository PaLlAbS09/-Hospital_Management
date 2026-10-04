<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Home') &middot; {{ config('app.name') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @include('partials.theme')
    @include('partials.theme-components')
    @include('partials.theme-public')
    @stack('styles')
</head>
<body>
@php
    $signedInGuard = \App\Support\Navigation::activeGuard();
@endphp

<nav class="hms-public-nav">
    <div class="container d-flex align-items-center gap-3 py-3">
        <a href="{{ route('home') }}" class="hms-brand me-auto">
            <x-brand-logo />
            <span class="hms-brand__text">
                {{ config('app.name') }}
                <small>Clinics &middot; Doctors &middot; Patients</small>
            </span>
        </a>

        <ul class="nav d-none d-lg-flex align-items-center gap-2">
            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('clinics.index') }}">Clinic directory</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#contact">Contact us</a></li>
        </ul>

        @if ($signedInGuard)
            <a href="{{ route($signedInGuard.'.dashboard') }}" class="btn btn-sm btn-hms">
                <i class="bi bi-speedometer2 me-1"></i>Go to dashboard
            </a>
        @else
            <div class="dropdown">
                <button class="btn btn-sm btn-hms dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Portal access
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="{{ route('patient.login') }}"><i class="bi bi-person-heart me-2"></i>Patient sign in</a></li>
                    <li><a class="dropdown-item" href="{{ route('patient.register') }}"><i class="bi bi-person-plus me-2"></i>Patient registration</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('clinic.login') }}"><i class="bi bi-hospital me-2"></i>Clinic sign in</a></li>
                    <li><a class="dropdown-item" href="{{ route('doctor.login') }}"><i class="bi bi-person-badge me-2"></i>Doctor sign in</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('admin.login') }}"><i class="bi bi-shield-lock me-2"></i>Administrator sign in</a></li>
                </ul>
            </div>
        @endif
    </div>
</nav>

<main class="pb-5">
    @yield('content')
</main>

<footer class="border-top bg-white py-4 mt-4">
    <div class="container">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="fw-bold">{{ config('app.name') }}</div>
                <div class="text-muted small">
                    Connecting patients with approved clinics, doctors and their prescriptions.
                </div>
            </div>
            <div class="col-md-6 text-md-end text-muted small">
                &copy; {{ date('Y') }} {{ config('app.name') }} &middot; All rights reserved.
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
@stack('scripts')
</body>
</html>
