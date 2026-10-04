{{--
    Doctors at a clinic with their public rating and the "best choice" /
    "highly recommended" badge awarded by DoctorRatingService.

    Expects: $ratedDoctors, $bookingUrl, $swiperId
--}}
@if ($ratedDoctors->isNotEmpty())
    @php
        $swiperOptions = json_encode([
            'spaceBetween' => 18,
            'slidesPerView' => 1.1,
            'breakpoints' => [
                '576' => ['slidesPerView' => 2],
                '992' => ['slidesPerView' => 3],
            ],
        ]);
    @endphp

    <div class="mt-4">
        <h2 class="h5 mb-1">Doctors at this clinic</h2>
        <p class="text-muted small mb-3">
            Rated by patients after completed visits. Badges highlight the doctors people keep choosing.
        </p>

        <div class="hms-slider">
            <div class="swiper" id="{{ $swiperId }}" data-hms-swiper="{{ $swiperOptions }}">
                <div class="swiper-wrapper">
                    @foreach ($ratedDoctors as $entry)
                        @php($doctor = $entry['doctor'])
                        <div class="swiper-slide">
                            <article class="hms-best">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="hms-best__avatar">{{ $doctor->initials }}</span>
                                    <div style="min-width:0">
                                        <h3 class="h6 mb-1 text-truncate">Dr. {{ $doctor->full_name }}</h3>
                                        <div class="text-muted small text-truncate">{{ $doctor->specialization }}</div>
                                    </div>
                                </div>

                                @if ($entry['badge_label'])
                                    <span class="hms-best__badge hms-best__badge--{{ $entry['badge']['tone'] }}">
                                        <i class="bi bi-award-fill"></i>{{ $entry['badge_label'] }}
                                    </span>
                                @endif

                                @if ($doctor->experience_label)
                                    <span class="hms-chip align-self-start">
                                        <i class="bi bi-briefcase"></i>{{ $doctor->experience_label }}
                                    </span>
                                @endif

                                @if ($doctor->experience_note)
                                    <p class="small text-muted mb-0">{{ $doctor->experience_note }}</p>
                                @endif

                                @if ($entry['count'] > 0)
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="hms-best__stars">{{ $doctor->stars }}</span>
                                        <span class="hms-best__score">{{ number_format($entry['average'], 1) }}</span>
                                        <span class="text-muted small">
                                            ({{ $entry['count'] }} rating{{ $entry['count'] === 1 ? '' : 's' }})
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted small">No public ratings yet.</span>
                                @endif

                                <div class="mt-auto">
                                    <a href="{{ $bookingUrl }}" class="btn btn-sm btn-hms w-100">
                                        <i class="bi bi-calendar-check me-1"></i>Book Dr. {{ $doctor->last_name }}
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
    </div>
@endif