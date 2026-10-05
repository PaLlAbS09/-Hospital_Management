@extends('layouts.app')

@section('title', 'Clinic details')
@section('subtitle', 'Name, contact number and the address your map pin points to')

@section('content')
    <div class="row g-3">
        {{-- Editor --------------------------------------------------------- --}}
        <div class="col-xl-7">
            <div class="hms-card">
                <div class="hms-card__header">
                    <h5><i class="bi bi-building me-2"></i>Update your details</h5>
                    <a href="{{ route('clinics.show', $clinic) }}" class="btn btn-sm btn-outline-hms" target="_blank" rel="noopener">
                        <i class="bi bi-eye me-1"></i>Preview
                    </a>
                </div>
                <div class="hms-card__body">
                    <form method="POST" action="{{ route('clinic.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="clinic_name">Clinic name</label>
                            <input type="text" class="form-control @error('clinic_name') is-invalid @enderror"
                                   id="clinic_name" name="clinic_name" maxlength="100"
                                   value="{{ old('clinic_name', $clinic->clinic_name) }}" required>
                            @error('clinic_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="area">Area</label>
                                <input type="text" class="form-control @error('area') is-invalid @enderror"
                                       id="area" name="area" maxlength="100"
                                       value="{{ old('area', $clinic->area) }}" list="area-options" required>
                                <datalist id="area-options">
                                    @foreach (\App\Models\Clinic::query()->approved()->distinct()->orderBy('area')->pluck('area') as $option)
                                        <option value="{{ $option }}"></option>
                                    @endforeach
                                </datalist>
                                <div class="form-text">Patients search clinics by this area.</div>
                                @error('area') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="contact_number">Contact number</label>
                                <input type="text" inputmode="numeric"
                                       class="form-control @error('contact_number') is-invalid @enderror"
                                       id="contact_number" name="contact_number" maxlength="15"
                                       value="{{ old('contact_number', $clinic->contact_number) }}" required>
                                <div class="form-text">7 to 15 digits, no spaces.</div>
                                @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label fw-semibold" for="email">Login email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" maxlength="100"
                                   value="{{ old('email', $clinic->email) }}">
                            <div class="form-text">
                                Leave it as it is to keep signing in with the same address. Changing it means
                                signing in with the new one next time.
                            </div>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="address">Full address</label>
                            <input type="text" class="form-control @error('address') is-invalid @enderror"
                                   id="address" name="address" maxlength="255"
                                   value="{{ old('address', $clinic->address) }}"
                                   placeholder="Rakhal Pirtala, Purba Bardhaman">
                            <div class="form-text">
                                Shown to patients, and the address your map marker points to.
                            </div>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Exact coordinates <span class="text-muted fw-normal small">(optional)</span>
                            </label>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <input type="number" step="any"
                                           class="form-control @error('latitude') is-invalid @enderror"
                                           id="latitude" name="latitude"
                                           value="{{ old('latitude', $clinic->latitude) }}"
                                           placeholder="23.232403">
                                    <div class="form-text">Latitude</div>
                                    @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-sm-6">
                                    <input type="number" step="any"
                                           class="form-control @error('longitude') is-invalid @enderror"
                                           id="longitude" name="longitude"
                                           value="{{ old('longitude', $clinic->longitude) }}"
                                           placeholder="87.861506">
                                    <div class="form-text">Longitude</div>
                                    @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="form-text">
                                Leave both blank and Google Maps finds your marker from the address above.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-hms">
                            <i class="bi bi-check2 me-1"></i>Save details
                        </button>
                    </form>
                </div>
            </div>
        </div>
        {{-- Live preview ----------------------------------------------------- --}}
        <div class="col-xl-5">
            <div class="hms-card">
                <div class="hms-card__header">
                    <h5><i class="bi bi-map me-2"></i>Where patients will find you</h5>
                </div>
                <div class="hms-card__body">
                    @php
                        $previewAddress = old('address', $clinic->address);
                        $previewArea = old('area', $clinic->area);
                        $previewFull = collect([$previewAddress, $previewArea])
                            ->map(fn (?string $part) => filled($part) ? trim($part) : null)
                            ->filter()
                            ->unique()
                            ->implode(', ');

                        $previewQuery = filled(old('latitude'))
                            ? old('latitude').','.old('longitude')
                            : $previewFull;
                    @endphp

                    @if (filled($previewQuery))
                        <div class="hms-map__frame mb-3" style="height: 240px">
                            <iframe src="{{ \App\Support\GoogleMaps::embedUrl($previewQuery, 15) }}"
                                    title="{{ old('clinic_name', $clinic->clinic_name) }} map preview"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    allowfullscreen></iframe>
                        </div>
                    @endif

                    <h3 class="h6 mb-2">{{ old('clinic_name', $clinic->clinic_name) }}</h3>

                    <ul class="list-unstyled small mb-0">
                        <li class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-geo-alt-fill text-primary"></i>
                            <span>{{ $previewFull !== '' ? $previewFull : 'No address saved yet' }}</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-telephone-fill text-primary"></i>
                            <span>{{ old('contact_number', $clinic->contact_number) }}</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-envelope-fill text-primary"></i>
                            <span class="text-truncate">{{ old('email', $clinic->email) }}</span>
                        </li>
                    </ul>

                    <p class="small text-muted mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Your approval status is set by the administrator and cannot be changed here.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
