@extends('layouts.app')

@section('title', 'My appointments')
@section('subtitle', $patient->full_name . ' · booking history, upcoming visits and prescriptions')

@section('content')
    <div class="row g-3">
        <div class="col-6 col-xl-3">
            <x-stat-card label="Upcoming" :value="$stats['upcoming']" icon="bi-calendar2-heart" variant="primary" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Completed" :value="$stats['completed']" icon="bi-check2-circle" variant="accent" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Cancelled" :value="$stats['cancelled']" icon="bi-x-octagon" variant="rose" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Prescriptions" :value="$stats['prescriptions']" icon="bi-file-earmark-medical"
                         variant="violet" />
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-3">
        <span class="text-muted small">
            <i class="bi bi-hospital me-1"></i>{{ $stats['total'] }} bookings in total
        </span>
        <a href="{{ route('patient.clinics.index') }}" class="btn btn-hms btn-sm">
            <i class="bi bi-search me-1"></i>Book a new appointment
        </a>
    </div>

    @if ($pendingReviews->isNotEmpty())
        <div class="hms-card mb-3" style="border-color: rgba(245, 158, 11, .4)">
            <div class="hms-card__header">
                <h5>
                    <i class="bi bi-star-fill text-warning me-2"></i>
                    Rate your recent visit{{ $pendingReviews->count() === 1 ? '' : 's' }}
                </h5>
                <span class="hms-chip">{{ $pendingReviews->count() }} pending</span>
            </div>
            <div class="hms-card__body hms-card__body--flush">
                <ul class="list-group list-group-flush">
                    @foreach ($pendingReviews as $appointment)
                        <li class="list-group-item d-flex flex-wrap align-items-center gap-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Dr. {{ $appointment->doctor?->full_name ?? '—' }}</div>
                                <div class="small text-muted">
                                    {{ $appointment->appointment_date?->format('d M Y') }}
                                    · {{ $appointment->formatted_time }}
                                    · {{ $appointment->clinic?->clinic_name ?? '—' }}
                                </div>
                            </div>
                            <a href="{{ route('patient.appointments.review.create', $appointment) }}"
                               class="btn btn-sm btn-hms">
                                <i class="bi bi-star me-1"></i>Rate &amp; share experience
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if ($upcoming->isNotEmpty())
        <div class="hms-card mb-3">
            <div class="hms-card__header">
                <h5><i class="bi bi-calendar2-heart me-2"></i>Upcoming visits</h5>
            </div>
            <div class="hms-card__body hms-card__body--flush">
                <ul class="list-group list-group-flush">
                    @foreach ($upcoming as $appointment)
                        <li class="list-group-item d-flex flex-wrap align-items-center gap-3">
                            <span class="hms-avatar" style="border-radius: 12px">
                                {{ $appointment->appointment_date?->format('d') }}
                            </span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">
                                    Dr. {{ $appointment->doctor?->full_name ?? '—' }}
                                    <x-status-badge :status="$appointment->status" class="ms-1" />
                                </div>
                                <div class="small text-muted">
                                    {{ $appointment->appointment_date?->format('l, d F Y') }}
                                    · {{ $appointment->formatted_time }}
                                    · {{ $appointment->clinic?->clinic_name ?? '—' }},
                                    {{ $appointment->clinic?->area ?? '—' }}
                                </div>
                            </div>
                            <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}"
                                  onsubmit="return confirm('Cancel this appointment?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-calendar-x me-1"></i>Cancel
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @include('patient._history_table')
@endsection