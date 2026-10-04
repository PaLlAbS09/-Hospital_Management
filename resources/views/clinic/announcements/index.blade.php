@extends('layouts.app')

@section('title', 'New doctor promotions')
@section('subtitle', 'Announce doctors joining your clinic - these appear on the landing page slider')

@section('content')
    <div class="row g-3">
        {{-- Form ------------------------------------------------------- --}}
        <div class="col-xl-5">
            <div class="hms-card">
                <div class="hms-card__header">
                    <h5><i class="bi bi-megaphone me-2"></i>New announcement</h5>
                </div>
                <div class="hms-card__body">
                    <form method="POST" action="{{ route('clinic.announcements.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="doctor_id">Doctor (optional)</label>
                            <select class="form-select @error('doctor_id') is-invalid @enderror"
                                    id="doctor_id" name="doctor_id">
                                <option value="">Announce without naming a doctor</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->doctor_id }}"
                                            @selected((int) old('doctor_id') === $doctor->doctor_id)>
                                        Dr. {{ $doctor->full_name }} — {{ $doctor->specialization }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Pick a doctor only if they already have an account in the system.</div>
                            @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="department">Department</label>
                            <input type="text" class="form-control @error('department') is-invalid @enderror"
                                   id="department" name="department" value="{{ old('department') }}"
                                   placeholder="e.g. Orthopedics" required>
                            @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="joining_date">Joining date</label>
                                <input type="date" class="form-control @error('joining_date') is-invalid @enderror"
                                       id="joining_date" name="joining_date"
                                       value="{{ old('joining_date', today()->addWeek()->toDateString()) }}" required>
                                @error('joining_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="joining_time">Joining time</label>
                                <input type="time" class="form-control @error('joining_time') is-invalid @enderror"
                                       id="joining_time" name="joining_time"
                                       value="{{ old('joining_time', '10:00') }}" required>
                                @error('joining_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-3 mb-3">
                            <label class="form-label fw-semibold" for="message">Message (optional)</label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="message" name="message" rows="3"
                                      placeholder="Consultation timings, first-day specialities, etc.">{{ old('message') }}</textarea>
                            @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                            <label class="form-check-label" for="is_active">Publish immediately on the website</label>
                        </div>

                        <button type="submit" class="btn btn-hms">
                            <i class="bi bi-megaphone me-1"></i>Publish announcement
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- List ------------------------------------------------------- --}}
        <div class="col-xl-7">
            <div class="hms-card">
                <div class="hms-card__header">
                    <h5><i class="bi bi-list-ul me-2"></i>Published announcements ({{ $announcements->count() }})</h5>
                </div>
                <div class="hms-card__body hms-card__body--flush">
                    @if ($announcements->isEmpty())
                        <div class="hms-empty">
                            <i class="bi bi-megaphone"></i>
                            <p class="fw-semibold mb-1">No announcements yet.</p>
                            <p class="mb-0 small">Publish one to promote a new doctor on the landing page slider.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table hms-table">
                                <thead>
                                    <tr>
                                        <th>Doctor / Department</th>
                                        <th>Joining</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($announcements as $announcement)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $announcement->doctor ? 'Dr. '.$announcement->doctor->full_name : 'Unnamed doctor' }}
                                                </div>
                                                <div class="text-muted small">{{ $announcement->department }}</div>
                                                @if ($announcement->message)
                                                    <div class="text-muted small text-truncate" style="max-width: 260px">
                                                        {{ $announcement->message }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <div>{{ $announcement->joining_date?->format('d M Y') ?? '—' }}</div>
                                                <div class="text-muted small">{{ $announcement->formatted_joining_time ?? '—' }}</div>
                                            </td>
                                            <td>
                                                @if ($announcement->is_active)
                                                    <span class="badge text-bg-success">Live</span>
                                                @else
                                                    <span class="badge text-bg-secondary">Hidden</span>
                                                @endif
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <div class="d-inline-flex gap-1">
                                                    <form method="POST"
                                                          action="{{ route('clinic.announcements.toggle', $announcement) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="btn btn-sm btn-outline-hms">
                                                            <i class="bi bi-{{ $announcement->is_active ? 'eye-slash' : 'eye' }} me-1"></i>
                                                            {{ $announcement->is_active ? 'Hide' : 'Show' }}
                                                        </button>
                                                    </form>
                                                    <form method="POST"
                                                          action="{{ route('clinic.announcements.destroy', $announcement) }}"
                                                          onsubmit="return confirm('Delete this announcement?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                    </form>
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
        </div>
    </div>
@endsection