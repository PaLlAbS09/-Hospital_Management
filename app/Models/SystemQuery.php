<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Maps to the existing `system_queries` table.
 *
 * Columns: query_id, user_name, email, contact_number, message, submitted_at
 *
 * The table only tracks a submission date, so `UPDATED_AT` is disabled and
 * `submitted_at` is used as the creation timestamp.
 */
class SystemQuery extends Model
{
    protected $table = 'system_queries';

    protected $primaryKey = 'query_id';

    public const CREATED_AT = 'submitted_at';

    public const UPDATED_AT = null;

    public $timestamps = true;

    protected $fillable = [
        'user_name',
        'email',
        'contact_number',
        'message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    /* ---------------------------------------------------------------------
     | Helpers
     | ------------------------------------------------------------------- */

    public function getSubjectAttribute(): string
    {
        return str((string) $this->message)->squish()->limit(60)->toString();
    }

    public function getIsRecentAttribute(): bool
    {
        return $this->submitted_at !== null
            && $this->submitted_at->greaterThanOrEqualTo(now()->subDays(7));
    }

    /* ---------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------- */

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('submitted_at', '>=', now()->subDays($days));
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('submitted_at');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), function (Builder $q) use ($term) {
            $q->where(function (Builder $inner) use ($term) {
                $inner->where('user_name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('message', 'like', '%'.$term.'%');
            });
        });
    }
}
