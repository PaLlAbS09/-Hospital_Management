@extends('layouts.app')

@section('title', 'Doctor dashboard')
@section('subtitle', 'Dr. ' . $doctor->full_name . ' · ' . $doctor->specialization . ' · ' . \Carbon\Carbon::parse($today)->format('l, d M Y'))

@section('content')
    <div class="row g-3">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Today's queue" :value="$stats['todayTotal']" icon="bi-people" variant="primary"
                         :hint="$stats['todayPending'].' pending · '.$stats['todayCompleted'].' completed'" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Total appointments" :value="$stats['totalAppointments']" icon="bi-calendar2-check"
                         variant="accent" :hint="$stats['completed'].' completed all-time'" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Prescriptions" :value="$stats['prescriptions']" icon="bi-file-earmark-medical"
                         variant="violet" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Upcoming sessions" :value="$stats['clinics']" icon="bi-hospital" variant="amber"
                         hint="Scheduled clinic dates ahead" />
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-3">
        <div class="small text-muted">
            @if ($sessionLog)
                <i class="bi bi-person-check-fill text-success me-1"></i>
                Signed in since {{ $sessionLog->login_time?->format('H:i') }}
                · session {{ $sessionLog->duration_for_humans }}
            @else
                <i class="bi bi-person me-1"></i>No active session record
            @endif
        </div>
        <a href="{{ route('doctor.queue') }}" class="btn btn-hms btn-sm">
            <i class="bi bi-list-check me-1"></i>Open appointment queue
        </a>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="hms-card h-100">
                <div class="hms-card__header">
                    <h5><i class="bi bi-people me-2"></i>Today's queue</h5>
                </div>
                <div class="hms-card__body hms-card__body--flush">
                    @if ($todayQueue->isEmpty())
                        <div class="hms-empty">
                            <i class="bi bi-calendar-x"></i>
                            <p class="mb-0 fw-semibold">No patients scheduled for today.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($todayQueue as $appointment)
                                <li class="list-group-item d-flex align-items-center gap-3">
                                    <div class="text-center text-nowrap" style="min-width: 64px">
                                        <div class="fw-bold text-primary">{{ $appointment->formatted_time }}</div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $appointment->patient?->full_name ?? '—' }}</div>
                                        <div class="small text-muted">
                                            {{ $appointment->clinic?->clinic_name ?? '—' }}
                                            @if ($appointment->disease)
                                                · {{ $appointment->disease }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <x-status-badge :status="$appointment->status" />
                                        <div>
                                            @if ($appointment->is_checked_in)
                                                <small class="text-success">Arrived</small>
                                            @elseif ($appointment->is_active)
                                                <small class="text-warning">Not arrived</small>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="hms-card h-100">
                <div class="hms-card__header">
                    <h5><i class="bi bi-calendar2-heart me-2"></i>Upcoming visits</h5>
                </div>
                <div class="hms-card__body hms-card__body--flush">
                    @if ($upcoming->isEmpty())
                        <div class="hms-empty">
                            <i class="bi bi-calendar-plus"></i>
                            <p class="mb-0 fw-semibold">No upcoming patient visits scheduled.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($upcoming as $appointment)
                                <li class="list-group-item d-flex align-items-center gap-3">
                                    <span class="hms-avatar" style="border-radius: 12px">
                                        {{ $appointment->appointment_date?->format('d') }}
                                    </span>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $appointment->patient?->full_name ?? '—' }}</div>
                                        <div class="small text-muted">
                                            {{ $appointment->appointment_date?->format('D, d M') }}
                                            · {{ $appointment->formatted_time }}
                                            · {{ $appointment->clinic?->clinic_name ?? '—' }}
                                        </div>
                                    </div>
                                    <x-status-badge :status="$appointment->status" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection