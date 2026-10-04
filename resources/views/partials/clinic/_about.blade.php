{{--
    Clinic about section: banner image, description, contact details and the
    "why choose this clinic" summary numbers.

    Expects: $clinic, $ratedDoctors, $reviews, $offers, $announcements,
             $bookingUrl, $bookingLabel, $backUrl, $backLabel
--}}
<div class="hms-card overflow-hidden">
    <div class="hms-about-banner">
        @if ($clinic->banner_url)
            <img src="{{ $clinic->banner_url }}" alt="{{ $clinic->clinic_name }} banner">
        @else
            <div>
                <i class="bi bi-hospital fs-1 d-block mb-2 opacity-75"></i>
                <div class="fw-semibold">{{ $clinic->clinic_name }}</div>
                <div class="small" style="opacity:.75">{{ $clinic->area }}</div>
            </div>
        @endif
    </div>

    <div class="hms-card__body">
        <div class="row g-4">
            <div class="col-lg-7">
                <span class="hms-chip mb-2"><i class="bi bi-hospital"></i>About this clinic</span>
                <h1 class="h4 mb-2">{{ $clinic->clinic_name }}</h1>

                <ul class="list-unstyled small text-muted mb-3">
                    <li class="mb-1"><i class="bi bi-geo-alt me-2"></i>{{ $clinic->area }}</li>
                    <li class="mb-1"><i class="bi bi-telephone me-2"></i>{{ $clinic->contact_number }}</li>
                    <li><i class="bi bi-envelope me-2"></i>{{ $clinic->email }}</li>
                </ul>

                @if ($clinic->about)
                    <p class="mb-0" style="white-space: pre-line">{{ $clinic->about }}</p>
                @else
                    <p class="text-muted mb-0">
                        This clinic has not written an about section yet.
                    </p>
                @endif
            </div>

            <div class="col-lg-5">
                <div class="d-grid gap-2">
                    <a href="{{ $bookingUrl }}" class="btn btn-hms">
                        <i class="bi bi-calendar-plus me-1"></i>{{ $bookingLabel }}
                    </a>
                    <a href="{{ $backUrl }}" class="btn btn-outline-hms">
                        <i class="bi bi-arrow-left me-1"></i>{{ $backLabel }}
                    </a>
                </div>

                <div class="row g-2 mt-3">
                    @foreach ([
                        ['value' => $ratedDoctors->count(), 'label' => 'Doctors'],
                        ['value' => $reviews->count(), 'label' => 'Reviews'],
                        ['value' => $offers->count(), 'label' => 'Offers'],
                    ] as $stat)
                        <div class="col-4">
                            <div class="hms-hero__stat text-center"
                                 style="background: var(--hms-primary-soft); border-color: rgba(79,70,229,.18)">
                                <strong class="text-dark">{{ $stat['value'] }}</strong>
                                <span class="text-dark" style="opacity:.75">{{ $stat['label'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($announcements->isNotEmpty() || $offers->isNotEmpty())
                    <div class="mt-3 d-flex flex-column gap-2 align-items-start">
                        @if ($offers->isNotEmpty())
                            <span class="hms-best__badge hms-best__badge--gold">
                                <i class="bi bi-tag-fill"></i>
                                {{ $offers->first()->title }}
                            </span>
                        @endif

                        @if ($announcements->isNotEmpty())
                            <span class="hms-best__badge hms-best__badge--indigo">
                                <i class="bi bi-megaphone-fill"></i>
                                New {{ strtolower($announcements->first()->department) }} doctor joining
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>