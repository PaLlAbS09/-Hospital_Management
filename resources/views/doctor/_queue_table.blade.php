<div class="table-responsive">
    <table class="table hms-table align-middle">
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
                    <td class="text-nowrap">
                        <div class="fw-semibold">{{ $appointment->appointment_date?->format('d M Y') }}</div>
                        <div class="text-muted small">{{ $appointment->formatted_time }}</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="hms-avatar">{{ $appointment->patient?->initials ?? 'PT' }}</span>
                            <div>
                                <div class="fw-semibold">{{ $appointment->patient?->full_name ?? '—' }}</div>
                                <div class="text-muted small">{{ $appointment->patient?->contact }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $appointment->clinic?->clinic_name ?? '—' }}</td>
                    <td style="max-width: 260px">
                        @if ($appointment->has_prescription)
                            <div class="fw-semibold small">{{ $appointment->disease ?: 'Diagnosis recorded' }}</div>
                            <div class="text-muted small text-truncate-2">{{ $appointment->prescription_details }}</div>
                        @else
                            <span class="text-muted small">No prescription yet</span>
                        @endif
                    </td>
                    <td><x-status-badge :status="$appointment->status" /></td>
                    <td class="text-end text-nowrap">
                        <div class="d-inline-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-hms" data-bs-toggle="modal"
                                    data-bs-target="#rx-{{ $appointment->appointment_id }}">
                                <i class="bi bi-file-earmark-medical me-1"></i>{{ $appointment->has_prescription ? 'Edit' : 'Prescribe' }}
                            </button>

                            @if ($appointment->status === \App\Models\Appointment::STATUS_ACTIVE)
                                <form method="POST" action="{{ route('doctor.appointments.complete', $appointment) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-success"
                                            @if (! $appointment->has_prescription) onclick="return confirm('No prescription recorded yet. Mark this appointment as completed anyway?')" @endif>
                                        <i class="bi bi-check2 me-1"></i>Complete
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('doctor.appointments.cancel', $appointment) }}"
                                      onsubmit="return confirm('Cancel this appointment?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-lg me-1"></i>Cancel
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