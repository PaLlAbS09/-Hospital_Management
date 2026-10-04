@extends('layouts.app')

@section('title', 'Find a clinic')
@section('subtitle', 'Search approved clinics by area, then pick a doctor and time slot')

@section('content')
    @php
        // One accent per card so a page of results does not read as a wall of clones.
        $accents = [
            'linear-gradient(135deg, #4f46e5, #7c3aed)',
            'linear-gradient(135deg, #0891b2, #0d9488)',
            'linear-gradient(135deg, #db2777, #e11d48)',
            'linear-gradient(135deg, #ea580c, #d97706)',
            'linear-gradient(135deg, #0f766e, #059669)',
        ];
    @endphp

    {{-- Search ---------------------------------------------------------- --}}
    <section class="hms-find">
        <div class="hms-find__glow" aria-hidden="true"></div>

        <div class="hms-find__panel">
            <span class="hms-find__eyebrow"><i class="bi bi-compass me-1"></i>Clinic discovery</span>
            <h1 class="hms-find__title">Search clinics in your area</h1>
            <p class="hms-find__lead">
                Only clinics approved by an administrator can accept bookings.
            </p>

            <form method="GET" action="{{ route('patient.clinics.index') }}" class="hms-find__form">
                <label class="visually-hidden" for="area">Area</label>
                <div class="hms-find__field">
                    <i class="bi bi-geo-alt" aria-hidden="true"></i>
                    <input type="text"
                           class="form-control"
                           id="area"
                           name="area"
                           value="{{ $area }}"
                           list="area-options"
                           placeholder="Enter your area, e.g. Bardhaman"
                           autocomplete="off">
                    <datalist id="area-options">
                        @foreach ($areas as $option)
                            <option value="{{ $option }}"></option>
                        @endforeach
                    </datalist>
                </div>

                <button type="submit" class="btn btn-hms hms-find__submit">
                    <i class="bi bi-search me-1"></i>Search
                </button>

                @if ($area)
                    <a href="{{ route('patient.clinics.index') }}" class="btn btn-link text-decoration-none">Reset</a>
                @endif
            </form>

            <div class="hms-find__meta">
                <span class="hms-find__count">
                    <i class="bi bi-buildings me-1"></i>{{ $clinics->total().' '.Str::plural('clinic', $clinics->total()).' found' }}
                </span>

                @if ($areas->isNotEmpty())
                    <div class="hms-find__areas">
                        @foreach ($areas->take(8) as $option)
                            <a href="{{ route('patient.clinics.index', ['area' => $option]) }}"
                               class="hms-find__area {{ $area === $option ? 'is-active' : '' }}">
                                <i class="bi bi-geo-alt-fill"></i>{{ $option }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Results ---------------------------------------------------------- --}}
    <div class="row g-4">
        @forelse ($clinics as $clinic)
            @php
                $summary = $summaries->get($clinic->clinic_id);
                $clinicOffers = $summary['offers'] ?? collect();
                $clinicPromos = $summary['announcements'] ?? collect();
                $clinicRating = $summary['rating_average'] ?? null;
                $clinicRatingCount = $summary['rating_count'] ?? 0;
                $clinicDoctors = $summary['doctor_count'] ?? 0;
                $accent = $accents[$loop->index % count($accents)];
            @endphp

            <div class="col-md-6 col-xl-4">
                <article class="hms-clinic h-100" style="--hms-clinic-accent: {{ $accent }}">
                    <header class="hms-clinic__head">
                        <span class="hms-clinic__icon"><i class="bi bi-hospital-fill"></i></span>

                        <div class="hms-clinic__ident">
                            <h2 class="hms-clinic__name">{{ $clinic->clinic_name }}</h2>
                            <p class="hms-clinic__area">
                                <i class="bi bi-geo-alt-fill me-1"></i>{{ $clinic->area }}
                            </p>
                        </div>

                        @if ($clinicRatingCount > 0)
                            <span class="hms-clinic__rating" title="Rated {{ number_format($clinicRating, 1) }} of 5">
                                <i class="bi bi-star-fill"></i>{{ number_format($clinicRating, 1) }}
                                <small>{{ $clinicRatingCount }}</small>
                            </span>
                        @else
                            <span class="hms-clinic__rating is-empty" title="No reviews yet">
                                <i class="bi bi-star"></i>New
                            </span>
                        @endif
                    </header>

                    <div class="hms-clinic__body">
                        @if ($clinic->about)
                            <p class="hms-clinic__about">
                                {{ \Illuminate\Support\Str::limit($clinic->about, 96) }}
                            </p>
                        @else
                            <p class="hms-clinic__about is-empty">
                                This clinic has not written an about section yet.
                            </p>
                        @endif

                        <div class="hms-clinic__tags">
                            @if ($clinicOffers->isNotEmpty())
                                <span class="hms-tag hms-tag--gold">
                                    <i class="bi bi-tag-fill"></i>{{ $clinicOffers->first()->discount_label }}
                                </span>
                            @endif

                            @if ($clinicPromos->isNotEmpty())
                                <span class="hms-tag hms-tag--violet">
                                    <i class="bi bi-megaphone-fill"></i>New doctor joining
                                </span>
                            @endif

                            @if ($clinicDoctors > 0)
                                <span class="hms-tag">
                                    <i class="bi bi-person-badge"></i>{{ $clinicDoctors.' '.Str::plural('doctor', $clinicDoctors) }}
                                </span>
                            @endif
                        </div>

                        <dl class="hms-clinic__contact">
                            <div>
                                <dt><i class="bi bi-telephone-fill"></i></dt>
                                <dd>{{ $clinic->contact_number }}</dd>
                            </div>
                            <div>
                                <dt><i class="bi bi-envelope-fill"></i></dt>
                                <dd class="text-truncate">{{ $clinic->email }}</dd>
                            </div>
                            <div>
                                <dt><i class="bi bi-calendar3"></i></dt>
                                <dd>{{ $clinic->schedules_count.' '.Str::plural('slot', $clinic->schedules_count) }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Single action: opens this clinic's profile. --}}
                    <footer class="hms-clinic__foot">
                        <a href="{{ route('patient.clinics.show', $clinic) }}" class="btn hms-clinic__cta stretched-link">
                            View clinic<i class="bi bi-arrow-right"></i>
                        </a>
                    </footer>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="hms-card">
                    <div class="hms-empty">
                        <i class="bi bi-geo-alt"></i>
                        <p class="fw-semibold mb-1">No approved clinics match your search.</p>
                        <p class="mb-3 small">Try another area or clear the filter to see every clinic.</p>
                        <a href="{{ route('patient.clinics.index') }}" class="btn btn-sm btn-outline-hms">Show all clinics</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if ($clinics->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $clinics->links() }}
        </div>
    @endif
@endsection


@push('styles')
    <style>
        /* Find-a-clinic search hero ---------------------------------------- */
        .hms-find {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            border-radius: 22px;
            padding: 1.9rem 1.75rem;
            margin-bottom: 1.75rem;
            color: #fff;
            background:
                radial-gradient(720px 300px at 8% -20%, rgba(99, 102, 241, .55), transparent 62%),
                radial-gradient(620px 280px at 95% 120%, rgba(6, 182, 212, .42), transparent 62%),
                linear-gradient(135deg, #101828, #312e81 58%, #0e7490);
        }

        .hms-find__glow {
            position: absolute;
            z-index: -1;
            width: 340px;
            height: 340px;
            right: -110px;
            top: -150px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .14), transparent 66%);
        }

        .hms-find__eyebrow {
            display: inline-flex;
            align-items: center;
            padding: .3rem .7rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .22);
        }

        .hms-find__title { font-size: 1.6rem; font-weight: 800; letter-spacing: -.02em; margin: .85rem 0 .3rem; }
        .hms-find__lead { color: rgba(255, 255, 255, .78); font-size: .9rem; margin-bottom: 1.2rem; }

        .hms-find__form { display: flex; flex-wrap: wrap; gap: .6rem; align-items: stretch; }
        .hms-find__field { position: relative; flex: 1 1 260px; min-width: 0; }

        .hms-find__field i {
            position: absolute;
            top: 50%;
            left: .9rem;
            transform: translateY(-50%);
            color: var(--hms-primary);
            pointer-events: none;
        }

        .hms-find__field .form-control { padding-left: 2.5rem; height: 46px; border-radius: 12px; }
        .hms-find__submit { height: 46px; padding-inline: 1.6rem; border-radius: 12px; }
        .hms-find__meta { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem 1rem; margin-top: 1.1rem; }

        .hms-find__count {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .82rem;
            font-weight: 700;
            padding: .32rem .75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .16);
        }

        .hms-find__areas { display: flex; flex-wrap: wrap; gap: .4rem; }

        .hms-find__area {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .78rem;
            padding: .3rem .7rem;
            border-radius: 999px;
            color: rgba(255, 255, 255, .9);
            text-decoration: none;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .18);
            transition: background .18s ease;
        }

        .hms-find__area:hover,
        .hms-find__area.is-active { background: rgba(255, 255, 255, .26); color: #fff; }

        /* Clinic result cards --------------------------------------------- */
        .hms-clinic {
            position: relative;
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 1px solid var(--hms-line);
            border-radius: 20px;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .hms-clinic::before {
            content: "";
            display: block;
            height: 6px;
            background: var(--hms-clinic-accent);
        }

        .hms-clinic:hover {
            transform: translateY(-5px);
            border-color: rgba(79, 70, 229, .28);
            box-shadow: 0 24px 48px -26px rgba(15, 23, 42, .38);
        }

        .hms-clinic__head { display: flex; align-items: flex-start; gap: .8rem; padding: 1.1rem 1.25rem .9rem; }

        .hms-clinic__icon {
            flex: 0 0 auto;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 1.25rem;
            color: #fff;
            background: var(--hms-clinic-accent);
            box-shadow: 0 10px 20px -12px rgba(15, 23, 42, .6);
        }

        .hms-clinic__ident { flex: 1 1 auto; min-width: 0; }
        .hms-clinic__name { font-size: 1.02rem; font-weight: 700; letter-spacing: -.01em; line-height: 1.3; margin: 0; }
        .hms-clinic__area { font-size: .8rem; color: var(--hms-muted); margin: .2rem 0 0; }

        .hms-clinic__rating {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: .22rem;
            font-size: .82rem;
            font-weight: 700;
            color: #b45309;
            padding: .24rem .55rem;
            border-radius: 999px;
            background: #fffbeb;
            border: 1px solid #fde68a;
        }

        .hms-clinic__rating i { color: #f59e0b; }
        .hms-clinic__rating small { color: #a16207; font-weight: 600; }

        .hms-clinic__rating.is-empty {
            color: var(--hms-muted);
            background: var(--hms-primary-soft);
            border-color: rgba(79, 70, 229, .16);
        }

        .hms-clinic__rating.is-empty i { color: var(--hms-muted); }
        .hms-clinic__body { flex: 1 1 auto; padding: 0 1.25rem 1rem; }

        .hms-clinic__about {
            font-size: .855rem;
            color: #475569;
            line-height: 1.55;
            margin: 0 0 .9rem;
        }

        .hms-clinic__about.is-empty { color: var(--hms-muted); font-style: italic; }
        .hms-clinic__tags { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .95rem; }

        .hms-tag {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .72rem;
            font-weight: 700;
            padding: .26rem .6rem;
            border-radius: 999px;
            color: #334155;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }

        .hms-tag--gold { color: #b45309; background: #fffbeb; border-color: #fde68a; }
        .hms-tag--violet { color: var(--hms-primary-dark); background: var(--hms-primary-soft); border-color: rgba(79, 70, 229, .2); }
        .hms-clinic__contact { display: grid; gap: .38rem; margin: 0; }
        .hms-clinic__contact > div { display: flex; align-items: center; gap: .55rem; font-size: .8rem; min-width: 0; }
        .hms-clinic__contact dt { flex: 0 0 auto; color: var(--hms-primary); margin: 0; }
        .hms-clinic__contact dd { margin: 0; color: #475569; min-width: 0; }
        .hms-clinic__foot { padding: 0 1.25rem 1.25rem; margin-top: auto; }

        .hms-clinic__cta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            width: 100%;
            font-weight: 700;
            color: #fff;
            border-radius: 12px;
            background: var(--hms-clinic-accent);
            border: 0;
            padding: .6rem 1rem;
            transition: filter .18s ease;
        }

        .hms-clinic__cta:hover { color: #fff; filter: brightness(1.08); }
        .hms-clinic__cta:focus-visible { color: #fff; box-shadow: 0 0 0 .25rem rgba(79, 70, 229, .35); }
    </style>
@endpush
