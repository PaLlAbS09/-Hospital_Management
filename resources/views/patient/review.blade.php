@extends('layouts.app')

@section('title', 'Rate your doctor')
@section('subtitle', 'Your feedback helps other patients choose the right doctor')

@section('content')
    <div class="row g-3 justify-content-center">
        <div class="col-xl-8">

            {{-- Visit summary --------------------------------------------- --}}
            <div class="hms-card mb-3">
                <div class="hms-card__header">
                    <h5><i class="bi bi-calendar2-check me-2"></i>Visit details</h5>
                    <x-status-badge :status="$appointment->status" />
                </div>
                <div class="hms-card__body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <span class="hms-best__avatar">{{ $appointment->doctor?->initials ?? 'DR' }}</span>
                                <div style="min-width:0">
                                    <div class="fw-semibold">Dr. {{ $appointment->doctor?->full_name ?? '—' }}</div>
                                    <div class="text-muted small">{{ $appointment->doctor?->specialization }}</div>
                                    @if ($appointment->doctor?->experience_label)
                                        <div class="small text-muted">
                                            <i class="bi bi-award me-1"></i>{{ $appointment->doctor->experience_label }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled small text-muted mb-0">
                                <li class="mb-1">
                                    <i class="bi bi-hospital me-2"></i>{{ $appointment->clinic?->clinic_name ?? '—' }}
                                </li>
                                <li class="mb-1">
                                    <i class="bi bi-geo-alt me-2"></i>{{ $appointment->clinic?->area ?? '—' }}
                                </li>
                                <li>
                                    <i class="bi bi-clock me-2"></i>
                                    {{ $appointment->appointment_date?->format('l, d F Y') }} at {{ $appointment->formatted_time }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rating form ----------------------------------------------- --}}
            <div class="hms-card">
                <div class="hms-card__header">
                    <h5><i class="bi bi-star me-2"></i>Share your experience</h5>
                </div>
                <div class="hms-card__body">
                    <form method="POST" action="{{ route('patient.appointments.review.store', $appointment) }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="rating">How would you rate this visit?</label>

                            <div class="hms-rating" role="radiogroup" aria-labelledby="rating">
                                @foreach ($ratingScale as $star)
                                    <label class="hms-rating__star">
                                        <input type="radio"
                                               name="rating"
                                               id="rating_{{ $star }}"
                                               value="{{ $star }}"
                                               class="visually-hidden"
                                               @checked((int) old('rating') === $star)
                                               required>
                                        <span class="hms-rating__icon">★</span>
                                        <span class="hms-rating__label">{{ $star }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @error('rating')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="experience">Your experience (optional)</label>
                            <textarea class="form-control @error('experience') is-invalid @enderror"
                                      id="experience" name="experience" rows="5"
                                      placeholder="How was the doctor's behaviour, the waiting time and the treatment? Other patients find this helpful.">{{ old('experience') }}</textarea>
                            <div class="form-text">
                                Share what other patients should know before booking. Please avoid personal
                                identifiable details such as phone numbers or addresses.
                            </div>
                            @error('experience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="is_public" name="is_public" value="1" @checked(old('is_public', true))>
                            <label class="form-check-label" for="is_public">
                                Show my rating publicly on the doctor's profile
                            </label>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-hms">
                                <i class="bi bi-send me-1"></i>Submit rating
                            </button>
                            <a href="{{ route('patient.dashboard') }}" class="btn btn-outline-secondary">Later</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Clicking a star selects it and highlights every star up to that value.
        (function () {
            const group = document.querySelector('.hms-rating');
            if (!group) return;

            const inputs = Array.from(group.querySelectorAll('input[type="radio"]'));
            const paint = (value) => {
                group.querySelectorAll('.hms-rating__star').forEach((star, index) => {
                    star.classList.toggle('is-active', index < value);
                });
            };

            inputs.forEach((input) => {
                input.addEventListener('change', () => paint(Number(input.value)));
            });

            const initial = inputs.find((input) => input.checked);
            paint(initial ? Number(initial.value) : 0);
        })();
    </script>
@endpush