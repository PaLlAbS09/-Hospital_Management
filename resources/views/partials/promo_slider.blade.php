{{--
    Landing page promo slider.

    Mixes clinic-published "new doctor joining" announcements with their
    discount offers in a single Swiper track, exactly as requested.

    Expects: $announcements (Collection<ClinicAnnouncement>), $offers (Collection<ClinicOffer>)
--}}
@php
    $hasAnnouncements = $announcements->isNotEmpty();
    $hasOffers = $offers->isNotEmpty();

    // Rendered as a data attribute so the shared Swiper bootstrap can read it.
    $swiperOptions = json_encode([
        'autoplay' => ['delay' => 5200, 'disableOnInteraction' => false],
        'spaceBetween' => 20,
        'breakpoints' => [
            '768' => ['slidesPerView' => 1],
            '1200' => ['slidesPerView' => 1.35],
        ],
    ]);
@endphp

@if ($hasAnnouncements || $hasOffers)
    <section class="hms-slider" aria-label="Latest clinic announcements and offers">
        <div class="swiper" id="hms-promo-slider" data-hms-swiper="{{ $swiperOptions }}">
            <div class="swiper-wrapper">

                {{-- New doctor joining ---------------------------------------- --}}
                @foreach ($announcements as $announcement)
                    <div class="swiper-slide">
                        <article class="hms-promo hms-promo--doctor">
                            <div class="hms-promo__media">
                                <div>
                                    <div class="hms-promo__doctor-initial">
                                        {{ $announcement->doctor?->initials ?? 'DR' }}
                                    </div>
                                    <div class="small text-uppercase fw-semibold" style="letter-spacing:.12em; opacity:.8">
                                        New doctor
                                    </div>
                                    <div class="fw-bold">{{ $announcement->clinic?->clinic_name }}</div>
                                </div>
                            </div>

                            <div class="hms-promo__body">
                                <div class="hms-promo__eyebrow">
                                    {{ $announcement->department }}
                                    @if ($announcement->clinic?->area)
                                        &middot; {{ $announcement->clinic->area }}
                                    @endif
                                </div>

                                <h3 class="hms-promo__title">
                                    @if ($announcement->doctor)
                                        Dr. {{ $announcement->doctor->full_name }} is joining
                                        {{ $announcement->clinic?->clinic_name }}
                                    @else
                                        A new {{ strtolower($announcement->department) }} specialist is joining
                                        {{ $announcement->clinic?->clinic_name }}
                                    @endif
                                </h3>

                                @if ($announcement->message)
                                    <p class="hms-promo__text">{{ $announcement->message }}</p>
                                @else
                                    <p class="hms-promo__text">
                                        Book an appointment with the new {{ strtolower($announcement->department) }} doctor
                                        as soon as the schedule is published.
                                    </p>
                                @endif

                                <div class="hms-promo__meta">
                                    @if ($announcement->joining_date)
                                        <span class="hms-chip">
                                            <i class="bi bi-calendar-event"></i>
                                            {{ $announcement->joining_date->format('D, d M Y') }}
                                        </span>
                                    @endif

                                    @if ($announcement->formatted_joining_time)
                                        <span class="hms-chip">
                                            <i class="bi bi-clock"></i>{{ $announcement->formatted_joining_time }}
                                        </span>
                                    @endif

                                    <a href="{{ $announcement->clinic ? route('clinics.show', $announcement->clinic) : route('clinics.index') }}"
                                       class="btn btn-sm btn-hms ms-auto">
                                        View clinic <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach

                {{-- Discount offers ------------------------------------------- --}}
                @foreach ($offers as $offer)
                    <div class="swiper-slide">
                        <article class="hms-promo hms-promo--offer">
                            <div class="hms-promo__media">
                                <div>
                                    <i class="bi bi-tag-fill fs-2 d-block mb-2 opacity-75"></i>
                                    <div class="hms-promo__discount">{{ $offer->discount_label }}</div>
                                    <div class="small text-uppercase fw-semibold mt-1" style="letter-spacing:.12em; opacity:.8">
                                        Limited offer
                                    </div>
                                </div>
                            </div>

                            <div class="hms-promo__body">
                                <div class="hms-promo__eyebrow">
                                    {{ $offer->clinic?->clinic_name }}
                                    @if ($offer->clinic?->area)
                                        &middot; {{ $offer->clinic->area }}
                                    @endif
                                </div>

                                <h3 class="hms-promo__title">{{ $offer->title }}</h3>

                                @if ($offer->description)
                                    <p class="hms-promo__text">{{ $offer->description }}</p>
                                @else
                                    <p class="hms-promo__text">
                                        Save {{ $offer->discount_percent }}% on your bill at
                                        {{ $offer->clinic?->clinic_name }} during this offer period.
                                    </p>
                                @endif

                                <div class="hms-promo__meta">
                                    @if ($offer->validity_label)
                                        <span class="hms-chip">
                                            <i class="bi bi-hourglass-split"></i>{{ $offer->validity_label }}
                                        </span>
                                    @else
                                        <span class="hms-chip"><i class="bi bi-infinity"></i>Always on</span>
                                    @endif

                                    <a href="{{ $offer->clinic ? route('clinics.show', $offer->clinic) : route('clinics.index') }}"
                                       class="btn btn-sm btn-hms ms-auto">
                                        Book now <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </section>
@endif