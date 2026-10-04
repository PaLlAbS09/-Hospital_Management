{{--
    "Chosen by patients" slider.

    Lists the doctors the public rated highest. A doctor who is repeatedly
    picked earns the "Best choice in {specialization}" / "Highly recommended
    in {specialization}" badge from DoctorRatingService.

    Expects: $topDoctors (Collection of arrays from DoctorRatingService::leaderboard)
--}}
@php
    // Rendered as a data attribute so the shared Swiper bootstrap can read it.
    $swiperOptions = json_encode([
        'autoplay' => ['delay' => 4200, 'disableOnInteraction' => false],
        'spaceBetween' => 18,
        'slidesPerView' => 1.1,
        'breakpoints' => [
            '576' => ['slidesPerView' => 2],
            '992' => ['slidesPerView' => 3],
        ],
    ]);
@endphp

@if ($topDoctors->isNotEmpty())
    <section class="container mt-5" aria-label="Doctors rated best by patients">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <h2 class="h4 mb-1">Chosen by patients</h2>
                <p class="text-muted mb-0 small">
                    These doctors carry the highest public rating after completed visits.
                </p>
            </div>
            <span class="hms-chip"><i class="bi bi-star-fill"></i>{{ $topDoctors->count() }} rated doctors</span>
        </div>

        <div class="hms-slider">
            <div class="swiper" id="hms-best-doctors" data-hms-swiper="{{ $swiperOptions }}">
                <div class="swiper-wrapper">
                    @foreach ($topDoctors as $entry)
                        @php($doctor = $entry['doctor'])
                        <div class="swiper-slide">
                            <article class="hms-best">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="hms-best__avatar">{{ $doctor->initials }}</span>
                                    <div class="min-w-0" style="min-width:0">
                                        <h3 class="h6 mb-1 text-truncate">Dr. {{ $doctor->full_name }}</h3>
                                        <div class="text-muted small text-truncate">{{ $doctor->specialization }}</div>
                                    </div>
                                </div>

                                @if ($entry['badge_label'])
                                    <span class="hms-best__badge hms-best__badge--{{ $entry['badge']['tone'] }}">
                                        <i class="bi bi-award-fill"></i>{{ $entry['badge_label'] }}
                                    </span>
                                @else
                                    <span class="hms-chip align-self-start">
                                        <i class="bi bi-person-check"></i>{{ $doctor->experience_label ?? 'Verified visits' }}
                                    </span>
                                @endif

                                <div class="d-flex align-items-center gap-2">
                                    <span class="hms-best__stars">{{ $doctor->stars }}</span>
                                    <span class="hms-best__score">{{ number_format($entry['average'], 1) }}</span>
                                    <span class="text-muted small">
                                        ({{ $entry['count'] }} rating{{ $entry['count'] === 1 ? '' : 's' }})
                                    </span>
                                </div>

                                <div class="mt-auto d-flex gap-2">
                                    <a href="{{ route('clinics.index') }}" class="btn btn-sm btn-hms flex-grow-1">
                                        Book appointment
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </section>
@endif