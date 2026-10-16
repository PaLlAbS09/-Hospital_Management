{{--
    Landing page hero - Light clean medical theme (white + teal).

    Expects: $area, $areas, $totalClinics, $totalDoctors, $totalPatients, $totalAppointments
--}}
@php
    // Auto-detect hero background from public/assets folder.
    // Supports: public/assets/*.{jpg,jpeg,png,webp} OR public/assets/images/*.{...}
    // Works with ANY filename you drop there. Prefers file with "hero" in name.
    $heroBg = null;
    $candidates = array_merge(
        glob(public_path('assets/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}'), GLOB_BRACE) ?: [],
        glob(public_path('assets/images/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}'), GLOB_BRACE) ?: []
    );
    // Remove non-files (README, .gitkeep etc. already excluded by extension filter)
    $candidates = array_filter($candidates, 'is_file');
    if (!empty($candidates)) {
        $heroFile = collect($candidates)->sortBy(function ($path) {
            return str_contains(strtolower(basename($path)), 'hero') ? 0 : 1;
        })->first();
        // Build web path relative to public/
        $heroBg = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $heroFile);
        $heroBg = str_replace(DIRECTORY_SEPARATOR, '/', $heroBg);
    } elseif (config('assets.hero_bg')) {
        // Fallback to config/assets.php value
        $heroBg = config('assets.hero_bg');
    }
@endphp
<section class="container mt-4">
    <div class="hms-hero hms-hero--light {{ $heroBg ? 'hms-hero--bg' : './assets/images/hero-bg.jpg' }}">
        @if($heroBg)
            {{-- Background image via <img> tag from public/assets --}}
            <img src="{{ asset($heroBg) }}" alt="" aria-hidden="true" class="hms-hero__bg-img">
            <div class="hms-hero__bg-overlay" aria-hidden="true"></div>
        @endif
        <div class="row g-4 g-lg-5 align-items-center">

            {{-- Copy + search ------------------------------------------------- --}}
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="hms-hero__badge"><i class="bi bi-heart-pulse-fill"></i></span>
                    <div>
                        <span class="hms-hero__eyebrow">
                            <i class="bi bi-geo-alt-fill"></i>Area-wise clinic discovery
                        </span>
                        <div class="hms-hero__trust">
                            <i class="bi bi-patch-check-fill"></i>
                            Only administrator-approved clinics are listed
                        </div>
                    </div>
                </div>

                <h1 class="mb-3">Book a doctor near you in <span class="hms-hero__highlight">three simple steps.</span></h1>

                <p class="hms-hero__lead mb-4">
                    Search approved clinics by area, review the doctors on duty and reserve a time slot
                    from their published schedule. No phone calls required.
                </p>

                <form action="{{ route('home') }}" method="GET" class="hms-hero__search">
                    <div class="hms-hero__search-field">
                        <i class="bi bi-geo-alt"></i>
                        <label class="visually-hidden" for="area">Area</label>
                        <input type="text"
                               id="area"
                               name="area"
                               value="{{ $area }}"
                               list="area-options"
                               class="form-control form-control-lg border-0 bg-transparent"
                               placeholder="Enter your area, e.g. Bardhaman">
                        <datalist id="area-options">
                            @foreach ($areas as $option)
                                <option value="{{ $option }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <button type="submit" class="btn btn-hms btn-lg hms-hero__search-btn">
                        <i class="bi bi-search me-1"></i>Find clinics
                    </button>
                </form>

                {{-- Three simple steps ---------------------------------------- --}}
                <div class="mt-4 hms-hero__steps">
                    <div class="hms-hero__step">
                        <span class="hms-hero__step-num">1</span>
                        <span><strong>Search</strong><small>approved clinics</small></span>
                    </div>
                    <span class="hms-hero__step-arrow"><i class="bi bi-chevron-right"></i></span>
                    <div class="hms-hero__step">
                        <span class="hms-hero__step-num">2</span>
                        <span><strong>Pick</strong><small>doctor &amp; slot</small></span>
                    </div>
                    <span class="hms-hero__step-arrow"><i class="bi bi-chevron-right"></i></span>
                    <div class="hms-hero__step">
                        <span class="hms-hero__step-num">3</span>
                        <span><strong>Reserve</strong><small>in seconds</small></span>
                    </div>
                </div>
            </div>

            {{-- Stats panel ---------------------------------------------------- --}}
            <div class="col-lg-5">
                <div class="hms-hero__panel">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="fw-bold text-dark">Network at a glance</div>
                            <div class="small text-muted">Live numbers across every approved centre</div>
                        </div>
                        <span class="hms-hero__live"><i class="bi bi-activity"></i> Live</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="hms-hero__stat">
                                <i class="bi bi-hospital"></i>
                                <strong>{{ $totalClinics }}</strong>
                                <span>Approved clinics</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hms-hero__stat">
                                <i class="bi bi-person-badge"></i>
                                <strong>{{ $totalDoctors }}</strong>
                                <span>Registered doctors</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hms-hero__stat">
                                <i class="bi bi-people"></i>
                                <strong>{{ $totalPatients }}</strong>
                                <span>Patients served</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hms-hero__stat hms-hero__stat--accent">
                                <i class="bi bi-calendar2-check"></i>
                                <strong>{{ $totalAppointments }}</strong>
                                <span>Appointments booked</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>