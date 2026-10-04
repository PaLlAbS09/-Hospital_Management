@extends('layouts.public')

@section('title', 'Trusted clinic appointments')

@section('content')

    {{-- New doctor promotions + clinic discount offers (single slider) ------ --}}
    <div class="container mt-4">
        @include('partials.promo_slider')
    </div>

    {{-- Clinic directory ---------------------------------------------------- --}}
    <div class="container mt-5">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <h2 class="h4 mb-1">
                    @if ($area)
                        Clinics in &ldquo;{{ $area }}&rdquo;
                    @else
                        Clinic directory
                    @endif
                </h2>
                <p class="text-muted mb-0 small">
                    Only clinics approved by an administrator can accept appointments.
                </p>
            </div>
            <a href="{{ route('clinics.index', array_filter(['area' => $area])) }}" class="btn btn-sm btn-outline-hms">
                Browse all clinics <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-3">
            @forelse ($clinics as $clinic)
                <div class="col-md-6 col-xl-4">
                    <div class="hms-card h-100">
                        <div class="hms-card__body d-flex flex-column h-100">
                            <div class="d-flex align-items-start gap-3 mb-2">
                                <span class="hms-avatar"><i class="bi bi-hospital"></i></span>
                                <div class="flex-grow-1">
                                    <h3 class="h6 mb-1">{{ $clinic->clinic_name }}</h3>
                                    <div class="text-muted small">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $clinic->area }}
                                    </div>
                                </div>
                                <x-status-badge :status="$clinic->status" />
                            </div>

                            <div class="small text-muted mb-3">
                                <div><i class="bi bi-telephone me-1"></i>{{ $clinic->contact_number }}</div>
                                <div class="text-truncate"><i class="bi bi-envelope me-1"></i>{{ $clinic->email }}</div>
                            </div>

                            <div class="mt-auto d-flex align-items-center justify-content-between gap-2">
                                <span class="hms-chip">
                                    <i class="bi bi-calendar3"></i>{{ $clinic->schedules_count }} published
                                </span>
                                <a href="{{ route('patient.clinics.doctors', $clinic) }}" class="btn btn-sm btn-hms">
                                    View doctors
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="hms-card">
                        <div class="hms-empty">
                            <i class="bi bi-search"></i>
                            <p class="mb-1 fw-semibold">No approved clinics found{{ $area ? ' in '.$area : '' }}.</p>
                            <p class="mb-0 small">Try a different area or browse the full directory.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- How it works -------------------------------------------------------- --}}
    <div class="container mt-5">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="hms-feature">
                    <i class="bi bi-geo-alt"></i>
                    <div>
                        <h6>1. Search by area</h6>
                        <p>Filter our directory of approved clinics by the area you live in.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="hms-feature">
                    <i class="bi bi-person-badge"></i>
                    <div>
                        <h6>2. Pick doctor &amp; slot</h6>
                        <p>See the doctors on duty and the exact slots still available that day.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="hms-feature">
                    <i class="bi bi-file-medical"></i>
                    <div>
                        <h6>3. Collect your prescription</h6>
                        <p>After the visit your prescription can be viewed and printed online.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hero (premium redesign, same palette as before) ------------------ --}}
    @include('partials.home_hero')

    {{-- Best doctors chosen by the public ------------------------------- --}}
    @include('partials.best_doctors')

    {{-- Contact us ---------------------------------------------------------- --}}
    <div class="container mt-5" id="contact">
        <div class="hms-card">
            <div class="row g-0">
                <div class="col-lg-5 p-4 p-lg-5" style="background: linear-gradient(160deg, #101828, #312e81); border-radius: 16px 0 0 16px;">
                    <h2 class="h4 text-white mb-2">Contact us</h2>
                    <p class="text-white-50 small mb-4">
                        Send us a message and the hospital administration team will get back to you.
                        Every submission is stored in the system queries register.
                    </p>
                    <ul class="list-unstyled small text-white-50 mb-0">
                        <li class="mb-2"><i class="bi bi-clock-history me-2"></i>Responses within 1 working day</li>
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>Area-wise clinic network</li>
                        <li><i class="bi bi-shield-check me-2"></i>Your details are only used to answer your query</li>
                    </ul>
                </div>

                <div class="col-lg-7 p-4 p-lg-5">
                    <form method="POST" action="{{ route('contact.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="user_name">Your name</label>
                                <input type="text"
                                       class="form-control @error('user_name') is-invalid @enderror"
                                       id="user_name"
                                       name="user_name"
                                       value="{{ old('user_name') }}"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="contact_number">Contact number</label>
                                <input type="text"
                                       class="form-control @error('contact_number') is-invalid @enderror"
                                       id="contact_number"
                                       name="contact_number"
                                       value="{{ old('contact_number') }}"
                                       required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="email">Email address</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="message">How can we help?</label>
                                <textarea class="form-control @error('message') is-invalid @enderror"
                                          id="message"
                                          name="message"
                                          rows="4"
                                          required>{{ old('message') }}</textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-hms">
                                    <i class="bi bi-send me-1"></i>Submit query
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('partials.swiper_init')
@endpush

