@extends('layouts.guest')

@section('title', $role === 'clinic' ? 'Clinic registration' : 'Patient registration')

@section('content')
    <div class="hms-card overflow-hidden">
        <div class="row g-0">
            <div class="col-lg-5 p-4 p-lg-5 text-white"
                 style="background: linear-gradient(160deg, #101828, #312e81);">
                <div class="hms-role-card__icon bg-white bg-opacity-25 text-white mb-3">
                    <i class="bi {{ $meta['icon'] }}"></i>
                </div>
                <h1 class="h4 mb-2">
                    {{ $role === 'clinic' ? 'Register your clinic' : 'Create your patient account' }}
                </h1>

                @if ($role === 'clinic')
                    <p class="small text-white-50 mb-4">
                        Submit your clinic details for review. Administrators approve or reject registrations,
                        and only approved clinics can publish schedules.
                    </p>
                    <ul class="list-unstyled small text-white-50 mb-0">
                        <li class="mb-2"><i class="bi bi-1-circle me-2"></i>Register with your clinic details</li>
                        <li class="mb-2"><i class="bi bi-2-circle me-2"></i>Wait for administrative approval</li>
                        <li><i class="bi bi-3-circle me-2"></i>Publish doctor schedules and receive bookings</li>
                    </ul>
                @else
                    <p class="small text-white-50 mb-4">
                        Register once and book appointments at any approved clinic in your area.
                        Prescriptions are stored against your appointment history.
                    </p>
                    <ul class="list-unstyled small text-white-50 mb-0">
                        <li class="mb-2"><i class="bi bi-search me-2"></i>Search clinics by area</li>
                        <li class="mb-2"><i class="bi bi-calendar-plus me-2"></i>Reserve any published time slot</li>
                        <li><i class="bi bi-file-earmark-medical me-2"></i>View and print prescriptions</li>
                    </ul>
                @endif
            </div>

            <div class="col-lg-7 p-4 p-lg-5">
                <form method="POST" action="{{ route($role.'.register.store') }}">
                    @csrf

                    @if ($role === 'clinic')
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="clinic_name">Clinic name</label>
                                <input type="text" class="form-control @error('clinic_name') is-invalid @enderror"
                                       id="clinic_name" name="clinic_name" value="{{ old('clinic_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="area">Area</label>
                                <input type="text" class="form-control @error('area') is-invalid @enderror"
                                       id="area" name="area" value="{{ old('area') }}"
                                       placeholder="e.g. Bardhaman" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="contact_number">Contact number</label>
                                <input type="text" class="form-control @error('contact_number') is-invalid @enderror"
                                       id="contact_number" name="contact_number" value="{{ old('contact_number') }}"
                                       required>
                            </div>
                        </div>
                    @else
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="first_name">First name</label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                       id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="last_name">Last name</label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                       id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="gender">Gender</label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                    <option value="">Select gender</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ $gender }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="contact">Contact number</label>
                                <input type="text" class="form-control @error('contact') is-invalid @enderror"
                                       id="contact" name="contact" value="{{ old('contact') }}" required>
                            </div>
                        </div>
                    @endif

                    <div class="row g-3 mt-0">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="email">Email address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="password">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            <div class="form-text">Minimum 8 characters.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="password_confirmation">Confirm password</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                   name="password_confirmation" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-hms w-100 py-2 mt-4">
                        <i class="bi bi-person-plus me-1"></i>
                        {{ $role === 'clinic' ? 'Submit clinic registration' : 'Create account' }}
                    </button>
                </form>

                <hr class="my-4">

                <div class="small">
                    Already registered?
                    <a href="{{ route($role.'.login') }}" class="fw-semibold">Sign in instead</a>.
                </div>
            </div>
        </div>
    </div>
@endsection
