@props([
    'clinic',
    'heading' => 'Clinic location',
    'zoom' => 16,
    'height' => '300px',
    'profileRoute' => 'patient.clinics.show',
])

@if ($clinic->map_query)
    <section {{ $attributes->merge(['class' => 'hms-map']) }}>
        <div class="hms-map__frame" style="height: {{ $height }}">
            <iframe src="{{ \App\Support\GoogleMaps::embedUrl($clinic->map_query, $zoom) }}"
                    title="{{ $heading }}"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen></iframe>
        </div>

        <ul class="hms-map__list">
            <li class="hms-map__item">
                <i class="bi bi-geo-alt-fill"></i>

                <div class="hms-map__text">
                    <a href="{{ route($profileRoute, $clinic) }}" class="hms-map__name">{{ $clinic->clinic_name }}</a>
                    <span class="hms-map__address">{{ $clinic->full_address }}</span>
                </div>

                <a href="{{ $clinic->directions_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-hms hms-map__link">
                    <i class="bi bi-sign-turn-right-fill me-1"></i>Directions
                </a>
            </li>
        </ul>
    </section>
@endif