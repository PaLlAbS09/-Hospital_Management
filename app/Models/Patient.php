<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as AuthUser;

class Patient extends AuthUser
{
    protected $table = 'patients';

    protected $primaryKey = 'patient_id';

    /** The `patients` table only has a `created_at` column. */
    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    public $timestamps = true;

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
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

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'patient_id');
    }

    public function activeAppointments()
    {
        return $this->appointments()->where('status', Appointment::STATUS_ACTIVE);
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
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('contact', 'like', '%'.$term.'%');
            });
        });
    }
}