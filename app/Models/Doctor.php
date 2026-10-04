<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Notifications\Notifiable;

class Doctor extends AuthUser
{
    use HasFactory;
    use Notifiable;

    protected $table = 'doctors';

    protected $primaryKey = 'doctor_id';

    /** The `doctors` table only has a `created_at` column. */
    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    public $timestamps = true;

    protected $fillable = [
        'first_name',
        'last_name',
        'specialization',
        'email',
        'contact',
        'password',
        'experience_years',
        'experience_note',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'experience_years' => 'integer',
        ];
    }

    /* ---------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------- */

    public function schedules()
    {
        return $this->hasMany(ClinicSchedule::class, 'doctor_id', 'doctor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'doctor_id');
    }

    public function sessionLogs()
    {
        return $this->hasMany(DoctorSessionLog::class, 'doctor_id', 'doctor_id');
    }

    public function reviews()
    {
        return $this->hasMany(DoctorReview::class, 'doctor_id', 'doctor_id');
    }

    public function announcements()
    {
        return $this->hasMany(ClinicAnnouncement::class, 'doctor_id', 'doctor_id');
    }

    /* ---------------------------------------------------------------------
     | Accessors & helpers
     | ------------------------------------------------------------------- */

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(
            str($this->first_name)->substr(0, 1).str($this->last_name)->substr(0, 1)
        );
    }

    /** e.g. "12 years experience", or null when the admin left it blank. */
    public function getExperienceLabelAttribute(): ?string
    {
        return $this->experience_years
            ? $this->experience_years.' year'.($this->experience_years === 1 ? '' : 's').' experience'
            : null;
    }

    /** Attaches aggregate columns loaded by {@see withRatingSummary()}. */
    public function getRatingAverageAttribute(): ?float
    {
        $average = $this->attributes['rating_average'] ?? null;

        return $average !== null ? round((float) $average, 1) : null;
    }

    public function getRatingCountAttribute(): int
    {
        return (int) ($this->attributes['rating_count'] ?? 0);
    }

    /** Filled/empty stars for the rating summary, e.g. "★★★★☆". */
    public function getStarsAttribute(): string
    {
        $rating = $this->rating_average ?? 0;

        return str_repeat('★', (int) round($rating)).str_repeat('☆', DoctorReview::MAX_RATING - (int) round($rating));
    }

    /* ---------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------- */

    public function scopeSearch($query, ?string $term)
    {
        return $query->when(filled($term), function ($q) use ($term) {
            $q->where(function ($inner) use ($term) {
                $inner->where('first_name', 'like', '%'.$term.'%')
                    ->orWhere('last_name', 'like', '%'.$term.'%')
                    ->orWhere('specialization', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%');
            });
        });
    }

    /**
     * Attach `rating_average` / `rating_count` for the public "best doctor" slider.
     *
     * @param  int|null  $clinicId  Restrict the average to one clinic when given.
     */
    public function scopeWithRatingSummary(Builder $query, ?int $clinicId = null): Builder
    {
        $ratings = DoctorReview::query()
            ->selectRaw('doctor_id, AVG(rating) as rating_average, COUNT(*) as rating_count')
            ->where('is_public', true)
            ->when($clinicId, fn (Builder $inner) => $inner->where('clinic_id', $clinicId))
            ->groupBy('doctor_id');

        return $query
            ->leftJoinSub($ratings, 'rating_summary', 'rating_summary.doctor_id', 'doctors.doctor_id')
            ->addSelect(['doctors.*', 'rating_summary.rating_average', 'rating_summary.rating_count']);
    }

    /** Doctors that already have at least one public rating. */
    public function scopeRatedByPublic(Builder $query): Builder
    {
        return $query->whereIn(
            'doctor_id',
            DoctorReview::query()->public()->select('doctor_id')->distinct()
        );
    }
}
