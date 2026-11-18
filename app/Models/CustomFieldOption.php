<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldOption extends Model
{
    protected $fillable = [
        'custom_field_id',
        'value',
        'label',
        'color',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'sort'      => 'integer',
        'is_active' => 'boolean',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }

    /** Vrijednosti koje su odabrale ovu opciju. */
    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class, 'value_option_id');
    }

    /** Koliko zapisa koristi ovu opciju — za potvrdu prije gašenja. */
    public function usageCount(): int
    {
        return $this->values()->count();
    }
}
