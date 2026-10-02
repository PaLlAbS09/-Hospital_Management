@extends('layouts.app')

@section('title', 'Patient history')
@section('subtitle', $patient->full_name . ' · appointment and prescription record')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <a href="{{ route('admin.patients.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to directory
        </a>
        <span class="text-muted small">
            <i class="bi bi-calendar-plus me-1"></i>Registered {{ $patient->created_at?->format('d M Y') ?? '—' }}
        </span>
    </div>

    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="hms-avatar" style="width:56px;height:56px;font-size:1.2rem">{{ $patient->initials }}</span>
                <div class="flex-grow-1">
                    <h1 class="h5 mb-1">{{ $patient->full_name }}</h1>
                    <div class="text-muted small">
                        <span class="me-3"><i class="bi bi-gender-ambiguous me-1"></i>{{ $patient->gender }}</span>
                        <span class="me-3"><i class="bi bi-envelope me-1"></i>{{ $patient->email }}</span>
                        <span><i class="bi bi-telephone me-1"></i>{{ $patient->contact }}</span>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-6 col-xl-3">
                    <div class="hms-card h-100">
                        <div class="hms-card__body py-3 text-center">
                            <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                            <div class="text-muted small text-uppercase fw-semibold">Total</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="hms-card h-100">
                        <div class="hms-card__body py-3 text-center">
                            <div class="fs-4 fw-bold text-primary">{{ $stats['active'] }}</div>
                            <div class="text-muted small text-uppercase fw-semibold">Active</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="hms-card h-100">
                        <div class="hms-card__body py-3 text-center">
                            <div class="fs-4 fw-bold text-success">{{ $stats['completed'] }}</div>
                            <div class="text-muted small text-uppercase fw-semibold">Completed</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="hms-card h-100">
                        <div class="hms-card__body py-3 text-center">
                            <div class="fs-4 fw-bold text-secondary">{{ $stats['cancelled'] }}</div>
                            <div class="text-muted small text-uppercase fw-semibold">Cancelled</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="hms-card">
        <div class="hms-card__header">
            <h5><i class="bi bi-calendar2-week me-2"></i>Appointment history ({{ $appointments->count() }})</h5>
        </div>
        <div class="hms-card__body hms-card__body--flush">
            @if ($appointments->isEmpty())
                <div class="hms-empty">
                    <i class="bi bi-calendar-x"></i>
                    <p class="mb-0 fw-semibold">This patient has no appointments on record.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table hms-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Doctor</th>
                                <th>Clinic</th>
                                <th>Diagnosis</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td class="text-nowrap">{{ $appointment->appointment_date?->format('d M Y') }}</td>
                                    <td class="text-nowrap">{{ $appointment->formatted_time }}</td>
                                    <td>{{ $appointment->doctor?->full_name ?? '—' }}</td>
                                    <td>{{ $appointment->clinic?->clinic_name ?? '—' }}</td>
                                    <td>
                                        @if ($appointment->has_prescription)
                                            <span class="hms-chip"><i class="bi bi-file-earmark-medical"></i>Prescribed</span>
                                            <div class="small text-muted mt-1">{{ $appointment->disease ?: 'Diagnosis recorded' }}</div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-status-badge :status="$appointment->status" />
                                        @if ($appointment->is_checked_in)
                                            <div><small class="text-success">Arrived {{ $appointment->checked_in_at?->format('d M H:i') }}</small></div>
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