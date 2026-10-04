<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A clinic-published promotion announcing a doctor who is joining the centre.
 *
 * Columns: announcement_id, clinic_id, doctor_id, department, joining_date,
 * joining_time, message, is_active, created_at
 *
 * The table only has a `created_at` column, therefore timestamps are disabled.
 */
class ClinicAnnouncement extends Model
{
    use HasFactory;

    protected $table = 'clinic_announcements';

    protected $primaryKey = 'announcement_id';

    /** The `clinic_announcements` table has no created_at / updated_at columns. */
    public $timestamps = false;

    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'department',
        'joining_date',
        'joining_time',
        'message',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'joining_time' => 'datetime:H:i',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------- */

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    /* ---------------------------------------------------------------------
     | Accessors
     | ------------------------------------------------------------------- */

    /** Times come back from MySQL as "09:30:00"; the slider only needs "09:30". */
    public function getFormattedJoiningTimeAttribute(): ?string
    {
        // Read the raw attribute: a mutator must never read the property it
        // defines, otherwise Eloquent resolves it recursively.
        $time = $this->attributes['joining_time'] ?? null;

        if (! filled($time)) {
            return null;
        }

        return substr((string) $time, 0, 5);
    }

    public function getIsUpcomingAttribute(): bool
    {
        $date = $this->joining_date;

        return $date !== null && $date->greaterThanOrEqualTo(today()->startOfDay());
    }

    /* ---------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Nearest joining date first, with undated announcements last.
     *
     * Named consistently with ClinicOffer::scopeLatestFirst() and
     * DoctorReview::scopeLatestFirst().
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByRaw('joining_date IS NULL, joining_date ASC')->orderByDesc('announcement_id');
    }
}
