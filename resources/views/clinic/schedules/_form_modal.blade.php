{{-- Create schedule modal --}}
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true" aria-labelledby="addScheduleLabel">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST" action="{{ route('clinic.schedules.store') }}">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title" id="addScheduleLabel">
                    <i class="bi bi-calendar-plus me-2"></i>New doctor schedule
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="doctor_id">Doctor</label>
                        <select class="form-select @error('doctor_id') is-invalid @enderror"
                                id="doctor_id" name="doctor_id" required>
                            <option value="">Select a doctor</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->doctor_id }}" @selected((int) old('doctor_id') === $doctor->doctor_id)>
                                    Dr. {{ $doctor->full_name }} — {{ $doctor->specialization }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="schedule_date">Date</label>
                        <input type="date" id="schedule_date" name="schedule_date"
                               value="{{ old('schedule_date', today()->toDateString()) }}"
                               min="{{ today()->toDateString() }}"
                               class="form-control @error('schedule_date') is-invalid @enderror" required>
                        @error('schedule_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="patient_capacity">Patient capacity</label>
                        <input type="number" id="patient_capacity" name="patient_capacity"
                               value="{{ old('patient_capacity', 20) }}" min="1" max="200"
                               class="form-control @error('patient_capacity') is-invalid @enderror" required>
                        @error('patient_capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="start_time">Start time</label>
                        <input type="time" id="start_time" name="start_time" value="{{ old('start_time', '09:00') }}"
                               class="form-control @error('start_time') is-invalid @enderror" required>
                        @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="end_time">End time</label>
                        <input type="time" id="end_time" name="end_time" value="{{ old('end_time', '17:00') }}"
                               class="form-control @error('end_time') is-invalid @enderror" required>
                        @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-hms">
                    <i class="bi bi-check2 me-1"></i>Create schedule
                </button>
            </div>
        </form>
    </div>
</div>