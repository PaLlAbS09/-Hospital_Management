@php($availableCount = collect($slots)->where('available', true)->count())

<div class="col-lg-6">
    <div class="hms-card h-100">
        <div class="hms-card__header">
            <div class="d-flex align-items-center gap-2">
                <span class="hms-avatar">{{ $schedule->doctor?->initials ?? 'DR' }}</span>
                <div>
                    <h5>Dr. {{ $schedule->doctor?->full_name ?? 'Removed doctor' }}</h5>
                    <div class="text-muted small">{{ $schedule->doctor?->specialization }}</div>
                </div>
            </div>
            <span class="hms-chip">
                <i class="bi bi-people"></i>{{ $availableCount }} slots free
            </span>
        </div>
        <div class="hms-card__body">
            <div class="d-flex flex-wrap gap-3 mb-3">
                <span class="small text-muted">
                    <i class="bi bi-clock me-1"></i>{{ $schedule->time_range }}
                </span>
                <span class="small text-muted">
                    <i class="bi bi-people me-1"></i>
                    {{ $schedule->bookedCount() }}/{{ $schedule->patient_capacity }} patients
                </span>
            </div>

            @if ($availableCount === 0)
                <div class="alert alert-secondary mb-0 small" role="alert">
                    <i class="bi bi-info-circle me-1"></i>
                    Fully booked for this date. Please choose another doctor or date.
                </div>
            @else
                <form method="POST" action="{{ route('patient.appointments.store') }}">
                    @csrf
                    <input type="hidden" name="clinic_id" value="{{ $clinic->clinic_id }}">
                    <input type="hidden" name="doctor_id" value="{{ $schedule->doctor_id }}">
                    <input type="hidden" name="appointment_date" value="{{ $date }}">

                    <div class="mb-3">
                        @foreach ($slots as $slot)
                            <label class="hms-slot {{ $slot['available'] ? '' : 'hms-slot--disabled' }}">
                                <input type="radio"
                                       name="appointment_time"
                                       value="{{ $slot['time'] }}"
                                       @disabled(! $slot['available'])
                                       @checked(old('appointment_time') === $slot['time'] && $slot['available'])
                                       required>
                                {{ $slot['time'] }}
                            </label>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-hms w-100">
                        <i class="bi bi-calendar-check me-1"></i>Book appointment with
                        Dr. {{ $schedule->doctor?->full_name }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>