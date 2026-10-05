<?php

namespace App\Support;

/**
 * Builds the Google Maps links the application renders.
 *
 * When `GOOGLE_MAPS_API_KEY` is set the official Maps Embed API is used, which
 * is the supported integration. Without a key the app falls back to the
 * classic keyless embed, so a fresh checkout still shows a working map.
 */
class GoogleMaps
{
    public const DEFAULT_ZOOM = 15;

    public static function apiKey(): ?string
    {
        $key = config('services.google_maps.key');

        return filled($key) ? (string) $key : null;
    }

    public static function hasApiKey(): bool
    {
        return static::apiKey() !== null;
    }

    public static function embedUrl(string $query, int $zoom = self::DEFAULT_ZOOM): string
    {
        if (static::hasApiKey()) {
            return 'https://www.google.com/maps/embed/v1/place?'.http_build_query([
                'key' => static::apiKey(),
                'q' => $query,
                'zoom' => $zoom,
                'hl' => app()->getLocale(),
            ]);
        }

        return 'https://maps.google.com/maps?'.http_build_query([
            'q' => $query,
            'z' => $zoom,
            'hl' => app()->getLocale(),
            'output' => 'embed',
        ]);
    }

    /**
     * Turn-by-turn directions to a place.
     */
    public static function directionsUrl(string $query): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($query);
    }
}
