@extends('layouts.app')

@section('title', 'Discount offers')
@section('subtitle', 'Publish offers such as "15% off on every medicine" - shown on the landing page slider')

@section('content')
    <div class="row g-3">
        {{-- Form ------------------------------------------------------- --}}
        <div class="col-xl-5">
            <div class="hms-card">
                <div class="hms-card__header">
                    <h5><i class="bi bi-percent me-2"></i>New offer</h5>
                </div>
                <div class="hms-card__body">
                    <form method="POST" action="{{ route('clinic.offers.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="title">Offer headline</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}"
                                   placeholder="e.g. 15% off on every medicine" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="discount_percent">Discount percentage</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('discount_percent') is-invalid @enderror"
                                       id="discount_percent" name="discount_percent"
                                       value="{{ old('discount_percent', 15) }}" min="1" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                            @error('discount_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="description">Description (optional)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="Terms, exclusions or extra details for this offer.">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="valid_from">Valid from</label>
                                <input type="date" class="form-control @error('valid_from') is-invalid @enderror"
                                       id="valid_from" name="valid_from" value="{{ old('valid_from') }}">
                                @error('valid_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="valid_until">Valid until</label>
                                <input type="date" class="form-control @error('valid_until') is-invalid @enderror"
                                       id="valid_until" name="valid_until" value="{{ old('valid_until') }}">
                                @error('valid_until') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-check form-switch mt-3 mb-3">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                            <label class="form-check-label" for="is_active">Publish immediately on the website</label>
                        </div>

                        <button type="submit" class="btn btn-hms">
                            <i class="bi bi-percent me-1"></i>Publish offer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- List ------------------------------------------------------- --}}
        <div class="col-xl-7">
            <div class="hms-card">
                <div class="hms-card__header">
                    <h5><i class="bi bi-list-ul me-2"></i>Your offers ({{ $offers->count() }})</h5>
                </div>
                <div class="hms-card__body hms-card__body--flush">
                    @if ($offers->isEmpty())
                        <div class="hms-empty">
                            <i class="bi bi-percent"></i>
                            <p class="fw-semibold mb-1">No offers yet.</p>
                            <p class="mb-0 small">Publish an offer to advertise a discount on the landing page slider.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table hms-table">
                                <thead>
                                    <tr>
                                        <th>Offer</th>
                                        <th>Validity</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($offers as $offer)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge text-bg-warning">{{ $offer->discount_label }}</span>
                                                    <div style="min-width:0">
                                                        <div class="fw-semibold text-truncate">{{ $offer->title }}</div>
                                                        @if ($offer->description)
                                                            <div class="text-muted small text-truncate" style="max-width: 260px">
                                                                {{ $offer->description }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-nowrap small">
                                                {{ $offer->validity_label ?? 'Always on' }}
                                            </td>
                                            <td>
                                                @if ($offer->is_active && ! $offer->is_expired)
                                                    <span class="badge text-bg-success">Live</span>
                                                @elseif ($offer->is_expired)
                                                    <span class="badge text-bg-secondary">Expired</span>
                                                @else
                                                    <span class="badge text-bg-secondary">Hidden</span>
                                                @endif
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <div class="d-inline-flex gap-1">
                                                    <form method="POST" action="{{ route('clinic.offers.toggle', $offer) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="btn btn-sm btn-outline-hms">
                                                            <i class="bi bi-{{ $offer->is_active ? 'eye-slash' : 'eye' }} me-1"></i>
                                                            {{ $offer->is_active ? 'Hide' : 'Show' }}
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('clinic.offers.destroy', $offer) }}"
                                                          onsubmit="return confirm('Delete this offer?')">
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