@extends('layouts.app')

@section('title', 'Doctor management')
@section('subtitle', 'Create doctor accounts, review the roster and remove unused profiles')

@section('content')
    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <form method="GET" action="{{ route('admin.doctors.index') }}" class="row g-2">
                <div class="col-md-9">
                    <input type="search" name="search" value="{{ $search }}" class="form-control"
                           placeholder="Search by name, specialization or email">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-hms flex-grow-1"><i class="bi bi-search me-1"></i>Search</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="hms-card">
        <div class="hms-card__header">
            <h5><i class="bi bi-person-badge me-2"></i>Registered doctors ({{ $doctors->total() }})</h5>
            <a href="{{ route('admin.doctors.create') }}" class="btn btn-sm btn-hms">
                <i class="bi bi-plus-lg me-1"></i>Add new doctor
            </a>
        </div>
        <div class="hms-card__body hms-card__body--flush">
            @if ($doctors->isEmpty())
                <div class="hms-empty">
                    <i class="bi bi-person-badge"></i>
                    <p class="mb-0 fw-semibold">No doctors have been added yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table hms-table">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Specialization</th>
                                <th>Contact</th>
                                <th class="text-center">Schedules</th>
                                <th class="text-center">Appointments</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($doctors as $doctor)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="hms-avatar">{{ $doctor->initials }}</span>
                                            <div>
                                                <div class="fw-semibold">Dr. {{ $doctor->full_name }}</div>
                                                <div class="text-muted small">{{ $doctor->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $doctor->specialization }}</td>
                                    <td>{{ $doctor->contact }}</td>
                                    <td class="text-center">{{ $doctor->schedules_count }}</td>
                                    <td class="text-center">{{ $doctor->appointments_count }}</td>
                                    <td class="text-end">
                                        <form method="POST"
                                              action="{{ route('admin.doctors.destroy', $doctor) }}"
                                              onsubmit="return confirm('Delete this doctor? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash3 me-1"></i>Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($doctors->hasPages())
            <div class="hms-card__body border-top d-flex justify-content-center">
                {{ $doctors->links() }}
            </div>
        @endif
    </div>
@endsection
