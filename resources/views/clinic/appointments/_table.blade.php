<div class="table-responsive">
    <table class="table hms-table">
        <thead>
            <tr>
                <th>When</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Diagnosis</th>
                <th>Status</th>
                <th class="text-end">Arrival</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($appointments as $appointment)
                <tr>
                    <td class="text-nowrap">
                        <div class="fw-semibold">{{ $appointment->appointment_date?->format('d M Y') }}</div>
                        <div class="text-muted small">{{ $appointment->formatted_time }}</div>
                        @if ($appointment->is_checked_in)
                            <span class="badge text-bg-success mt-1">
                                <i class="bi bi-person-check me-1"></i>Arrived {{ $appointment->checked_in_at?->format('H:i') }}
                            </span>
                        @elseif ($appointment->is_active)
                            <span class="badge text-bg-warning mt-1">
                                <i class="bi bi-hourglass-split me-1"></i>Not arrived
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $appointment->patient?->full_name ?? '—' }}</div>
                        <div class="text-muted small">
                            <i class="bi bi-telephone me-1"></i>{{ $appointment->contact_phone ?? $appointment->patient?->contact }}
                        </div>
                    </td>
                    <td>{{ $appointment->doctor?->full_name ?? '—' }}</td>
                    <td>
                        @if ($appointment->disease)
                            <div>{{ $appointment->disease }}</div>
                            <div class="text-muted small text-truncate-2">{{ $appointment->prescription_details }}</div>
                        @else
                            <span class="text-muted small">Not recorded</span>
                        @endif
                    </td>
                    <td>
                        <x-status-badge :status="$appointment->status" />
                        <div><small class="text-muted">{{ $appointment->patient?->email ?? '—' }}</small></div>
                    </td>
                    <td class="text-end">
                        @if ($appointment->is_active && ! $appointment->is_checked_in)
                            <form method="POST" action="{{ route('clinic.appointments.check-in', $appointment) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success">
                                    <i class="bi bi-person-check me-1"></i>Arrived
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>