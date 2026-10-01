<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Maps to the existing `doctor_session_logs` table.
 *
 * Columns: log_id, doctor_id, login_time, logout_time
 *
 * The table has no timestamp columns, therefore timestamps are disabled.
 */
class DoctorSessionLog extends Model
{
    protected $table = 'doctor_session_logs';

    protected $primaryKey = 'log_id';

    /** The `doctor_session_logs` table has no created_at / updated_at columns. */
    public $timestamps = false;

    protected $fillable = [
        'doctor_id',
        'login_time',
        'logout_time',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'login_time' => 'datetime',
            'logout_time' => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------- */

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'doctor_id');
    }

    /* ---------------------------------------------------------------------
     | Helpers
     | ------------------------------------------------------------------- */

    public function getIsOpenAttribute(): bool
    {
        return $this->logout_time === null;
    }

    /** Session length in whole minutes, or null while the session is open. */
    public function getDurationInMinutesAttribute(): ?int
    {
        if (! $this->login_time || ! $this->logout_time) {
            return null;
        }

        return (int) $this->login_time->diffInMinutes($this->logout_time);
    }

    public function getDurationForHumansAttribute(): string
    {
        $minutes = $this->duration_in_minutes;

        if ($minutes === null) {
            return 'Still logged in';
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $hours > 0
            ? sprintf('%dh %02dm', $hours, $rest)
            : sprintf('%dm', $rest);
    }

    /** Close an still-open session (idempotent). */
    public function close(): bool
    {
        if (! $this->is_open) {
            return false;
        }

        return $this->update(['logout_time' => now()]);
    }

    /* ---------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------- */

    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('logout_time');
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('login_time');
    }
}
