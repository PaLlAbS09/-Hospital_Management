@extends('layouts.app')

@section('title', 'Clinic appointments')
@section('subtitle', $clinic->clinic_name . ' · daily appointment book for your clinic')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Total appointments" :value="$summary['total']" icon="bi-folder2-check" variant="primary" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Active" :value="$summary['active']" icon="bi-calendar2-check" variant="accent" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Completed" :value="$summary['completed']" icon="bi-check2-circle" variant="violet" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="On selected day" :value="$summary['upcoming']" icon="bi-calendar-day" variant="amber" />
        </div>
    </div>

    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="{{ route('clinic.appointments.index', array_filter(['status' => $status, 'attendance' => $attendance ?? ''])) }}"
                   class="btn btn-sm {{ $date === today()->toDateString() ? 'btn-hms' : 'btn-outline-secondary' }}">
                    Today
                </a>
                <a href="{{ route('clinic.appointments.index', array_merge(array_filter(['status' => $status, 'attendance' => $attendance ?? '']), ['date' => 'all'])) }}"
                   class="btn btn-sm {{ $date === 'all' ? 'btn-hms' : 'btn-outline-secondary' }}">
                    All dates
                </a>
                <span class="vr mx-1"></span>
                @foreach ($statuses as $key => $label)
                    <a href="{{ route('clinic.appointments.index', array_filter(['date' => $date, 'status' => $key, 'attendance' => $attendance ?? ''])) }}"
                       class="btn btn-sm {{ $status === $key ? 'btn-hms' : 'btn-outline-secondary' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('clinic.appointments.index') }}" class="row g-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="col-md-7">
                    <label class="visually-hidden" for="date">Appointment date</label>
                    <select class="form-select" id="date" name="date">
                        <option value="all" @selected($date === 'all')>All dates</option>
                        <option value="" @selected($date === today()->toDateString())>Today</option>
                        @if ($date !== 'all' && $date !== today()->toDateString())
                            <option value="{{ $date }}" selected>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="visually-hidden" for="attendance">Arrival</label>
                    <select id="attendance" name="attendance" class="form-select">
                        <option value="" @selected(($attendance ?? '') === '')>All arrivals</option>
                        <option value="arrived" @selected(($attendance ?? '') === 'arrived')>Arrived</option>
                        <option value="not_arrived" @selected(($attendance ?? '') === 'not_arrived')>Not arrived</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-hms flex-grow-1"><i class="bi bi-funnel me-1"></i>Apply</button>
                    <a href="{{ route('clinic.appointments.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="hms-card">
        <div class="hms-card__header">
            <h5>
                <i class="bi bi-calendar2-week me-2"></i>
                Appointments ({{ $appointments->total() }})
                @if ($date !== 'all')
                    · {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                @endif
            </h5>
        </div>
        <div class="hms-card__body hms-card__body--flush">
            @if ($appointments->isEmpty())
                <div class="hms-empty">
                    <i class="bi bi-calendar-x"></i>
                    <p class="mb-0 fw-semibold">No appointments match the current filters.</p>
                </div>
            @else
                @include('clinic.appointments._table')
            @endif
        </div>
        @if ($appointments->hasPages())
            <div class="hms-card__body border-top d-flex justify-content-center">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
@endsection