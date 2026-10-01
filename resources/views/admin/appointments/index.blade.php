@extends('layouts.app')

@section('title', 'Appointments & prescriptions')
@section('subtitle', 'Network-wide overview of every scheduled, completed and cancelled appointment')

@section('content')
    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="{{ route('admin.appointments.index', array_filter(['date' => $date, 'search' => $search])) }}"
                   class="btn btn-sm {{ $status === '' ? 'btn-hms' : 'btn-outline-secondary' }}">
                    All statuses
                </a>
                @foreach ($statuses as $key => $label)
                    <a href="{{ route('admin.appointments.index', array_filter(['status' => $key, 'date' => $date, 'search' => $search])) }}"
                       class="btn btn-sm {{ $status === $key ? 'btn-hms' : 'btn-outline-secondary' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.appointments.index') }}" class="row g-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="col-md-5">
                    <label class="visually-hidden" for="search">Search</label>
                    <input type="search" id="search" name="search" value="{{ $search }}" class="form-control"
                           placeholder="Search by patient, doctor, clinic or disease">
                </div>
                <div class="col-md-3">
                    <label class="visually-hidden" for="date">Date</label>
                    <input type="date" id="date" name="date" value="{{ $date }}" class="form-control">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-hms flex-grow-1"><i class="bi bi-search me-1"></i>Filter</button>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="hms-card">
        <div class="hms-card__header">
            <h5><i class="bi bi-calendar2-check me-2"></i>Appointments ({{ $appointments->total() }})</h5>
        </div>
        <div class="hms-card__body hms-card__body--flush">
            @if ($appointments->isEmpty())
                <div class="hms-empty">
                    <i class="bi bi-calendar-x"></i>
                    <p class="mb-0 fw-semibold">No appointments match the current filters.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table hms-table">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Clinic</th>
                                <th>Prescription</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td class="text-nowrap">
                                        <div class="fw-semibold">{{ $appointment->appointment_date?->format('d M Y') }}</div>
                                        <div class="text-muted small">{{ $appointment->formatted_time }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $appointment->patient?->full_name ?? 'Removed patient' }}</div>
                                        <div class="text-muted small">{{ $appointment->patient?->contact }}</div>
                                    </td>
                                    <td>{{ $appointment->doctor?->full_name ?? 'Removed doctor' }}</td>
                                    <td>{{ $appointment->clinic?->clinic_name ?? 'Removed clinic' }}</td>
                                    <td>
                                        @if ($appointment->has_prescription)
                                            <button type="button" class="btn btn-sm btn-outline-hms" data-bs-toggle="modal"
                                                    data-bs-target="#rx-{{ $appointment->appointment_id }}">
                                                <i class="bi bi-file-earmark-medical me-1"></i>View
                                            </button>
                                        @else
                                            <span class="text-muted small">Not recorded</span>
                                        @endif
                                    </td>
                                    <td><x-status-badge :status="$appointment->status" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($appointments->hasPages())
            <div class="hms-card__body border-top d-flex justify-content-center">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>

    @foreach ($appointments as $appointment)
        @if ($appointment->has_prescription)
            @include('admin.appointments._prescription_modal', ['appointment' => $appointment])
        @endif
    @endforeach
@endsection