@extends('layouts.public')

@section('title', 'Trusted clinic appointments')

@section('content')

    <div class="hms-landing">

    {{-- 1. Hero first -------------------------------------------------------- --}}
    @include('partials.home_hero')

    {{-- 2. Offers + new doctors --------------------------------------------- --}}
    @if ($announcements->isNotEmpty() || $offers->isNotEmpty())
    <div class="container mt-5">
        <div class="hms-section-head">
            <div>
                <span class="hms-eyebrow"><i class="bi bi-megaphone-fill"></i>What's new</span>
                <h2>Offers &amp; new doctors</h2>
                <p>Latest announcements and limited-period discounts from approved clinics.</p>
            </div>
        </div>
        @include('partials.promo_slider')
    </div>
    @endif
    {{-- 3. Clinic directory -------------------------------------------------- --}}
    <div class="container mt-5">
        <div class="hms-section-head">
            <div>
                <span class="hms-eyebrow"><i class="bi bi-hospital"></i>Find care</span>
                <h2>
                    @if ($area)
                        Clinics in &ldquo;{{ $area }}&rdquo;
                    @else
                        Clinic directory
                    @endif
                </h2>
                <p>Only clinics approved by an administrator can accept appointments.</p>
            </div>
            <a href="{{ route('clinics.index', array_filter(['area' => $area])) }}" class="btn btn-hms">
                Browse all clinics <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        @if ($clinics->isNotEmpty())
            @php
                // Swiper options consumed by `partials.swiper_init` via `data-hms-swiper`.
                $clinicSwiperOptions = json_encode([
                    'slidesPerView' => 1,
                    'spaceBetween' => 20,
                    'autoplay' => ['delay' => 4500, 'disableOnInteraction' => false],
                    'breakpoints' => [
                        '768' => ['slidesPerView' => 2],
                        '1200' => ['slidesPerView' => 3],
                    ],
                ]);
            @endphp
            <section class="hms-slider hms-clinic-slider" aria-label="Clinic directory">
                <div class="swiper" id="hms-clinic-slider" data-hms-swiper="{{ $clinicSwiperOptions }}">
                    <div class="swiper-wrapper">
                        @foreach ($clinics as $clinic)
                            <div class="swiper-slide">
                                <div class="hms-clinic-card h-100 w-100">
                                    <div class="hms-clinic-card__top">
                                        <span class="hms-clinic-card__icon"><i class="bi bi-hospital"></i></span>
                                        <div class="flex-grow-1 min-w-0">
                                            <h3>{{ $clinic->clinic_name }}</h3>
                                            <div class="hms-clinic-card__area">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $clinic->area }}
                                            </div>
                                        </div>
                                        <x-status-badge :status="$clinic->status" />
                                    </div>
                                    <div class="hms-clinic-card__contact">
                                        <div><i class="bi bi-telephone me-1"></i>{{ $clinic->contact_number }}</div>
                                        <div class="text-truncate"><i class="bi bi-envelope me-1"></i>{{ $clinic->email }}</div>
                                    </div>
                                    <div class="hms-clinic-card__foot">
                                        <span class="hms-chip">
                                            <i class="bi bi-calendar3"></i>{{ $clinic->schedules_count }} published
                                        </span>
                                        <a href="{{ route('patient.clinics.doctors', $clinic) }}" class="btn btn-sm btn-hms">
                                            View doctors
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
            </section>
        @else
            <div class="hms-card">
                <div class="hms-empty">
                    <i class="bi bi-search"></i>
                    <p class="mb-1 fw-semibold">No approved clinics found{{ $area ? ' in '.$area : '' }}.</p>
                    <p class="mb-0 small">Try a different area or browse the full directory.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- 4. How it works ------------------------------------------------------ --}}
    <div class="container mt-5">
        <div class="hms-section-head text-center justify-content-center">
            <div>
                <span class="hms-eyebrow"><i class="bi bi-magic"></i>How it works</span>
                <h2>Care in three simple steps</h2>
                <p>No phone calls. No queues. Just pick clinic, doctor and slot.</p>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="hms-step-card">
                    <span class="hms-step-card__num">01</span>
                    <span class="hms-step-card__icon"><i class="bi bi-geo-alt"></i></span>
                    <h6>Search by area</h6>
                    <p>Filter approved clinics by the area you live in.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="hms-step-card">
                    <span class="hms-step-card__num">02</span>
                    <span class="hms-step-card__icon"><i class="bi bi-person-badge"></i></span>
                    <h6>Pick doctor &amp; slot</h6>
                    <p>See doctors on duty and exact slots still available.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="hms-step-card">
                    <span class="hms-step-card__num">03</span>
                    <span class="hms-step-card__icon"><i class="bi bi-file-medical"></i></span>
                    <h6>Collect prescription</h6>
                    <p>After the visit view and print your prescription online.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Best doctors -------------------------------------------------------- --}}
    @include('partials.best_doctors')

    {{-- 6. Portals ------------------------------------------------------------ --}}
    <div class="container mt-5">
        <div class="hms-portals">
            <div class="row g-3 align-items-center">
                <div class="col-lg-5">
                    <span class="hms-eyebrow"><i class="bi bi-grid-1x2-fill"></i>One platform</span>
                    <h2 class="mt-2">Everyone connects here</h2>
                    <p class="mb-0">Patients book visits, clinics publish schedules, doctors manage consultations.</p>
                </div>
                <div class="col-lg-7">
                    <div class="row g-2">
                        <div class="col-sm-4">
                            <a href="{{ route('patient.login') }}" class="hms-portal-link">
                                <i class="bi bi-person-heart"></i><strong>Patient</strong><small>Book &amp; track visits</small>
                            </a>
                        </div>
                        <div class="col-sm-4">
                            <a href="{{ route('clinic.login') }}" class="hms-portal-link">
                                <i class="bi bi-hospital"></i><strong>Clinic</strong><small>Schedules &amp; doctors</small>
                            </a>
                        </div>
                        <div class="col-sm-4">
                            <a href="{{ route('doctor.login') }}" class="hms-portal-link">
                                <i class="bi bi-person-badge"></i><strong>Doctor</strong><small>Consults &amp; Rx</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 7. Contact ------------------------------------------------------------ --}}
    <div class="container mt-5" id="contact">
        <div class="hms-contact">
            <div class="row g-0">
                <div class="col-lg-5 hms-contact__side">
                    <span class="hms-eyebrow hms-eyebrow--on-teal"><i class="bi bi-chat-dots-fill"></i>Contact us</span>
                    <h2 class="mt-2">We're here to help</h2>
                    <p>Send a message and the hospital administration team will get back to you.</p>
                    <ul>
                        <li><i class="bi bi-clock-history"></i>Responses within 1 working day</li>
                        <li><i class="bi bi-geo-alt"></i>Area-wise clinic network</li>
                        <li><i class="bi bi-shield-check"></i>Details only used to answer your query</li>
                    </ul>
                </div>

                <div class="col-lg-7 hms-contact__form">
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
                                <button type="submit" class="btn btn-hms btn-lg w-100">
                                    <i class="bi bi-send me-1"></i>Submit query
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @include('partials.swiper_init')
@endpush

