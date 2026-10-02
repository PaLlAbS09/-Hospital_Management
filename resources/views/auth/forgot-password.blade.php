@extends('layouts.guest')

@section('title', 'Forgot password - '.$meta['label'])

@section('content')
    <div class="hms-card overflow-hidden">
        <div class="row g-0">
            <div class="col-lg-5 p-4 p-lg-5 text-white"
                 style="background: linear-gradient(160deg, #101828, #312e81);">
                <div class="hms-role-card__icon bg-white bg-opacity-25 text-white mb-3">
                    <i class="bi {{ $meta['icon'] }}"></i>
                </div>
                <h1 class="h4 mb-2">Forgot your password?</h1>
                <p class="small text-white-50 mb-4">
                    Reset it for your {{ strtolower($meta['label']) }} account.
                </p>

                <ul class="list-unstyled small text-white-50 mb-0">
                    <li class="mb-2"><i class="bi bi-envelope-check me-2"></i>We email a single-use reset link</li>
                    <li class="mb-2"><i class="bi bi-clock-history me-2"></i>The link expires in 60 minutes</li>
                    <li><i class="bi bi-shield-lock me-2"></i>Your password is never shown to anyone</li>
                </ul>
            </div>

            <div class="col-lg-7 p-4 p-lg-5">
                @if (session('status'))
                    <div class="alert alert-success small" role="alert">
                        <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
                    </div>
                @endif

                <p class="small text-muted">
                    Enter the email address registered with your {{ strtolower($meta['label']) }} account and
                    we will send you a link to choose a new password.
                </p>

                <form method="POST" action="{{ route($role.'.password.email') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="email">Email address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   autocomplete="username"
                                   required
                                   autofocus>
                        </div>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-hms w-100 py-2">
                        <i class="bi bi-send me-1"></i>Send reset link
                    </button>
                </form>

                <hr class="my-4">

                <div class="small d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <a href="{{ route($role.'.login') }}" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Back to {{ strtolower($meta['label']) }} sign in
                    </a>

                    @foreach (['patient', 'clinic', 'doctor', 'admin'] as $other)
                        @continue($other === $role)
                        <a href="{{ route($other.'.password.request') }}" class="text-decoration-none text-muted">
                            {{ ucfirst($other) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection