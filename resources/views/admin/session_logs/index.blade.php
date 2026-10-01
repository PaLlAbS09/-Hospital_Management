@extends('layouts.app')

@section('title', 'Doctor session logs')
@section('subtitle', 'Audit trail of doctor logins, logouts and session durations')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Log entries" :value="$logs->total()" icon="bi-clock-history" variant="primary" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Doctors" :value="$doctors->count()" icon="bi-person-badge" variant="accent" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Open sessions" :value="$openSessions" icon="bi-person-check" variant="amber"
                         hint="Signed in right now" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Closed sessions" :value="$logs->total() - $openSessions" icon="bi-box-arrow-right"
                         variant="slate" />
        </div>
    </div>

    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <form method="GET" action="{{ route('admin.session-logs.index') }}" class="row g-2">
                <div class="col-md-9">
                    <label class="visually-hidden" for="doctor_id">Doctor</label>
                    <select class="form-select" id="doctor_id" name="doctor_id">
                        <option value="">All doctors</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->doctor_id }}" @selected($doctorId === $doctor->doctor_id)>
                                Dr. {{ $doctor->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-hms flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                    <a href="{{ route('admin.session-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="hms-card">
        <div class="hms-card__header">
            <h5><i class="bi bi-clock-history me-2"></i>Session history ({{ $logs->total() }})</h5>
        </div>
        <div class="hms-card__body hms-card__body--flush">
            @if ($logs->isEmpty())
                <div class="hms-empty">
                    <i class="bi bi-clock-history"></i>
                    <p class="mb-0 fw-semibold">No session logs have been recorded yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table hms-table">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Login time</th>
                                <th>Logout time</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="hms-avatar">{{ $log->doctor?->initials ?? 'DR' }}</span>
                                            <div>
                                                <div class="fw-semibold">{{ $log->doctor?->full_name ?? 'Removed doctor' }}</div>
                                                <div class="text-muted small">{{ $log->doctor?->specialization }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">{{ $log->login_time?->format('d M Y, H:i') ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $log->logout_time?->format('d M Y, H:i') ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $log->duration_for_humans }}</td>
                                    <td>
                                        @if ($log->is_open)
                                            <span class="badge text-bg-success">Online</span>
                                        @else
                                            <span class="badge text-bg-secondary">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($logs->hasPages())
            <div class="hms-card__body border-top d-flex justify-content-center">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection