<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    protected $fillable = [
        'custom_field_id', 'custom_record_id',
        'value_string', 'value_text', 'value_number', 'value_boolean',
        'value_date', 'value_datetime', 'value_option_id', 'value_json',
    ];

    protected $casts = [
        'value_number'   => 'decimal:6',
        'value_boolean'  => 'boolean',
        'value_date'     => 'date',
        'value_datetime' => 'datetime',
        'value_json'     => 'array',
    ];

    public function field(): BelongsTo { return $this->belongsTo(CustomField::class, 'custom_field_id'); }
    public function record(): BelongsTo { return $this->belongsTo(CustomRecord::class, 'custom_record_id'); }
    public function option(): BelongsTo { return $this->belongsTo(CustomFieldOption::class, 'value_option_id'); }

    public function relations(): HasMany
    {
        return $this->hasMany(CustomFieldValueRelation::class)->orderBy('sort');
    }
}
