<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldValueRelation extends Model
{
    protected $fillable = [
        'custom_field_value_id',
        'target_record_id',
        'sort',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    /** Vrijednost koja nosi ovu vezu — preko nje se dolazi do izvornog zapisa. */
    public function value(): BelongsTo
    {
        return $this->belongsTo(CustomFieldValue::class, 'custom_field_value_id');
    }

    /** Zapis na koji veza pokazuje. */
    public function targetRecord(): BelongsTo
    {
        return $this->belongsTo(CustomRecord::class, 'target_record_id');
    }

    /** Zapis s kojeg veza polazi. Prečac kroz vrijednost. */
    public function sourceRecord(): BelongsTo
    {
        return $this->value->record();
    }
}
