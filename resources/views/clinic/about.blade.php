@extends('layouts.app')

@section('title', 'Clinic about section')
@section('subtitle', 'Banner image and description shown on your public clinic profile')

@section('content')
    <div class="row g-3">
        {{-- Editor ------------------------------------------------------- --}}
        <div class="col-xl-7">
            <div class="hms-card">
                <div class="hms-card__header">
                    <h5><i class="bi bi-card-image me-2"></i>About section</h5>
                    <a href="{{ route('clinics.show', $clinic) }}" class="btn btn-sm btn-outline-hms" target="_blank" rel="noopener">
                        <i class="bi bi-eye me-1"></i>Preview
                    </a>
                </div>
                <div class="hms-card__body">
                    <form method="POST" action="{{ route('clinic.about.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="about">About your clinic</label>
                            <textarea class="form-control @error('about') is-invalid @enderror"
                                      id="about" name="about" rows="7"
                                      placeholder="Describe your clinic, the facilities you offer and what patients can expect.">{{ old('about', $clinic->about) }}</textarea>
                            <div class="form-text">Shown at the top of your public clinic profile, above the doctor list.</div>
                            @error('about') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="banner_image">Banner image</label>
                            <input type="file" class="form-control @error('banner_image') is-invalid @enderror"
                                   id="banner_image" name="banner_image" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG or WEBP up to 4 MB. Recommended size 1600 &times; 600 pixels.</div>
                            @error('banner_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if ($clinic->banner_image)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Current banner</label>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $clinic->banner_url }}" alt="Current clinic banner"
                                         class="rounded border" style="max-height: 110px; object-fit: cover;">
                                    <label class="form-check small mb-0">
                                        <input class="form-check-input" type="checkbox" name="remove_banner" value="1">
                                        <span class="form-check-label">Remove this banner</span>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-hms">
                            <i class="bi bi-check2 me-1"></i>Save about section
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Live preview --------------------------------------------------- --}}
        <div class="col-xl-5">
            <div class="hms-card">
                <div class="hms-card__header">
                    <h5><i class="bi bi-eye me-2"></i>Live preview</h5>
                </div>
                <div class="hms-card__body">
                    <div class="hms-about-banner mb-3">
                        @if ($clinic->banner_url)
                            <img src="{{ $clinic->banner_url }}" alt="{{ $clinic->clinic_name }} banner">
                        @else
                            <div>
                                <i class="bi bi-image fs-2 d-block mb-2 opacity-75"></i>
                                <div class="fw-semibold">No banner uploaded yet</div>
                                <div class="small" style="opacity:.75">A branded gradient is shown until you upload one.</div>
                            </div>
                        @endif
                    </div>

                    <h3 class="h6 mb-1">{{ $clinic->clinic_name }}</h3>
                    <div class="text-muted small mb-2">
                        <i class="bi bi-geo-alt me-1"></i>{{ $clinic->area }}
                    </div>

                    @if ($clinic->about)
                        <p class="small mb-0" style="white-space: pre-line">{{ $clinic->about }}</p>
                    @else
                        <p class="text-muted small mb-0">Your description will appear here once you save it.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Doctors and their experience ------------------------------------- --}}
    <div class="hms-card mt-3">
        <div class="hms-card__header">
            <h5><i class="bi bi-person-badge me-2"></i>Doctors at your clinic ({{ $doctors->count() }})</h5>
            <span class="hms-chip"><i class="bi bi-info-circle"></i>Experience is set by the administrator</span>
        </div>
        <div class="hms-card__body hms-card__body--flush">
            @if ($doctors->isEmpty())
                <div class="hms-empty">
                    <i class="bi bi-calendar-plus"></i>
                    <p class="fw-semibold mb-1">No doctors are linked to your clinic yet.</p>
                    <p class="mb-3 small">Publish a schedule and the doctor will appear here with their experience.</p>
                    <a href="{{ route('clinic.schedules.index') }}" class="btn btn-sm btn-hms">Publish a schedule</a>
                </div>
            @else
                <div class="row g-3 p-3">
                    @foreach ($doctors as $doctor)
                        <div class="col-md-6 col-xxl-4">
                            <div class="hms-best">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="hms-best__avatar">{{ $doctor->initials }}</span>
                                    <div style="min-width:0">
                                        <h3 class="h6 mb-1 text-truncate">Dr. {{ $doctor->full_name }}</h3>
                                        <div class="text-muted small text-truncate">{{ $doctor->specialization }}</div>
                                    </div>
                                </div>

                                @if ($doctor->experience_label)
                                    <span class="hms-chip align-self-start">
                                        <i class="bi bi-award"></i>{{ $doctor->experience_label }}
                                    </span>
                                @endif

                                @if ($doctor->experience_note)
                                    <p class="small text-muted mb-0">{{ $doctor->experience_note }}</p>
                                @else
                                    <p class="small text-muted mb-0">No experience summary recorded yet.</p>
                                @endif

                                <div class="mt-auto small text-muted">
                                    <i class="bi bi-star-fill text-warning me-1"></i>
                                    {{ $doctor->public_reviews_count }} public rating{{ $doctor->public_reviews_count === 1 ? '' : 's' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection