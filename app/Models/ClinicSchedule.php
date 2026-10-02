<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicSchedule extends Model
{
    use HasFactory;

    public const SLOT_MINUTES = 30;

    protected $table = 'clinic_schedules';

    protected $primaryKey = 'schedule_id';

    public $timestamps = false;

    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'schedule_date',
        'start_time',
        'end_time',
        'patient_capacity',
    ];

    protected $attributes = [
        'patient_capacity' => 20,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'patient_capacity' => 'integer',
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

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'clinic_id', 'clinic_id')
            ->where('doctor_id', $this->doctor_id)
            ->whereDate('appointment_date', $this->schedule_date);
    }

    /* ---------------------------------------------------------------------
     | Helpers
     | ------------------------------------------------------------------- */

    /** Normalise MySQL TIME values ("09:30:00" / "09:30") into "H:i". */
    public static function normalizeTime(mixed $time): ?string
    {
        if (! filled($time)) {
            return null;
        }

        if ($time instanceof Carbon) {
            return $time->format('H:i');
        }

        $timeString = trim((string) $time);

        // If a full datetime slipped in (e.g. "2026-10-02 12:00:00"), keep only the time part
        // so Carbon never sees a double time specification.
        if (strlen($timeString) > 8 && preg_match('/(\d{1,2}:\d{2}(?::\d{2})?)\s*$/', $timeString, $matches) === 1) {
            $timeString = $matches[1];
        } else {
            $timeString = substr($timeString, 0, 5);
        }

        return Carbon::parse($timeString)->format('H:i');
    }

    /** Build "Y-m-d H:i:s" safely even when Eloquent casts give us Carbon instances. */
    private function buildDateTime(mixed $date, mixed $time): Carbon
    {
        $dateString = $date instanceof Carbon
            ? $date->toDateString()
            : substr(trim((string) $date), 0, 10);

        if ($time instanceof Carbon) {
            $timeString = $time->format('H:i:s');
        } else {
            $timeString = trim((string) $time);
            // If a full datetime slipped in (e.g. "2026-10-02 12:00:00"), keep only the time part.
            if (strlen($timeString) > 8 && preg_match('/(\d{1,2}:\d{2}(?::\d{2})?)\s*$/', $timeString, $matches) === 1) {
                $timeString = $matches[1];
            }
        }

        return Carbon::parse($dateString.' '.$timeString);
    }

    public function getStartAtAttribute(): ?Carbon
    {
        if (! filled($this->start_time) || ! filled($this->schedule_date)) {
            return null;
        }

        return $this->buildDateTime($this->schedule_date, $this->start_time);
    }

    public function getEndAtAttribute(): ?Carbon
    {
        if (! filled($this->end_time) || ! filled($this->schedule_date)) {
            return null;
        }

        return $this->buildDateTime($this->schedule_date, $this->end_time);
    }

    public function getTimeRangeAttribute(): string
    {
        return self::normalizeTime($this->start_time).' - '.self::normalizeTime($this->end_time);
    }

    public function getIsPastAttribute(): bool
    {
        $end = $this->end_at;

        return $end !== null && $end->isPast();
    }

    /* ---------------------------------------------------------------------
     | Slot helpers
     | ------------------------------------------------------------------- */

    /**
     * Every bookable time slot for this schedule.
     *
     * @return array<int, string> Times formatted as "H:i".
     */
    public function slotTimes(): array
    {
        $start = $this->start_at;
        $end = $this->end_at;

        if (! $start || ! $end || $end->lessThanOrEqualTo($start)) {
            return [];
        }

        $slots = [];

        for ($slot = $start->copy(); $slot->lessThan($end); $slot->addMinutes(self::SLOT_MINUTES)) {
            $slots[] = $slot->format('H:i');
        }

        return $slots;
    }

    /** Appointments that currently occupy a slot for this schedule. */
    public function slotConsumingAppointments(): Builder
    {
        return Appointment::query()
            ->consumingSlot()
            ->where('clinic_id', $this->clinic_id)
            ->where('doctor_id', $this->doctor_id)
            ->whereDate('appointment_date', $this->schedule_date);
    }

    public function bookedCount(): int
    {
        return $this->slotConsumingAppointments()->count();
    }

    public function remainingCapacity(): int
    {
        return max(0, (int) $this->patient_capacity - $this->bookedCount());
    }

    public function getIsFullAttribute(): bool
    {
        return $this->remainingCapacity() === 0;
    }

    /**
     * Bookable slots with their occupancy (single query per schedule).
     *
     * @return array<int, array{time: string, booked: int, remaining: int, available: bool}>
     */
    public function slotAvailability(): array
    {
        $taken = $this->slotConsumingAppointments()
            ->get(['appointment_time'])
            ->groupBy(fn (Appointment $appointment) => substr((string) $appointment->appointment_time, 0, 5))
            ->map->count();

        $bookedTotal = (int) $taken->sum();
        $capacity = (int) $this->patient_capacity;
        $remaining = max(0, $capacity - $bookedTotal);

        return collect($this->slotTimes())
            ->map(fn (string $time) => [
                'time' => $time,
                'booked' => (int) $taken->get($time, 0),
                'remaining' => $remaining,
                'available' => ((int) $taken->get($time, 0) === 0) && $remaining > 0,
            ])
            ->all();
    }

    /* ---------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------- */

    public function scopeForClinic(Builder $query, int $clinicId): Builder
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('schedule_date', $date);
    }

    public function scopeOnOrAfter(Builder $query, string $date): Builder
    {
        return $query->whereDate('schedule_date', '>=', $date);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('schedule_date')->orderBy('start_time');
    }
}
