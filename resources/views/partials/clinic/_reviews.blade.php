{{--
    Public patient reviews left after a completed visit.

    Expects: $reviews
--}}
@if ($reviews->isNotEmpty())
    <div class="mt-4">
        <h2 class="h5 mb-1">What patients said</h2>
        <p class="text-muted small mb-3">Shared by patients after their appointment at this clinic.</p>

        <div class="row g-3">
            @foreach ($reviews as $review)
                <div class="col-md-6 col-xl-4">
                    <div class="hms-card h-100">
                        <div class="hms-card__body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="hms-avatar">{{ $review->patient?->initials ?? 'PT' }}</span>
                                <div style="min-width:0">
                                    <div class="fw-semibold small text-truncate">
                                        {{ $review->patient?->full_name ?? 'Patient' }}
                                    </div>
                                    <div class="hms-best__stars" style="font-size: .82rem">{{ $review->stars }}</div>
                                </div>
                            </div>

                            @if ($review->experience)
                                <p class="small text-muted mb-2">{{ $review->experience }}</p>
                            @endif

                            <div class="small text-muted">
                                <i class="bi bi-person-badge me-1"></i>Dr. {{ $review->doctor?->full_name ?? '—' }}
                                &middot; {{ $review->created_at?->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif