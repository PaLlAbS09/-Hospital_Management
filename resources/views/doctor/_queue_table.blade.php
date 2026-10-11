<div class="table-responsive">
    <table class="table hms-table hms-table--responsive align-middle">
        <thead>
            <tr>
                <th>When</th>
                <th>Patient</th>
                <th>Clinic</th>
                <th>Medical record</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($appointments as $appointment)
                <tr>
                    <td class="text-nowrap" data-label="When">
                        <div class="fw-semibold">{{ $appointment->appointment_date?->format('d M Y') }}</div>
                        <div class="text-muted small">{{ $appointment->formatted_time }}</div>
                    </td>
                    <td data-label="Patient">
                        <div class="d-flex align-items-center gap-2">
                            <span class="hms-avatar">{{ $appointment->patient?->initials ?? 'PT' }}</span>
                            <div>
                                <div class="fw-semibold">{{ $appointment->patient?->full_name ?? '—' }}</div>
                                <div class="text-muted small">
                                    <i class="bi bi-telephone me-1"></i>{{ $appointment->contact_phone ?? $appointment->patient?->contact }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Clinic">{{ $appointment->clinic?->clinic_name ?? '—' }}</td>
                    <td style="max-width: 260px" data-label="Medical record">
                        @if ($appointment->has_prescription)
                            <div class="fw-semibold small">{{ $appointment->disease ?: 'Diagnosis recorded' }}</div>
                            <div class="text-muted small text-truncate-2">{{ $appointment->prescription_details }}</div>
                        @else
                            <span class="text-muted small">No prescription yet</span>
                        @endif
                    </td>
                    <td data-label="Status">
                        <x-status-badge :status="$appointment->status" />
                        @if ($appointment->is_checked_in)
                            <div><small class="text-success">Arrived {{ $appointment->checked_in_at?->format('H:i') }}</small></div>
                        @elseif ($appointment->is_active)
                            <div><small class="text-warning">Not arrived</small></div>
                        @endif
                    </td>
                    <td class="text-end text-nowrap" data-label="Actions">
                        <div class="d-inline-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-hms" data-bs-toggle="modal"
                                    data-bs-target="#rx-{{ $appointment->appointment_id }}"
                                    aria-label="Record prescription for {{ $appointment->patient?->full_name ?? 'patient' }}">
                                <i class="bi bi-file-earmark-medical me-1" aria-hidden="true"></i>{{ $appointment->has_prescription ? 'Edit' : 'Prescribe' }}
                            </button>

                            @if ($appointment->status === \App\Models\Appointment::STATUS_ACTIVE && ! $appointment->is_checked_in)
                                <form method="POST" action="{{ route('doctor.appointments.check-in', $appointment) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success"
                                            aria-label="Mark {{ $appointment->patient?->full_name ?? 'patient' }} as arrived">
                                        <i class="bi bi-person-check me-1" aria-hidden="true"></i>Arrived
                                    </button>
                                </form>
                            @endif

                            @if ($appointment->status === \App\Models\Appointment::STATUS_ACTIVE)
                                <form method="POST" action="{{ route('doctor.appointments.complete', $appointment) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-success"
                                            aria-label="Mark appointment for {{ $appointment->patient?->full_name ?? 'patient' }} as completed"
                                            @if (! $appointment->has_prescription) onclick="return confirm('No prescription recorded yet. Mark this appointment as completed anyway?')" @endif>
                                        <i class="bi bi-check2 me-1" aria-hidden="true"></i>Complete
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('doctor.appointments.cancel', $appointment) }}"
                                      onsubmit="return confirm('Cancel this appointment?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger"
                                            aria-label="Cancel appointment for {{ $appointment->patient?->full_name ?? 'patient' }}">
                                        <i class="bi bi-x-lg me-1" aria-hidden="true"></i>Cancel
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>