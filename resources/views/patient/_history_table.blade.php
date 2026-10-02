<div class="hms-card">
    <div class="hms-card__header">
        <h5><i class="bi bi-clock-history me-2"></i>Full appointment history ({{ $appointments->count() }})</h5>
    </div>
    <div class="hms-card__body hms-card__body--flush">
        @if ($appointments->isEmpty())
            <div class="hms-empty">
                <i class="bi bi-calendar-x"></i>
                <p class="mb-1 fw-semibold">You have no appointments yet.</p>
                <p class="mb-3 small">Find a clinic in your area and reserve a time slot.</p>
                <a href="{{ route('patient.clinics.index') }}" class="btn btn-sm btn-hms">
                    <i class="bi bi-search me-1"></i>Find a clinic
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table hms-table">
                    <thead>
                        <tr>
                            <th>Date &amp; time</th>
                            <th>Doctor</th>
                            <th>Clinic</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appointments as $appointment)
                            <tr>
                                <td class="text-nowrap">
                                    <div class="fw-semibold">{{ $appointment->appointment_date?->format('d M Y') }}</div>
                                    <div class="text-muted small">{{ $appointment->formatted_time }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">Dr. {{ $appointment->doctor?->full_name ?? '—' }}</div>
                                    <div class="text-muted small">{{ $appointment->doctor?->specialization }}</div>
                                </td>
                                <td>
                                    <div>{{ $appointment->clinic?->clinic_name ?? '—' }}</div>
                                    <div class="text-muted small">{{ $appointment->clinic?->area }}</div>
                                </td>
                                <td>
                                    <x-status-badge :status="$appointment->status" />
                                    @if ($appointment->is_checked_in)
                                        <div><small class="text-success">Arrived {{ $appointment->checked_in_at?->format('d M H:i') }}</small></div>
                                    @endif
                                    <div><small class="text-muted">Phone: {{ $appointment->contact_phone ?? $appointment->patient?->contact ?? '—' }}</small></div>
                                </td>
                                <td class="text-end text-nowrap">
                                    <div class="d-inline-flex gap-1">
                                        @if ($appointment->is_active)
                                            <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}"
                                                  onsubmit="return confirm('Cancel this appointment?')">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-x-lg me-1"></i>Cancel
                                                </button>
                                            </form>
                                        @endif
                                        @if ($appointment->has_prescription)
                                            <a href="{{ route('patient.appointments.prescription', $appointment) }}"
                                               class="btn btn-sm btn-outline-hms" target="_blank" rel="noopener">
                                                <i class="bi bi-printer me-1"></i>Prescription
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>