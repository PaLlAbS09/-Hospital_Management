<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A patient's rating and written experience for one completed appointment.
 *
 * Columns: review_id, appointment_id (unique), patient_id, doctor_id, clinic_id,
 * rating (1-5), experience, is_public, created_at, updated_at
 */
class DoctorReview extends Model
{
    use HasFactory;

    public const MIN_RATING = 1;

    public const MAX_RATING = 5;

    protected $table = 'doctor_reviews';

    protected $primaryKey = 'review_id';

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'clinic_id',
        'rating',
        'experience',
        'is_public',
    ];

    protected $attributes = [
        'is_public' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Every selectable rating, used to populate the star inputs.
     *
     * @return array<int, int>
     */
    public static function ratingScale(): array
    {
        return range(self::MIN_RATING, self::MAX_RATING);
    }

    /**
     * Render an average as filled/empty stars, e.g. 4.3 -> "★★★★☆".
     *
     * Used by the clinic cards, where only the aggregate is available.
     */
    public static function starsFor(?float $average): string
    {
        $rounded = $average === null ? 0 : (int) round($average);

        return str_repeat('★', $rounded).str_repeat('☆', max(0, self::MAX_RATING - $rounded));
    }

    /* ---------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------- */

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    /* ---------------------------------------------------------------------
     | Accessors
     | ------------------------------------------------------------------- */

    public function getStarsAttribute(): string
    {
        return str_repeat('★', $this->rating).str_repeat('☆', self::MAX_RATING - $this->rating);
    }

    /* ---------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------- */

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeForClinic(Builder $query, int $clinicId): Builder
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('review_id');
    }
}
