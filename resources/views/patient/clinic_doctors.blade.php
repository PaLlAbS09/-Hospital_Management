@extends('layouts.app')

@section('title', 'Book an appointment')
@section('subtitle', $clinic->clinic_name . ' · ' . $clinic->area . ' · pick a doctor, date and time slot')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <a href="{{ route('patient.clinics.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to clinic search
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="hms-chip"><i class="bi bi-hospital"></i>{{ $clinic->clinic_name }}</span>
            <span class="hms-chip"><i class="bi bi-geo-alt"></i>{{ $clinic->area }}</span>
        </div>
    </div>

    {{-- Date picker ------------------------------------------------------- --}}
    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h1 class="h5 mb-0"><i class="bi bi-calendar3 me-2"></i>Choose a date</h1>
                <form method="GET" action="{{ route('patient.clinics.doctors', $clinic) }}" class="d-flex gap-2">
                    <label class="visually-hidden" for="date">Appointment date</label>
                    <input type="date" id="date" name="date" value="{{ $date }}" min="{{ today()->toDateString() }}"
                           class="form-control form-control-sm">
                    <button class="btn btn-sm btn-hms">Go</button>
                </form>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @forelse ($availableDates as $availableDate)
                    <a href="{{ route('patient.clinics.doctors', ['clinic' => $clinic, 'date' => $availableDate]) }}"
                       class="btn btn-sm {{ $availableDate === $date ? 'btn-hms' : 'btn-outline-secondary' }}">
                        {{ \Carbon\Carbon::parse($availableDate)->format('D, d M') }}
                    </a>
                @empty
                    <span class="text-muted small">No future schedules published yet — check back soon.</span>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Existing bookings for the selected date --------------------------- --}}
    @if ($myAppointments->isNotEmpty())
        <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
            <i class="bi bi-check-circle-fill fs-5 lh-1"></i>
            <div>
                <strong class="d-block">You already booked on this date:</strong>
                @foreach ($myAppointments as $booked)
                    <span class="small">
                        Dr. {{ $booked->doctor?->full_name }} at {{ $booked->formatted_time }}
                        (<x-status-badge :status="$booked->status" />)
                    </span>
                    @unless ($loop->last) &middot; @endunless
                @endforeach
            </div>
        </div>
    @endif

    {{-- Doctors & slots --------------------------------------------------- --}}
    <div class="row g-3">
        @forelse ($offers as $offer)
            @include('patient._offer_card', ['schedule' => $offer['schedule'], 'slots' => $offer['slots']])
        @empty
            <div class="col-12">
                <div class="hms-card">
                    <div class="hms-empty">
                        <i class="bi bi-calendar-x"></i>
                        <p class="fw-semibold mb-1">No doctor schedules are published for this date.</p>
                        <p class="mb-3 small">Pick another date above or check back later.</p>
                        <a href="{{ route('patient.clinics.index') }}" class="btn btn-sm btn-outline-hms">
                            Try another clinic
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection