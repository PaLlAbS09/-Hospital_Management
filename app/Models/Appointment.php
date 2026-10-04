<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Appointment extends Model
{
    use HasFactory;

    public static function hasDatabaseColumn(string $column): bool
    {
        try {
            return Schema::hasColumn('appointments', $column);
        } catch (\Throwable $exception) {
            return false;
        }
    }

    public const STATUS_ACTIVE = 'Active';

    public const STATUS_CANCELLED_BY_PATIENT = 'Cancelled_by_Patient';

    public const STATUS_CANCELLED_BY_DOCTOR = 'Cancelled_by_Doctor';

    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_NO_SHOW = 'No_Show';

    protected $table = 'appointments';

    protected $primaryKey = 'appointment_id';

    /** The `appointments` table has no created_at / updated_at columns. */
    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'doctor_id',
        'contact_phone',
        'appointment_date',
        'appointment_time',
        'status',
        'checked_in_at',
        'disease',
        'allergies',
        'prescription_details',
        'rating_request_sent_at',
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
            'checked_in_at' => 'datetime',
            'rating_request_sent_at' => 'datetime',
        ];
    }

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
            self::STATUS_NO_SHOW => 'No Show (auto-cancelled)',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function cancelledStatuses(): array
    {
        return [
            self::STATUS_CANCELLED_BY_PATIENT,
            self::STATUS_CANCELLED_BY_DOCTOR,
            self::STATUS_NO_SHOW,
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
            self::STATUS_NO_SHOW => 'dark',
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

    public function getIsCancelledAttribute(): bool
    {
        return in_array($this->status, self::cancelledStatuses(), true);
    }

    public function getIsCheckedInAttribute(): bool
    {
        return static::hasDatabaseColumn('checked_in_at') && filled($this->checked_in_at);
    }

    public function getHasPrescriptionAttribute(): bool
    {
        return filled($this->prescription_details)
            || filled($this->disease)
            || filled($this->allergies);
    }

    /**
     * A patient may only rate a session that actually finished, and only once.
     *
     * Cancelled and no-show appointments are excluded on purpose.
     */
    public function getIsReviewableAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /** Human readable appointment time (drops the seconds part). */
    public function getFormattedTimeAttribute(): string
    {
        return filled($this->appointment_time)
            ? substr((string) $this->appointment_time, 0, 5)
            : '--:--';
    }

    /**
     * Phone number captured with this booking, falling back to the number on the
     * patient's profile. Returns null when no number is available at all, which
     * keeps the SMS channel from firing for patients we cannot reach.
     */
    public function getNotificationPhoneAttribute(): ?string
    {
        $phone = static::hasDatabaseColumn('contact_phone')
            ? $this->contact_phone
            : null;

        if (blank($phone)) {
            $phone = $this->patient?->contact;
        }

        return filled($phone) ? (string) $phone : null;
    }

    /** Combined appointment start used for no-show detection. */
    public function getStartsAtAttribute(): ?CarbonInterface
    {
        if (! filled($this->appointment_date) || ! filled($this->appointment_time)) {
            return null;
        }

        try {
            return Carbon::parse(
                $this->appointment_date->toDateString().' '.substr((string) $this->appointment_time, 0, 8)
            );
        } catch (\Throwable $exception) {
            return null;
        }
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

    public function review()
    {
        return $this->hasOne(DoctorReview::class, 'appointment_id', 'appointment_id');
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

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->whereIn('status', self::cancelledStatuses());
    }

    public function scopeCheckedIn(Builder $query): Builder
    {
        if (! static::hasDatabaseColumn('checked_in_at')) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereNotNull('checked_in_at');
    }

    public function scopeNotCheckedIn(Builder $query): Builder
    {
        if (! static::hasDatabaseColumn('checked_in_at')) {
            return $query;
        }

        return $query->whereNull('checked_in_at');
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
