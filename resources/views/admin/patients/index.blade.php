@extends('layouts.app')

@section('title', 'Patient directory')
@section('subtitle', 'Browse every registered patient and open their appointment history')

@section('content')
    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <form method="GET" action="{{ route('admin.patients.index') }}" class="row g-2">
                <div class="col-md-9">
                    <input type="search" name="search" value="{{ $search }}" class="form-control"
                           placeholder="Search by name, email or contact number">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-hms flex-grow-1"><i class="bi bi-search me-1"></i>Search</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.patients.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="hms-card">
        <div class="hms-card__header">
            <h5><i class="bi bi-people me-2"></i>Registered patients ({{ $patients->total() }})</h5>
        </div>
        <div class="hms-card__body hms-card__body--flush">
            @if ($patients->isEmpty())
                <div class="hms-empty">
                    <i class="bi bi-people"></i>
                    <p class="mb-0 fw-semibold">No patients match the current search.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table hms-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Gender</th>
                                <th>Contact</th>
                                <th class="text-center">Appointments</th>
                                <th>Registered</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($patients as $patient)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="hms-avatar">{{ $patient->initials }}</span>
                                            <div>
                                                <div class="fw-semibold">{{ $patient->full_name }}</div>
                                                <div class="text-muted small">{{ $patient->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $patient->gender }}</td>
                                    <td>{{ $patient->contact }}</td>
                                    <td class="text-center">
                                        <span class="badge text-bg-light border">{{ $patient->appointments_count }}</span>
                                    </td>
                                    <td class="text-muted small">{{ $patient->created_at?->format('d M Y') ?? '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.patients.show', $patient) }}"
                                           class="btn btn-sm btn-outline-hms">
                                            <i class="bi bi-eye me-1"></i>History
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($patients->hasPages())
            <div class="hms-card__body border-top d-flex justify-content-center">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
@endsection