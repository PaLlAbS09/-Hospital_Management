@extends('layouts.app')

@section('title', 'Add new doctor')
@section('subtitle', 'Create a doctor account that can sign in immediately')

@section('content')
    <div class="hms-card mb-3">
        <div class="hms-card__header">
            <h5><i class="bi bi-person-plus me-2"></i>New doctor account</h5>
            <a href="{{ route('admin.doctors.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to doctors
            </a>
        </div>
        <div class="hms-card__body">
            <form method="POST" action="{{ route('admin.doctors.store') }}" class="row g-3">
                @csrf

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="first_name">First name</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}"
                           class="form-control @error('first_name') is-invalid @enderror" required>
                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="last_name">Last name</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}"
                           class="form-control @error('last_name') is-invalid @enderror" required>
                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="specialization">Specialization</label>
                    <input type="text" id="specialization" name="specialization" value="{{ old('specialization') }}"
                           placeholder="e.g. Cardiology"
                           class="form-control @error('specialization') is-invalid @enderror" required>
                    @error('specialization') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="contact">Contact number</label>
                    <input type="text" id="contact" name="contact" value="{{ old('contact') }}"
                           placeholder="Digits only, 7-15 characters"
                           class="form-control @error('contact') is-invalid @enderror" required>
                    @error('contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" for="email">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="password">Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required>
                    <div class="form-text">Minimum 8 characters.</div>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="password_confirmation">Confirm password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control" required>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-hms">
                        <i class="bi bi-check2 me-1"></i>Create doctor
                    </button>
                    <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
