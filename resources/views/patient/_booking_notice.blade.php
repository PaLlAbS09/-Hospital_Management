@if ($myAppointments->isNotEmpty())
    <div class="alert alert-success d-flex align-items-start gap-2" role="alert" id="booking-notice">
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
