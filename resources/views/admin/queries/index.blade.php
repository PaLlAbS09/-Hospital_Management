@extends('layouts.app')

@section('title', 'Contact queries')
@section('subtitle', 'Messages submitted by visitors through the contact form')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Total queries" :value="$totalQueries" icon="bi-envelope-paper" variant="primary" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Last 7 days" :value="$activeQueries" icon="bi-envelope-paper-heart" variant="accent" />
        </div>
    </div>

    <div class="hms-card mb-3">
        <div class="hms-card__body">
            <form method="GET" action="{{ route('admin.queries.index') }}" class="row g-2">
                <div class="col-md-9">
                    <input type="search" name="search" value="{{ $search }}" class="form-control"
                           placeholder="Search by name, email or message">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-hms flex-grow-1"><i class="bi bi-search me-1"></i>Search</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.queries.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="hms-card">
        <div class="hms-card__header">
            <h5><i class="bi bi-envelope-paper me-2"></i>Inbox ({{ $queries->total() }})</h5>
        </div>
        <div class="hms-card__body hms-card__body--flush">
            @if ($queries->isEmpty())
                <div class="hms-empty">
                    <i class="bi bi-inbox"></i>
                    <p class="mb-0 fw-semibold">No contact queries match the current search.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table hms-table align-middle">
                        <thead>
                            <tr>
                                <th>Sender</th>
                                <th>Contact</th>
                                <th>Message</th>
                                <th>Submitted</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($queries as $query)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="hms-avatar">{{ strtoupper(substr($query->user_name, 0, 2)) }}</span>
                                            <div class="fw-semibold">{{ $query->user_name }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $query->email }}</div>
                                        <div class="small text-muted">{{ $query->contact_number }}</div>
                                    </td>
                                    <td style="max-width: 380px">
                                        <div class="text-truncate-2">{{ $query->message }}</div>
                                    </td>
                                    <td class="text-nowrap small text-muted">
                                        {{ $query->submitted_at?->format('d M Y, H:i') ?? '—' }}
                                        @if ($query->is_recent)
                                            <span class="badge text-bg-success ms-1">New</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.queries.destroy', $query) }}"
                                              onsubmit="return confirm('Delete this query?')">
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
        @if ($queries->hasPages())
            <div class="hms-card__body border-top d-flex justify-content-center">
                {{ $queries->links() }}
            </div>
        @endif
    </div>
@endsection