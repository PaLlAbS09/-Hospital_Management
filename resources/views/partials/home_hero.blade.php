{{--
    Landing page hero.

    Premium rebuild of the original "Book a doctor near you in three simple
    steps." block. The colour palette is intentionally unchanged - only the
    markup, layering and spacing were upgraded.

    Expects: $area, $areas, $totalClinics, $totalDoctors, $totalPatients, $totalAppointments
--}}
<section class="container mt-5 px-0">
    <div class="hms-hero">
        <div class="row g-4 g-lg-5 align-items-center">

            {{-- Copy + search ------------------------------------------------- --}}
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="hms-hero__badge"><i class="bi bi-hospital-fill"></i></span>
                    <div>
                        <span class="hms-chip mb-1">
                            <i class="bi bi-geo-alt-fill"></i>Area-wise clinic discovery
                        </span>
                        <div class="hms-hero__trust">
                            <i class="bi bi-patch-check-fill"></i>
                            Only administrator-approved clinics are listed
                        </div>
                    </div>
                </div>

                <h1 class="display-6 mb-3">Book a doctor near you in three simple steps.</h1>

                <p class="lead mb-4">
                    Search approved clinics by area, review the doctors on duty and reserve a time slot
                    from their published schedule. No phone calls required.
                </p>

                <form action="{{ route('home') }}" method="GET" class="row g-2 align-items-center" style="max-width: 640px;">
                    <div class="col-sm-7">
                        <label class="visually-hidden" for="area">Area</label>
                        <input type="text"
                               id="area"
                               name="area"
                               value="{{ $area }}"
                               list="area-options"
                               class="form-control form-control-lg"
                               placeholder="Enter your area, e.g. Bardhaman">
                        <datalist id="area-options">
                            @foreach ($areas as $option)
                                <option value="{{ $option }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-sm-5 d-grid">
                        <button type="submit" class="btn btn-light btn-lg fw-semibold">
                            <i class="bi bi-search me-1"></i>Find clinics
                        </button>
                    </div>
                </form>

                @if ($areas->isNotEmpty())
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        @foreach ($areas->take(6) as $option)
                            <a href="{{ route('home', ['area' => $option]) }}"
                               class="badge rounded-pill text-bg-light text-decoration-none px-3 py-2">
                                <i class="bi bi-geo me-1"></i>{{ $option }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Stats panel ---------------------------------------------------- --}}
            <div class="col-lg-5">
                <div class="hms-hero__panel">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="fw-bold">Network at a glance</div>
                            <div class="small" style="opacity:.75">Live numbers across every approved centre</div>
                        </div>
                        <i class="bi bi-activity fs-4" style="opacity:.6"></i>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="hms-hero__stat">
                                <strong>{{ $totalClinics }}</strong>
                                <span>Approved clinics</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hms-hero__stat">
                                <strong>{{ $totalDoctors }}</strong>
                                <span>Registered doctors</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hms-hero__stat">
                                <strong>{{ $totalPatients }}</strong>
                                <span>Patients served</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hms-hero__stat">
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