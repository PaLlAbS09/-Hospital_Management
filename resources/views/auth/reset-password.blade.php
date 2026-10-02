@extends('layouts.guest')

@section('title', 'Choose a new password - '.$meta['label'])

@section('content')
    <div class="hms-card overflow-hidden">
        <div class="row g-0">
            <div class="col-lg-5 p-4 p-lg-5 text-white"
                 style="background: linear-gradient(160deg, #101828, #312e81);">
                <div class="hms-role-card__icon bg-white bg-opacity-25 text-white mb-3">
                    <i class="bi {{ $meta['icon'] }}"></i>
                </div>
                <h1 class="h4 mb-2">Choose a new password</h1>
                <p class="small text-white-50 mb-4">
                    Finish resetting your {{ strtolower($meta['label']) }} account.
                </p>

                <ul class="list-unstyled small text-white-50 mb-0">
                    <li class="mb-2"><i class="bi bi-check2 me-2"></i>At least 6 characters</li>
                    <li class="mb-2"><i class="bi bi-key me-2"></i>Stored securely, never emailed</li>
                    <li><i class="bi bi-clock-history me-2"></i>This link works once and then expires</li>
                </ul>
            </div>

            <div class="col-lg-7 p-4 p-lg-5">
                <p class="small text-muted">
                    Resetting the password for <strong class="text-body">{{ $email }}</strong>.
                </p>

                <form method="POST" action="{{ route($role.'.password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ old('email', $email) }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password">New password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   autocomplete="new-password"
                                   required
                                   autofocus>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password_confirmation">Confirm new password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password"
                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   autocomplete="new-password"
                                   required>
                        </div>
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-hms w-100 py-2">
                        <i class="bi bi-check2-circle me-1"></i>Reset password
                    </button>
                </form>

                <hr class="my-4">

                <div class="small text-center">
                    Link expired or already used?
                    <a href="{{ route($role.'.password.request') }}" class="fw-semibold">
                        Request a new reset link
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection