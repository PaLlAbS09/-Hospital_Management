<div class="modal fade" id="rx-{{ $appointment->appointment_id }}" tabindex="-1"
     aria-hidden="true" aria-labelledby="rx-label-{{ $appointment->appointment_id }}">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST"
              action="{{ route('doctor.appointments.prescription', $appointment) }}">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title" id="rx-label-{{ $appointment->appointment_id }}">
                    <i class="bi bi-file-earmark-medical me-2"></i>Prescription ·
                    {{ $appointment->patient?->full_name ?? 'Patient' }} ·
                    {{ $appointment->appointment_date?->format('d M Y') }} {{ $appointment->formatted_time }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="disease-{{ $appointment->appointment_id }}">Disease / diagnosis</label>
                        <input type="text" id="disease-{{ $appointment->appointment_id }}" name="disease"
                               value="{{ old('disease', $appointment->disease) }}" maxlength="255"
                               class="form-control" placeholder="e.g. Viral fever">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="allergies-{{ $appointment->appointment_id }}">Allergies</label>
                        <input type="text" id="allergies-{{ $appointment->appointment_id }}" name="allergies"
                               value="{{ old('allergies', $appointment->allergies) }}" maxlength="255"
                               class="form-control" placeholder="e.g. Penicillin">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="prescription_details-{{ $appointment->appointment_id }}">Prescription details</label>
                        <textarea id="prescription_details-{{ $appointment->appointment_id }}" name="prescription_details"
                                  rows="5" maxlength="5000" class="form-control"
                                  placeholder="Medicines, dosage, duration and advice">{{ old('prescription_details', $appointment->prescription_details) }}</textarea>
                        <div class="form-text">Supports multiple lines — one medicine per line works well.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-hms">
                    <i class="bi bi-check2 me-1"></i>Save prescription
                </button>
            </div>
        </form>
    </div>
</div>