<?php

namespace App\Models;

use App\Support\GoogleMaps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Clinic extends AuthUser
{
    use HasFactory;
    use Notifiable;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $table = 'clinics';

    protected $primaryKey = 'clinic_id';

    /** The `clinics` table only has a `created_at` column. */
    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    public $timestamps = true;

    protected $fillable = [
        'clinic_name',
        'area',
        'address',
        'latitude',
        'longitude',
        'email',
        'password',
        'contact_number',
        'status',
        'about',
        'banner_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /* ---------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------- */

    public function schedules()
    {
        return $this->hasMany(ClinicSchedule::class, 'clinic_id', 'clinic_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'clinic_id', 'clinic_id');
    }

    public function announcements()
    {
        return $this->hasMany(ClinicAnnouncement::class, 'clinic_id', 'clinic_id');
    }

    public function offers()
    {
        return $this->hasMany(ClinicOffer::class, 'clinic_id', 'clinic_id');
    }

    public function reviews()
    {
        return $this->hasMany(DoctorReview::class, 'clinic_id', 'clinic_id');
    }

    /** Distinct doctors this clinic has ever published a schedule for. */
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'clinic_schedules', 'clinic_id', 'doctor_id');
    }

    /* ---------------------------------------------------------------------
     | Accessors & helpers
     | ------------------------------------------------------------------- */

    public function getDisplayNameAttribute(): string
    {
        return $this->clinic_name;
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Two letter initials used by the avatar component.
     */
    public function getInitialsAttribute(): string
    {
        $initials = collect(preg_split('/\s+/', trim((string) $this->clinic_name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'CL';
    }

    /** True once the clinic has written about text or uploaded a banner. */
    public function getHasAboutSectionAttribute(): bool
    {
        return filled($this->about) || filled($this->banner_image);
    }

    /** Fallback gradient for clinics that never uploaded a banner image. */
    public function getBannerUrlAttribute(): ?string
    {
        return filled($this->banner_image)
            ? Str::startsWith($this->banner_image, ['http://', 'https://', '/'])
                ? $this->banner_image
                : asset('storage/'.$this->banner_image)
            : null;
    }

    /** Street address and area on one line, e.g. "Rakhal Pirtala, Burdwan". */
    public function getFullAddressAttribute(): string
    {
        return collect([$this->address, $this->area])
            ->map(fn (?string $part) => filled($part) ? trim($part) : null)
            ->filter()
            ->unique()
            ->implode(', ');
    }

    /** True when the clinic pinned its exact position instead of a place name. */
    public function getHasCoordinatesAttribute(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * What Google Maps is asked for: coordinates when known, otherwise the
     * address a patient reads, otherwise null when the clinic cannot be found.
     */
    public function getMapQueryAttribute(): ?string
    {
        if ($this->has_coordinates) {
            return $this->latitude.','.$this->longitude;
        }

        return filled($this->full_address) ? $this->full_address : null;
    }

    public function getDirectionsUrlAttribute(): ?string
    {
        return $this->map_query ? GoogleMaps::directionsUrl($this->map_query) : null;
    }

    /* ---------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------- */

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeInArea($query, ?string $area)
    {
        return $query->when(
            filled($area),
            fn ($q) => $q->where('area', 'like', '%'.$area.'%')
        );
    }
}
