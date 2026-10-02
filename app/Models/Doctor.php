<?php

namespace App\Models;

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
}
