<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A discount offer published by a clinic (for example 15% off every medicine).
 *
 * Columns: offer_id, clinic_id, title, description, discount_percent,
 * valid_from, valid_until, is_active, created_at
 *
 * The table only has a `created_at` column, therefore timestamps are disabled.
 */
class ClinicOffer extends Model
{
    use HasFactory;

    protected $table = 'clinic_offers';

    protected $primaryKey = 'offer_id';

    /** The `clinic_offers` table has no created_at / updated_at columns. */
    public $timestamps = false;

    protected $fillable = [
        'clinic_id',
        'title',
        'description',
        'discount_percent',
        'valid_from',
        'valid_until',
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
            'discount_percent' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
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

    /* ---------------------------------------------------------------------
     | Accessors
     | ------------------------------------------------------------------- */

    /** e.g. "15% OFF" — used for the big number in the offer slide. */
    public function getDiscountLabelAttribute(): string
    {
        return $this->discount_percent.'% OFF';
    }

    /** Human readable validity window, or null when the offer is open-ended. */
    public function getValidityLabelAttribute(): ?string
    {
        if ($this->valid_from === null && $this->valid_until === null) {
            return null;
        }

        if ($this->valid_from !== null && $this->valid_until !== null) {
            return 'Valid '.$this->valid_from->format('d M Y').' - '.$this->valid_until->format('d M Y');
        }

        if ($this->valid_from !== null) {
            return 'Valid from '.$this->valid_from->format('d M Y');
        }

        return 'Valid until '.$this->valid_until->format('d M Y');
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until !== null && $this->valid_until->isPast();
    }

    /* ---------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Active offers that have not passed their end date yet. */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->active()
            ->where(function (Builder $inner) {
                $inner->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', today()->toDateString());
            });
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('offer_id');
    }
}
