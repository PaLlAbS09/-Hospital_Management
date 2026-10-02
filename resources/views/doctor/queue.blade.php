@extends('layouts.app')

@section('title', 'Appointment queue')
@section('subtitle', 'Mark appointments as completed or cancelled and record prescriptions')

@section('content')
    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="{{ route('doctor.queue', array_filter(['status' => $status, 'attendance' => $attendance ?? ''])) }}"
                   class="btn btn-sm {{ $date === today()->toDateString() ? 'btn-hms' : 'btn-outline-secondary' }}">
                    Today
                </a>
                <a href="{{ route('doctor.queue', array_merge(array_filter(['status' => $status, 'attendance' => $attendance ?? '']), ['date' => 'all'])) }}"
                   class="btn btn-sm {{ $date === 'all' ? 'btn-hms' : 'btn-outline-secondary' }}">
                    All dates
                </a>
                <span class="vr mx-1"></span>
                @foreach ($statuses as $key => $label)
                    <a href="{{ route('doctor.queue', array_filter(['date' => $date, 'status' => $key, 'attendance' => $attendance ?? ''])) }}"
                       class="btn btn-sm {{ $status === $key ? 'btn-hms' : 'btn-outline-secondary' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('doctor.queue') }}" class="row g-2">
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
                    <a href="{{ route('doctor.queue') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="hms-card">
        <div class="hms-card__header">
            <h5>
                <i class="bi bi-list-check me-2"></i>
                Queue ({{ $appointments->total() }})
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
                @include('doctor._queue_table')
            @endif
        </div>
        @if ($appointments->hasPages())
            <div class="hms-card__body border-top d-flex justify-content-center">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>

    @foreach ($appointments as $appointment)
        @include('doctor._prescription_modal', ['appointment' => $appointment])
    @endforeach
@endsection