@extends('layouts.app')

@section('title', 'Schedule management')
@section('subtitle', 'Assign doctors to dates, set working hours and patient capacity')

@section('content')
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('clinic.schedules.index', ['scope' => 'upcoming']) }}"
           class="btn btn-sm {{ $scope !== 'past' ? 'btn-hms' : 'btn-outline-secondary' }}">
            <i class="bi bi-arrow-up-right me-1"></i>Upcoming
        </a>
        <a href="{{ route('clinic.schedules.index', ['scope' => 'past']) }}"
           class="btn btn-sm {{ $scope === 'past' ? 'btn-hms' : 'btn-outline-secondary' }}">
            <i class="bi bi-arrow-down-left me-1"></i>Past
        </a>
        <button class="btn btn-sm btn-hms ms-auto" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
            <i class="bi bi-plus-lg me-1"></i>New schedule
        </button>
    </div>

    <div class="hms-card">
        <div class="hms-card__header">
            <h5><i class="bi bi-calendar3 me-2"></i>{{ $scope === 'past' ? 'Past' : 'Upcoming' }} schedules ({{ $schedules->count() }})</h5>
        </div>
        <div class="hms-card__body hms-card__body--flush">
            @if ($schedules->isEmpty())
                <div class="hms-empty">
                    <i class="bi bi-calendar-x"></i>
                    <p class="mb-1 fw-semibold">No {{ $scope === 'past' ? 'past' : 'upcoming' }} schedules found.</p>
                    <p class="mb-3 small">Schedules define when a doctor is available for bookings at your clinic.</p>
                    <button class="btn btn-sm btn-hms" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                        <i class="bi bi-plus-lg me-1"></i>Create the first schedule
                    </button>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table hms-table">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Hours</th>
                                <th class="text-center">Booked / Capacity</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedules as $schedule)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="hms-avatar">{{ $schedule->doctor?->initials ?? 'DR' }}</span>
                                            <div>
                                                <div class="fw-semibold">Dr. {{ $schedule->doctor?->full_name ?? 'Removed doctor' }}</div>
                                                <div class="text-muted small">{{ $schedule->doctor?->specialization }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">{{ $schedule->schedule_date?->format('D, d M Y') }}</td>
                                    <td class="text-nowrap">{{ $schedule->time_range }}</td>
                                    <td class="text-center">
                                        <span class="hms-chip">{{ $schedule->bookedCount() }}/{{ $schedule->patient_capacity }}</span>
                                    </td>
                                    <td>
                                        @if ($schedule->is_past)
                                            <span class="badge text-bg-secondary">Completed</span>
                                        @elseif ($schedule->is_full)
                                            <span class="badge text-bg-danger">Full</span>
                                        @else
                                            <span class="badge text-bg-success">Open</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('clinic.schedules.destroy', $schedule) }}"
                                              onsubmit="return confirm('Remove this schedule?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash3 me-1"></i>Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @include('clinic.schedules._form_modal')
@endsection