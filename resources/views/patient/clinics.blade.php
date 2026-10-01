@extends('layouts.app')

@section('title', 'Find a clinic')
@section('subtitle', 'Search approved clinics by area, then pick a doctor and time slot')

@section('content')
    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-8">
                    <h1 class="h5 mb-1">Search clinics in your area</h1>
                    <p class="text-muted small mb-3">
                        Only clinics approved by an administrator can accept bookings.
                    </p>
                    <form method="GET" action="{{ route('patient.clinics.index') }}" class="row g-2">
                        <div class="col-sm-8">
                            <label class="visually-hidden" for="area">Area</label>
                            <input type="text"
                                   class="form-control"
                                   id="area"
                                   name="area"
                                   value="{{ $area }}"
                                   list="area-options"
                                   placeholder="Enter your area, e.g. Bardhaman">
                            <datalist id="area-options">
                                @foreach ($areas as $option)
                                    <option value="{{ $option }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-sm-4 d-flex gap-2">
                            <button type="submit" class="btn btn-hms flex-grow-1">
                                <i class="bi bi-search me-1"></i>Search
                            </button>
                            @if ($area)
                                <a href="{{ route('patient.clinics.index') }}" class="btn btn-outline-secondary">Reset</a>
                            @endif
                        </div>
                    </form>

                    @if ($areas->isNotEmpty())
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @foreach ($areas->take(8) as $option)
                                <a href="{{ route('patient.clinics.index', ['area' => $option]) }}"
                                   class="badge rounded-pill text-bg-light text-decoration-none px-3 py-2 border">
                                    <i class="bi bi-geo me-1"></i>{{ $option }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="col-lg-4 text-lg-end">
                    <span class="hms-chip"><i class="bi bi-hospital"></i>{{ $clinics->total() }} clinics found</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        @forelse ($clinics as $clinic)
            <div class="col-md-6 col-xl-4">
                <div class="hms-card h-100">
                    <div class="hms-card__body d-flex flex-column h-100">
                        <div class="d-flex align-items-start gap-3 mb-2">
                            <span class="hms-avatar"><i class="bi bi-hospital"></i></span>
                            <div class="flex-grow-1">
                                <h2 class="h6 mb-1">{{ $clinic->clinic_name }}</h2>
                                <div class="text-muted small">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $clinic->area }}
                                </div>
                            </div>
                        </div>

                        <ul class="list-unstyled small text-muted mb-3">
                            <li class="mb-1"><i class="bi bi-telephone me-2"></i>{{ $clinic->contact_number }}</li>
                            <li class="mb-1 text-truncate"><i class="bi bi-envelope me-2"></i>{{ $clinic->email }}</li>
                            <li><i class="bi bi-calendar3 me-2"></i>{{ $clinic->schedules_count }} published schedules</li>
                        </ul>

                        <div class="mt-auto d-grid">
                            <a href="{{ route('patient.clinics.doctors', $clinic) }}" class="btn btn-sm btn-hms">
                                <i class="bi bi-person-badge me-1"></i>See doctors &amp; slots
                            </a>
                        </div>
                    </div>
                </div>
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