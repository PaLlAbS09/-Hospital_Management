@extends('layouts.app')

@section('title', $clinic->clinic_name)
@section('subtitle', $clinic->area.' - Why patients choose this clinic, then book a slot')

@section('content')
    @php
        $ratingAverage = $reviews->avg('rating');
        $ratingCount = $reviews->count();
        $leadOffer = $offers->first();
        $leadAnnouncement = $announcements->first();
    @endphp

    {{-- Booking flow: 1 pick clinic (done) -> 2 review -> 3 choose slot --}}
    <nav class="hms-steps" aria-label="Booking progress">
        <a href="{{ route('patient.clinics.index') }}" class="hms-steps__item is-done">
            <span class="hms-steps__dot">1</span>Clinic
        </a>
        <span class="hms-steps__line"></span>
        <span class="hms-steps__item is-current">
            <span class="hms-steps__dot">2</span>Why choose us
        </span>
        <span class="hms-steps__line"></span>
        <span class="hms-steps__item">
            <span class="hms-steps__dot">3</span>Book a slot
        </span>
    </nav>

    {{-- Hero ------------------------------------------------------------ --}}
    <section class="hms-prof">
        <div class="hms-prof__banner">
            @if ($clinic->banner_url)
                <img src="{{ $clinic->banner_url }}" alt="{{ $clinic->clinic_name }} banner">
            @endif

            <div class="hms-prof__intro">
                <span class="hms-prof__eyebrow">
                    <i class="bi bi-hospital-fill me-1"></i>About this clinic
                </span>

                <h1 class="hms-prof__name">{{ $clinic->clinic_name }}</h1>

                <ul class="hms-prof__meta">
                    <li><i class="bi bi-geo-alt-fill"></i>{{ $clinic->area }}</li>
                    <li><i class="bi bi-telephone-fill"></i>{{ $clinic->contact_number }}</li>
                    <li><i class="bi bi-envelope-fill"></i>{{ $clinic->email }}</li>
                </ul>

                <div class="hms-prof__badges">
                    @if ($ratingCount > 0)
                        <span class="hms-prof__badge is-gold">
                            <i class="bi bi-star-fill"></i>
                            <span>{{ number_format($ratingAverage, 1) }}</span>
                            <small>({{ $ratingCount.' '.Str::plural('review', $ratingCount) }})</small>
                        </span>
                    @endif

                    @if ($leadOffer)
                        <span class="hms-prof__badge is-emerald">
                            <i class="bi bi-tag-fill"></i>{{ $leadOffer->discount_label }} offer
                        </span>
                    @endif

                    @if ($leadAnnouncement)
                        <span class="hms-prof__badge is-sky">
                            <i class="bi bi-megaphone-fill"></i>
                            New {{ strtolower($leadAnnouncement->department) }} doctor joining
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="hms-prof__body">
            <div class="row g-4">
                <div class="col-lg-7">
                    <h2 class="hms-prof__heading">About the clinic</h2>

                    @if ($clinic->about)
                        <p class="hms-prof__about">{{ $clinic->about }}</p>
                    @else
                        <p class="hms-prof__about is-empty">
                            This clinic has not written an about section yet. Ask at the front desk
                            for details on departments, timings and facilities.
                        </p>
                    @endif

                    @if ($leadOffer)
                        <p class="hms-prof__note">
                            <i class="bi bi-stars me-1"></i>
                            {{ $leadOffer->title }}
                        </p>
                    @endif
                </div>

                <div class="col-lg-5">
                    <div class="hms-prof__stats">
                        @foreach ([
                            ['value' => $ratedDoctors->count(), 'label' => 'Doctors', 'icon' => 'bi-person-badge'],
                            ['value' => $ratingCount, 'label' => 'Reviews', 'icon' => 'bi-chat-quote'],
                            ['value' => $offers->count(), 'label' => 'Offers', 'icon' => 'bi-tag'],
                        ] as $stat)
                            <div class="hms-prof__stat">
                                <i class="bi {{ $stat['icon'] }}"></i>
                                <strong>{{ $stat['value'] }}</strong>
                                <span>{{ $stat['label'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ route('patient.clinics.doctors', $clinic) }}" class="btn btn-hms hms-prof__cta">
                        <i class="bi bi-calendar-plus me-1"></i>See doctors &amp; book a slot
                    </a>

                    <a href="{{ route('patient.clinics.index') }}" class="hms-prof__back">
                        <i class="bi bi-arrow-left me-1"></i>Back to clinic search
                    </a>
                </div>
            </div>
        </div>
    </section>

    @include('partials.clinic._promos')

    @include('partials.clinic._doctors', [
        'bookingUrl' => route('patient.clinics.doctors', $clinic),
        'swiperId' => 'hms-patient-clinic-doctors',
    ])

    @include('partials.clinic._reviews')
@endsection

@push('scripts')
    @include('partials.swiper_init')
@endpush
@push('styles')
    <style>
        /* Booking progress ------------------------------------------------- */
        .hms-steps { display: flex; align-items: center; gap: .5rem; margin-bottom: 1.1rem; flex-wrap: wrap; }
        .hms-steps__item {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .82rem;
            font-weight: 700;
            color: var(--hms-muted);
            text-decoration: none;
        }
        .hms-steps__dot {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: .78rem;
            background: #fff;
            border: 1px solid var(--hms-line);
        }
        .hms-steps__line { flex: 1 1 26px; height: 2px; border-radius: 2px; background: var(--hms-line); }
        .hms-steps__item.is-done { color: var(--hms-primary); }
        .hms-steps__item.is-done .hms-steps__dot { background: #dcfce7; border-color: #86efac; color: #15803d; }
        .hms-steps__item.is-current { color: var(--hms-primary); }
        .hms-steps__item.is-current .hms-steps__dot { background: var(--hms-primary); border-color: var(--hms-primary); color: #fff; }

        /* Clinic hero ------------------------------------------------------ */
        .hms-prof { background: #fff; border: 1px solid var(--hms-line); border-radius: 20px; overflow: hidden; box-shadow: 0 18px 45px rgba(15, 23, 42, .08); }
        .hms-prof__banner { position: relative; isolation: isolate; background: linear-gradient(125deg, #1e1b4b 0%, #4338ca 52%, #0ea5e9 100%); }
        .hms-prof__banner::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(circle at 85% 18%, rgba(255, 255, 255, .22), transparent 45%),
                radial-gradient(circle at 12% 88%, rgba(14, 165, 233, .38), transparent 55%);
        }
        .hms-prof__banner img { position: absolute; inset: 0; z-index: -2; width: 100%; height: 100%; object-fit: cover; }
        .hms-prof__banner:has(img)::after { opacity: 1; }
        .hms-prof__intro { padding: 1.9rem 1.9rem 2rem; color: #fff; }
        .hms-prof__eyebrow {
            display: inline-flex;
            align-items: center;
            padding: .3rem .72rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .24);
        }
        .hms-prof__name { font-size: 1.9rem; font-weight: 800; letter-spacing: -.025em; line-height: 1.15; margin: .85rem 0 .7rem; }
        .hms-prof__meta { list-style: none; display: flex; flex-wrap: wrap; gap: .4rem 1.4rem; margin: 0 0 1rem; padding: 0; }
        .hms-prof__meta li { display: inline-flex; align-items: center; gap: .4rem; font-size: .85rem; color: rgba(255, 255, 255, .85); }
        .hms-prof__badges { display: flex; flex-wrap: wrap; gap: .4rem; }
        .hms-prof__badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .76rem;
            font-weight: 700;
            padding: .3rem .7rem;
            border-radius: 999px;
            color: #fff;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .26);
        }
        .hms-prof__badge small { font-weight: 600; opacity: .8; }
        .hms-prof__badge.is-gold { background: rgba(245, 158, 11, .28); border-color: rgba(253, 230, 138, .5); }
        .hms-prof__badge.is-emerald { background: rgba(16, 185, 129, .26); border-color: rgba(110, 231, 183, .5); }
        .hms-prof__badge.is-sky { background: rgba(56, 189, 248, .26); border-color: rgba(186, 230, 253, .5); }

        /* Clinic body ------------------------------------------------------ */
        .hms-prof__body { padding: 1.6rem 1.9rem 1.75rem; }
        .hms-prof__heading { font-size: 1.05rem; font-weight: 700; letter-spacing: -.01em; margin: 0 0 .6rem; }
        .hms-prof__about { font-size: .92rem; line-height: 1.7; color: #475569; margin: 0; white-space: pre-line; }
        .hms-prof__about.is-empty { color: var(--hms-muted); font-style: italic; }
        .hms-prof__note {
            display: flex;
            align-items: center;
            gap: .4rem;
            font-size: .82rem;
            font-weight: 600;
            color: #b45309;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: .55rem .8rem;
            margin: 1rem 0 0;
        }
        .hms-prof__stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: .6rem; }
        .hms-prof__stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .1rem;
            padding: .85rem .5rem;
            border-radius: 14px;
            background: var(--hms-primary-soft);
            border: 1px solid rgba(79, 70, 229, .16);
        }
        .hms-prof__stat i { color: var(--hms-primary); font-size: 1rem; }
        .hms-prof__stat strong { font-size: 1.35rem; font-weight: 800; line-height: 1.15; color: #1e1b4b; }
        .hms-prof__stat span { font-size: .68rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--hms-muted); }
        .hms-prof__cta { display: block; width: 100%; margin-top: .85rem; padding-block: .7rem; font-weight: 700; }
        .hms-prof__back { display: block; text-align: center; font-size: .84rem; font-weight: 600; color: var(--hms-muted); text-decoration: none; margin-top: .6rem; }
        .hms-prof__back:hover { color: var(--hms-primary); }

        @media (max-width: 575.98px) {
            .hms-prof__intro { padding: 1.4rem 1.25rem 1.5rem; }
            .hms-prof__body { padding: 1.25rem; }
            .hms-prof__name { font-size: 1.5rem; }
            .hms-steps { gap: .35rem; }
        }
    </style>
@endpush