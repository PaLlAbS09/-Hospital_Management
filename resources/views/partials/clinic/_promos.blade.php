{{--
    Clinic discount offers and "new doctor joining" promotions.

    Expects: $offers, $announcements
--}}
@if ($offers->isNotEmpty() || $announcements->isNotEmpty())
    <div class="mt-4">
        <h2 class="h5 mb-1">Offers &amp; new doctors</h2>
        <p class="text-muted small mb-3">
            @if ($offers->isNotEmpty())
                {{ $offers->count() }} {{ Str::plural('offer', $offers->count()) }} running right now
                @if ($announcements->isNotEmpty())
                    &middot;
                @endif
            @endif
            @if ($announcements->isNotEmpty())
                {{ $announcements->count() }} new {{ Str::plural('doctor', $announcements->count()) }} joining soon
            @endif
            @if ($offers->isEmpty() && $announcements->isEmpty())
                Nothing is running at the moment &mdash; check back soon.
            @endif
        </p>

        <div class="row g-3">
            @if ($offers->isNotEmpty())
                <div class="col-lg-6">
                    <div class="hms-card h-100">
                        <div class="hms-card__header">
                            <h5><i class="bi bi-percent me-2"></i>Current offers</h5>
                            <span class="hms-chip">{{ $offers->count() }} active</span>
                        </div>
                        <div class="hms-card__body">
                            <div class="row g-3">
                                @foreach ($offers as $offer)
                                    <div class="col-sm-6">
                                        <div class="hms-promo hms-promo--offer h-100">
                                            <div class="hms-promo__media" style="flex: 0 0 100%; min-height: 108px">
                                                <div>
                                                    <div class="hms-promo__discount" style="font-size: 1.9rem">
                                                        {{ $offer->discount_label }}
                                                    </div>
                                                    <div class="small text-uppercase fw-semibold mt-1"
                                                         style="letter-spacing:.12em; opacity:.85">
                                                        on every purchase of medicines &amp; services
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="hms-promo__body">
                                                <h4 class="h6 mb-1">{{ $offer->title }}</h4>
                                                @if ($offer->description)
                                                    <p class="hms-promo__text small">{{ $offer->description }}</p>
                                                @else
                                                    <p class="hms-promo__text small">
                                                        Save {{ $offer->discount_percent }}% on your bill at this
                                                        clinic while this offer is running.
                                                    </p>
                                                @endif
                                                @if ($offer->validity_label)
                                                    <span class="hms-chip align-self-start mt-2">
                                                        <i class="bi bi-hourglass-split"></i>{{ $offer->validity_label }}
                                                    </span>
                                                @else
                                                    <span class="hms-chip align-self-start mt-2">
                                                        <i class="bi bi-infinity"></i>Always on
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($announcements->isNotEmpty())
                <div class="col-lg-6">
                    <div class="hms-card h-100">
                        <div class="hms-card__header">
                            <h5><i class="bi bi-megaphone me-2"></i>Doctors joining soon</h5>
                            <span class="hms-chip">{{ $announcements->count() }}</span>
                        </div>
                        <div class="hms-card__body hms-card__body--flush">
                            <ul class="list-group list-group-flush">
                                @foreach ($announcements as $announcement)
                                    <li class="list-group-item">
                                        <div class="fw-semibold">
                                            {{ $announcement->doctor ? 'Dr. '.$announcement->doctor->full_name : 'New doctor' }}
                                        </div>
                                        <div class="small text-muted">
                                                {{ $announcement->department }}
                                                &middot;
                                                @if ($announcement->is_upcoming)
                                                    Starting soon
                                                @else
                                                    Already practising
                                                @endif
                                            </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            {{ $announcement->joining_date?->format('d M Y') ?? 'Date to be announced' }}
                                            @if ($announcement->formatted_joining_time)
                                                &middot; {{ $announcement->formatted_joining_time }}
                                            @endif
                                        </div>
                                        @if ($announcement->message)
                                            <div class="small text-muted mt-1">{{ $announcement->message }}</div>
@else
                                                <div class="small text-muted mt-1">
                                                    Appointments open once the schedule is published &mdash;
                                                    pick this doctor when booking.
                                                </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif