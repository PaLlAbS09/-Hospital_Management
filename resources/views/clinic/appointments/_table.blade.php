<div class="table-responsive">
    <table class="table hms-table">
        <thead>
            <tr>
                <th>When</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Diagnosis</th>
                <th>Status</th>
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
                        <div class="fw-semibold">{{ $appointment->patient?->full_name ?? '—' }}</div>
                        <div class="text-muted small">{{ $appointment->patient?->contact }}</div>
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
                    <td><x-status-badge :status="$appointment->status" /></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>