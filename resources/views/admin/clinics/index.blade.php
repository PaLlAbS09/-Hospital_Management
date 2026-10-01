@extends('layouts.app')

@section('title', 'Clinic management')
@section('subtitle', 'Approve, reject and audit every registered clinic')

@section('content')
    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="{{ route('admin.clinics.index', array_filter(['search' => $search])) }}"
                   class="btn btn-sm {{ $status === '' ? 'btn-hms' : 'btn-outline-secondary' }}">
                    All <span class="badge text-bg-light ms-1">{{ $counts['all'] }}</span>
                </a>
                @foreach (['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'] as $key => $variant)
                    <a href="{{ route('admin.clinics.index', array_filter(['status' => $key, 'search' => $search])) }}"
                       class="btn btn-sm {{ $status === $key ? 'btn-'.$variant : 'btn-outline-secondary' }}">
                        {{ ucfirst($key) }} <span class="badge text-bg-light ms-1">{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.clinics.index') }}" class="row g-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="col-md-9">
                    <input type="search" name="search" value="{{ $search }}" class="form-control"
                           placeholder="Search by clinic name, area, email or contact number">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-hms flex-grow-1"><i class="bi bi-search me-1"></i>Search</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.clinics.index', array_filter(['status' => $status])) }}"
                           class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="hms-card">
        <div class="hms-card__header">
            <h5><i class="bi bi-hospital me-2"></i>Registered clinics ({{ $clinics->total() }})</h5>
        </div>
        <div class="hms-card__body hms-card__body--flush">
            @if ($clinics->isEmpty())
                <div class="hms-empty">
                    <i class="bi bi-hospital"></i>
                    <p class="mb-0 fw-semibold">No clinics match the current filter.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table hms-table">
                        <thead>
                            <tr>
                                <th>Clinic</th>
                                <th>Area</th>
                                <th>Contact</th>
                                <th class="text-center">Schedules</th>
                                <th class="text-center">Appointments</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clinics as $clinic)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="hms-avatar"><i class="bi bi-hospital"></i></span>
                                            <div>
                                                <div class="fw-semibold">{{ $clinic->clinic_name }}</div>
                                                <div class="text-muted small">{{ $clinic->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><i class="bi bi-geo-alt text-muted me-1"></i>{{ $clinic->area }}</td>
                                    <td>{{ $clinic->contact_number }}</td>
                                    <td class="text-center">{{ $clinic->schedules_count }}</td>
                                    <td class="text-center">{{ $clinic->appointments_count }}</td>
                                    <td><x-status-badge :status="$clinic->status" /></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            @if ($clinic->status !== \App\Models\Clinic::STATUS_APPROVED)
                                                <form method="POST" action="{{ route('admin.clinics.approve', $clinic) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-success">
                                                        <i class="bi bi-check2 me-1"></i>Approve
                                                    </button>
                                                </form>
                                            @endif
                                            @if ($clinic->status !== \App\Models\Clinic::STATUS_REJECTED)
                                                <form method="POST" action="{{ route('admin.clinics.reject', $clinic) }}"
                                                      onsubmit="return confirm('Reject this clinic?')">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-x-lg me-1"></i>Reject
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
            @endif
        </div>
        @if ($clinics->hasPages())
            <div class="hms-card__body border-top d-flex justify-content-center">
                {{ $clinics->links() }}
            </div>
        @endif
    </div>
@endsection
