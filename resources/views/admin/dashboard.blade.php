@extends('layouts.app')

@section('title', 'Administrator dashboard')
@section('subtitle', 'Network wide overview of clinics, doctors, patients and appointments')

@section('content')
    <div class="row g-3">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Total clinics" :value="$stats['clinics']" icon="bi-hospital" variant="primary"
                         :hint="$stats['approvedClinics'].' approved &middot; '.$stats['pendingClinics'].' pending'" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Total doctors" :value="$stats['doctors']" icon="bi-person-badge" variant="accent"
                         :hint="$stats['doctorsOnline'].' currently signed in'" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Total patients" :value="$stats['patients']" icon="bi-people" variant="violet" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Total appointments" :value="$stats['appointments']" icon="bi-calendar2-check"
                         variant="slate"
                         :hint="$stats['activeAppointments'].' active &middot; '.$stats['completedAppointments'].' completed'" />
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-sm-6 col-xl-3">
            <div class="hms-card h-100">
                <div class="hms-card__body">
                    <div class="text-muted small text-uppercase fw-semibold">Today's appointments</div>
                    <div class="fs-3 fw-bold">{{ $stats['todayAppointments'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="hms-card h-100">
                <div class="hms-card__body">
                    <div class="text-muted small text-uppercase fw-semibold">Active queries (7 days)</div>
                    <div class="fs-3 fw-bold">{{ $stats['activeQueries'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="hms-card h-100">
                <div class="hms-card__body">
                    <div class="text-muted small text-uppercase fw-semibold">Clinics awaiting review</div>
                    <div class="fs-3 fw-bold">{{ $stats['pendingClinics'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="hms-card h-100">
                <div class="hms-card__body">
                    <div class="text-muted small text-uppercase fw-semibold">Clinics rejected</div>
                    <div class="fs-3 fw-bold">{{ $stats['rejectedClinics'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        {{-- Pending clinic approvals --}}
        <div class="col-xl-6">
            <div class="hms-card h-100">
                <div class="hms-card__header">
                    <h5><i class="bi bi-hourglass-split me-2"></i>Clinics awaiting approval</h5>
                    <a href="{{ route('admin.clinics.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-hms">
                        Manage clinics
                    </a>
                </div>
                <div class="hms-card__body hms-card__body--flush">
                    @if ($pendingClinics->isEmpty())
                        <div class="hms-empty">
                            <i class="bi bi-check2-circle"></i>
                            <p class="mb-0 fw-semibold">No clinic is waiting for review.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table hms-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Clinic</th>
                                        <th>Area</th>
                                        <th class="text-end">Decision</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingClinics as $clinic)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $clinic->clinic_name }}</div>
                                                <div class="text-muted small">{{ $clinic->email }}</div>
                                            </td>
                                            <td>{{ $clinic->area }}</td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <form method="POST" action="{{ route('admin.clinics.approve', $clinic) }}">
                                                        @csrf
                                                        <button class="btn btn-sm btn-success" title="Approve clinic">
                                                            <i class="bi bi-check2"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.clinics.reject', $clinic) }}"
                                                          onsubmit="return confirm('Reject this clinic?')">
                                                        @csrf
                                                        <button class="btn btn-sm btn-danger" title="Reject clinic">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent queries --}}
        <div class="col-xl-6">
            <div class="hms-card h-100">
                <div class="hms-card__header">
                    <h5><i class="bi bi-envelope-paper me-2"></i>Latest contact queries</h5>
                    <a href="{{ route('admin.queries.index') }}" class="btn btn-sm btn-outline-hms">Open inbox</a>
                </div>
                <div class="hms-card__body hms-card__body--flush">
                    @if ($recentQueries->isEmpty())
                        <div class="hms-empty">
                            <i class="bi bi-inbox"></i>
                            <p class="mb-0 fw-semibold">No contact queries have been submitted yet.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($recentQueries as $query)
                                <li class="list-group-item d-flex gap-3 align-items-start">
                                    <span class="hms-avatar">{{ strtoupper(substr($query->user_name, 0, 2)) }}</span>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $query->user_name }}</div>
                                        <div class="small text-muted">{{ $query->subject }}</div>
                                        <div class="small text-muted">
                                            <i class="bi bi-clock me-1"></i>{{ $query->submitted_at?->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent appointments --}}
    <div class="hms-card mt-3">
        <div class="hms-card__header">
            <h5><i class="bi bi-calendar2-week me-2"></i>Most recent appointments</h5>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-outline-hms">View all</a>
        </div>
        <div class="hms-card__body hms-card__body--flush">
            @if ($recentAppointments->isEmpty())
                <div class="hms-empty">
                    <i class="bi bi-calendar-x"></i>
                    <p class="mb-0 fw-semibold">No appointments have been booked yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table hms-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Clinic</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Arrival</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentAppointments as $appointment)
                                <tr>
                                    <td>
                                        {{ $appointment->patient?->full_name ?? 'Removed patient' }}
                                        <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $appointment->contact_phone ?? $appointment->patient?->contact ?? '—' }}</div>
                                    </td>
                                    <td>{{ $appointment->doctor?->full_name ?? 'Removed doctor' }}</td>
                                    <td>{{ $appointment->clinic?->clinic_name ?? 'Removed clinic' }}</td>
                                    <td>{{ $appointment->appointment_date?->format('d M Y') }}</td>
                                    <td>{{ $appointment->formatted_time }}</td>
                                    <td><x-status-badge :status="$appointment->status" /></td>
                                    <td>
                                        @if ($appointment->is_checked_in)
                                            <span class="badge text-bg-success">Arrived</span>
                                        @elseif ($appointment->is_active)
                                            <span class="badge text-bg-warning">Not arrived</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
