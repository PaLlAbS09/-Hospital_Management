@extends('layouts.app')

@section('title', 'Book an appointment')
@section('subtitle', $clinic->clinic_name . ' · ' . $clinic->area . ' · pick a doctor, date and time slot')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <a href="{{ route('patient.clinics.show', $clinic) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to clinic profile
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="badge text-bg-success">1. Clinic</span>
            <span class="badge text-bg-success">2. Why choose us</span>
            <span class="badge text-bg-primary">3. Book a slot</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="hms-chip"><i class="bi bi-hospital"></i>{{ $clinic->clinic_name }}</span>
            <span class="hms-chip"><i class="bi bi-geo-alt"></i>{{ $clinic->area }}</span>
        </div>
    </div>

    {{-- Date picker ------------------------------------------------------- --}}
    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h1 class="h5 mb-0">
                    <i class="bi bi-calendar3 me-2"></i>Choose a date
                    <span class="badge text-bg-primary ms-2" id="active-date-chip" data-active-date="{{ $date }}">
                        {{ \Carbon\Carbon::parse($date)->format('D, d M') }}
                    </span>
                </h1>
                <form method="GET" action="{{ route('patient.clinics.doctors', $clinic) }}" class="d-flex gap-2"
                      id="booking-date-form" data-availability-url="{{ route('patient.clinics.availability', $clinic) }}">
                    <label class="visually-hidden" for="date">Appointment date</label>
                    <input type="date" id="date" name="date" value="{{ $date }}" min="{{ today()->toDateString() }}"
                           class="form-control form-control-sm" data-date-input>
                    <button class="btn btn-sm btn-hms" type="submit">Go</button>
                </form>
            </div>

            <div class="d-flex flex-wrap gap-2" id="booking-date-chips">
                @forelse ($availableDates as $availableDate)
                    <a href="{{ route('patient.clinics.doctors', ['clinic' => $clinic, 'date' => $availableDate]) }}"
                       data-date-chip="{{ $availableDate }}"
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
    <div id="booking-notice-wrap">
        @include('patient._booking_notice', ['myAppointments' => $myAppointments])
    </div>

    {{-- Doctors & slots --------------------------------------------------- --}}
    <div id="booking-offers-wrap">
        @include('patient._offers_list', ['clinic' => $clinic, 'date' => $date, 'offers' => $offers])
    </div>

    <div id="booking-loading" class="text-center text-muted small py-3 d-none" role="status" aria-live="polite">
        <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Loading availability…
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('booking-date-form');
    if (!form) {
        return;
    }

    const dateInput = form.querySelector('[data-date-input]');
    const chipsWrap = document.getElementById('booking-date-chips');
    const offersWrap = document.getElementById('booking-offers-wrap');
    const noticeWrap = document.getElementById('booking-notice-wrap');
    const activeChip = document.getElementById('active-date-chip');
    const loading = document.getElementById('booking-loading');
    const availabilityUrl = form.dataset.availabilityUrl;
    const minDate = dateInput ? dateInput.getAttribute('min') : '';
    let requestId = 0;

    const formatChip = (value) => {
        const parsed = new Date(value + 'T00:00:00');
        if (Number.isNaN(parsed.getTime())) {
            return value;
        }
        return parsed.toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit', month: 'short' });
    };

    const setActiveChip = (value) => {
        if (chipsWrap) {
            chipsWrap.querySelectorAll('[data-date-chip]').forEach((chip) => {
                const isActive = chip.dataset.dateChip === value;
                chip.classList.toggle('btn-hms', isActive);
                chip.classList.toggle('btn-outline-secondary', !isActive);
            });
        }

        if (activeChip) {
            activeChip.dataset.activeDate = value;
            activeChip.textContent = formatChip(value);
        }
    };

    const fetchAvailability = async (value) => {
        if (!value || (minDate && value < minDate)) {
            return;
        }

        const current = ++requestId;
        if (loading) {
            loading.classList.remove('d-none');
        }
        if (dateInput) {
            dateInput.setAttribute('disabled', 'disabled');
        }

        try {
            const response = await fetch(availabilityUrl + '?date=' + encodeURIComponent(value), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('Availability request failed (' + response.status + ')');
            }

            const payload = await response.json();
            if (current !== requestId || !payload.date) {
                return;
            }

            if (offersWrap && typeof payload.offers_html === 'string') {
                offersWrap.innerHTML = payload.offers_html;
            }

            if (noticeWrap && typeof payload.booking_notice_html === 'string') {
                noticeWrap.innerHTML = payload.booking_notice_html;
            }

            if (dateInput) {
                dateInput.value = payload.date;
            }

            setActiveChip(payload.date);

            const url = new URL(window.location.href);
            url.searchParams.set('date', payload.date);
            window.history.replaceState(null, '', url.toString());
        } catch (error) {
            const url = new URL(window.location.href);
            url.searchParams.set('date', value);
            window.location.assign(url.toString());
        } finally {
            if (current === requestId) {
                if (loading) {
                    loading.classList.add('d-none');
                }
                if (dateInput) {
                    dateInput.removeAttribute('disabled');
                }
            }
        }
    };

    if (dateInput) {
        dateInput.addEventListener('change', (event) => fetchAvailability(event.target.value));
    }

    if (chipsWrap) {
        chipsWrap.addEventListener('click', (event) => {
            const chip = event.target.closest('[data-date-chip]');
            if (!chip) {
                return;
            }
            event.preventDefault();
            fetchAvailability(chip.dataset.dateChip);
        });
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        fetchAvailability(dateInput ? dateInput.value : '');
    });
});
</script>
@endpush