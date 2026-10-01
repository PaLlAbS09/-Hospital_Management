@extends('layouts.guest')

@section('title', $meta['title'])

@section('content')
    <div class="hms-card overflow-hidden">
        <div class="row g-0">
            <div class="col-lg-5 p-4 p-lg-5 text-white"
                 style="background: linear-gradient(160deg, #101828, #312e81);">
                <div class="hms-role-card__icon bg-white bg-opacity-25 text-white mb-3">
                    <i class="bi {{ $meta['icon'] }}"></i>
                </div>
                <h1 class="h4 mb-2">{{ $meta['title'] }}</h1>
                <p class="small text-white-50 mb-4">{{ $meta['subtitle'] }}</p>

                <ul class="list-unstyled small text-white-50 mb-0">
                    <li class="mb-2"><i class="bi bi-shield-lock me-2"></i>Branch specific authentication</li>
                    <li class="mb-2"><i class="bi bi-activity me-2"></i>Role based dashboards</li>
                    <li><i class="bi bi-journal-check me-2"></i>Complete appointment audit trail</li>
                </ul>
            </div>

            <div class="col-lg-7 p-4 p-lg-5">
                @if (! empty($clinicNotice))
                    <div class="alert alert-info small mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        New clinic? Clinics must be approved by an administrator before they can sign in.
                        <a href="{{ route('clinic.register') }}" class="fw-semibold">Register your clinic</a>.
                    </div>
                @endif

                <form method="POST" action="{{ route($role.'.login.store') }}">
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
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   autocomplete="current-password"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="toggle-password"
                                    aria-label="Show or hide password">
                                <i class="bi bi-eye" id="toggle-password-icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-hms w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Sign in as {{ $meta['label'] }}
                    </button>
                </form>

                <hr class="my-4">

                <div class="small">
                    @if ($role === 'patient')
                        New patient?
                        <a href="{{ route('patient.register') }}" class="fw-semibold">Create an account</a>
                    @elseif ($role === 'clinic')
                        New clinic?
                        <a href="{{ route('clinic.register') }}" class="fw-semibold">Submit a registration</a>
                    @endif

                    <div class="mt-2 d-flex flex-wrap gap-2">
                        @foreach (['patient', 'clinic', 'doctor', 'admin'] as $other)
                            @continue($other === $role)
                            <a href="{{ route($other.'.login') }}" class="badge text-bg-light text-decoration-none border">
                                {{ ucfirst($other) }} sign in
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('toggle-password')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggle-password-icon');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
@endpush
