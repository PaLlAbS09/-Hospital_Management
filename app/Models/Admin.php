<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Notifications\Notifiable;

class Admin extends AuthUser
{
    use Notifiable;

    protected $table = 'admin';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getDisplayNameAttribute(): string
    {
        $name = $this->attributes['name'] ?? null;

        return filled($name)
            ? (string) $name
            : str((string) $this->email)->before('@')->headline()->toString();
    }

    public function getNameAttribute($value): string
    {
        return filled($value) ? (string) $value : $this->getDisplayNameAttribute();
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->getDisplayNameAttribute())) ?: [];

        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'AD';
    }
}
