@extends('layouts.app')

@section('title', 'Clinic dashboard')
@section('subtitle', $clinic->clinic_name . ' · ' . $clinic->area . ' · local appointments and schedules')

@section('content')
    <div class="row g-3">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Today's appointments" :value="$stats['today']" icon="bi-calendar2-check"
                         variant="primary" :hint="$stats['todayRemaining'].' remaining · '.$stats['todayCompleted'].' done'" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Upcoming schedules" :value="$stats['upcomingSchedules']" icon="bi-calendar3"
                         variant="accent" :hint="$stats['assignedDoctors'].' doctors assigned'" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Total appointments" :value="$stats['totalAppointments']" icon="bi-folder2-check"
                         variant="violet" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Capacity today" :value="$stats['capacityToday']" icon="bi-people"
                         variant="amber" :hint="$stats['doctorsOnline'].' doctors online'" />
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-xl-6">
            <div class="hms-card h-100">
                <div class="hms-card__header">
                    <h5><i class="bi bi-calendar2-week me-2"></i>Today's appointments</h5>
                    <a href="{{ route('clinic.appointments.index', ['date' => $today]) }}" class="btn btn-sm btn-outline-hms">
                        View all
                    </a>
                </div>
                <div class="hms-card__body hms-card__body--flush">
                    @if ($todayAppointments->isEmpty())
                        <div class="hms-empty">
                            <i class="bi bi-calendar-x"></i>
                            <p class="mb-0 fw-semibold">No appointments booked for {{ \Carbon\Carbon::parse($today)->format('d M Y') }}.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table hms-table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($todayAppointments as $appointment)
                                        <tr>
                                            <td class="fw-semibold text-nowrap">{{ $appointment->formatted_time }}</td>
                                            <td>
                                                {{ $appointment->patient?->full_name ?? '—' }}
                                                <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $appointment->contact_phone ?? $appointment->patient?->contact ?? '—' }}</div>
                                            </td>
                                            <td>{{ $appointment->doctor?->full_name ?? '—' }}</td>
                                            <td>
                                                <x-status-badge :status="$appointment->status" />
                                                <div>
                                                    @if ($appointment->is_checked_in)
                                                        <small class="text-success">Arrived</small>
                                                    @elseif ($appointment->is_active)
                                                        <small class="text-warning">Not arrived</small>
                                                    @endif
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

        <div class="col-xl-6">
            <div class="hms-card h-100">
                <div class="hms-card__header">
                    <h5><i class="bi bi-calendar3 me-2"></i>Upcoming doctor schedules</h5>
                    <a href="{{ route('clinic.schedules.index') }}" class="btn btn-sm btn-outline-hms">
                        Manage schedules
                    </a>
                </div>
                <div class="hms-card__body hms-card__body--flush">
                    @if ($upcomingSchedules->isEmpty())
                        <div class="hms-empty">
                            <i class="bi bi-calendar-plus"></i>
                            <p class="mb-1 fw-semibold">No schedules published yet.</p>
                            <a href="{{ route('clinic.schedules.index') }}" class="btn btn-sm btn-hms">Publish a schedule</a>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($upcomingSchedules as $schedule)
                                <li class="list-group-item d-flex align-items-center gap-3">
                                    <div class="hms-avatar" style="border-radius: 12px">
                                        {{ $schedule->schedule_date?->format('d') }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">Dr. {{ $schedule->doctor?->full_name ?? 'Removed doctor' }}</div>
                                        <div class="small text-muted">
                                            {{ $schedule->schedule_date?->format('D, d M Y') }} · {{ $schedule->time_range }}
                                        </div>
                                    </div>
                                    <span class="hms-chip">
                                        <i class="bi bi-people"></i>{{ $schedule->bookedCount() }}/{{ $schedule->patient_capacity }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection