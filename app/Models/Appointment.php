<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Maps to the existing `appointments` table.
 *
 * Columns: appointment_id, patient_id, clinic_id, doctor_id,
 *          appointment_date, appointment_time, status, disease,
 *          allergies, prescription_details
 *
 * The table has no timestamp columns, therefore timestamps are disabled.
 */
class Appointment extends Model
{
    public const STATUS_ACTIVE = 'Active';

    public const STATUS_CANCELLED_BY_PATIENT = 'Cancelled_by_Patient';

    public const STATUS_CANCELLED_BY_DOCTOR = 'Cancelled_by_Doctor';

    public const STATUS_COMPLETED = 'Completed';

    protected $table = 'appointments';

    protected $primaryKey = 'appointment_id';

    /** The `appointments` table has no created_at / updated_at columns. */
    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'status',
        'disease',
        'allergies',
        'prescription_details',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    /* ---------------------------------------------------------------------
     | Status helpers
     | ------------------------------------------------------------------- */

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED_BY_PATIENT => 'Cancelled by Patient',
            self::STATUS_CANCELLED_BY_DOCTOR => 'Cancelled by Doctor',
        ];
    }

    /** Bootstrap 5 contextual colour for the current status. */
    public function getStatusVariantAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED_BY_PATIENT => 'warning',
            self::STATUS_CANCELLED_BY_DOCTOR => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? (string) $this->status;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getHasPrescriptionAttribute(): bool
    {
        return filled($this->prescription_details)
            || filled($this->disease)
            || filled($this->allergies);
    }

    /** Human readable appointment time (drops the seconds part). */
    public function getFormattedTimeAttribute(): string
    {
        return filled($this->appointment_time)
            ? substr((string) $this->appointment_time, 0, 5)
            : '--:--';
    }

    /* ---------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------- */

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    /* ---------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Filter by one of the appointment status values.
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when(filled($status), fn (Builder $q) => $q->where('status', $status));
    }

    /** An appointment consumes a clinic slot until it is cancelled. */
    public function scopeConsumingSlot(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_ACTIVE,
            self::STATUS_COMPLETED,
        ]);
    }

    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForClinic(Builder $query, int $clinicId): Builder
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('appointment_date', $date);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), function (Builder $q) use ($term) {
            $q->where(function (Builder $inner) use ($term) {
                $inner->where('disease', 'like', '%'.$term.'%')
                    ->orWhereHas('patient', fn (Builder $patient) => $patient
                        ->where('first_name', 'like', '%'.$term.'%')
                        ->orWhere('last_name', 'like', '%'.$term.'%'))
                    ->orWhereHas('doctor', fn (Builder $doctor) => $doctor
                        ->where('first_name', 'like', '%'.$term.'%')
                        ->orWhere('last_name', 'like', '%'.$term.'%'))
                    ->orWhereHas('clinic', fn (Builder $clinic) => $clinic
                        ->where('clinic_name', 'like', '%'.$term.'%'));
            });
        });
    }
}
