<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomRecord extends Model
{
    use SoftDeletes;

    protected $fillable = ['custom_object_id', 'created_by', 'updated_by'];

    public function object(): BelongsTo
    {
        return $this->belongsTo(CustomObject::class, 'custom_object_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    /** Veze gdje je OVAJ zapis cilj — obrnuti pregled. */
    public function incomingRelations(): HasMany
    {
        return $this->hasMany(CustomFieldValueRelation::class, 'target_record_id');
    }

    public function getTitleAttribute(): string
    {
        $fieldId = $this->object?->title_field_id;

        if ($fieldId) {
            $value = $this->values->firstWhere('custom_field_id', $fieldId);
            $resolved = $value?->value_string ?? $value?->value_number;
            if (filled($resolved)) {
                return (string) $resolved;
            }
        }

        return ($this->object?->title ?? 'Zapis') . ' #' . $this->id;
    }
}
