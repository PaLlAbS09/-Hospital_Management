<div class="row g-3" id="booking-offers">
    @forelse ($offers as $offer)
        @include('patient._offer_card', ['clinic' => $clinic, 'date' => $date, 'schedule' => $offer['schedule'], 'slots' => $offer['slots']])
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
