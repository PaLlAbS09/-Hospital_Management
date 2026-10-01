<div class="modal fade" id="rx-{{ $appointment->appointment_id }}" tabindex="-1"
     aria-hidden="true" aria-labelledby="rx-label-{{ $appointment->appointment_id }}">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="rx-label-{{ $appointment->appointment_id }}">
                    <i class="bi bi-file-earmark-medical me-2"></i>Prescription ·
                    {{ $appointment->patient?->full_name ?? 'Patient' }} ·
                    {{ $appointment->appointment_date?->format('d M Y') }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted small text-uppercase fw-semibold">Diagnosis</dt>
                    <dd class="col-sm-8">{{ $appointment->disease ?: '—' }}</dd>
                    <dt class="col-sm-4 text-muted small text-uppercase fw-semibold">Allergies</dt>
                    <dd class="col-sm-8">{{ $appointment->allergies ?: 'None recorded' }}</dd>
                    <dt class="col-sm-4 text-muted small text-uppercase fw-semibold">Prescription</dt>
                    <dd class="col-sm-8" style="white-space: pre-wrap">{{ $appointment->prescription_details ?: '—' }}</dd>
                    <dt class="col-sm-4 text-muted small text-uppercase fw-semibold">Prescribed by</dt>
                    <dd class="col-sm-8">{{ $appointment->doctor?->full_name ?? '—' }}</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>