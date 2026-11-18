<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomField extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'custom_object_id', 'key', 'label', 'type', 'settings',
        'help_text', 'is_required', 'show_in_table', 'is_active', 'sort',
    ];

    protected $casts = [
        'settings'      => 'array',
        'is_required'   => 'boolean',
        'show_in_table' => 'boolean',
        'is_active'     => 'boolean',
    ];

    public function object(): BelongsTo
    {
        return $this->belongsTo(CustomObject::class, 'custom_object_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(CustomFieldOption::class)->orderBy('sort');
    }

    public function fieldType(): FieldType
    {
        return app(FieldTypeRegistry::class)->get($this->type);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function isRelationField(): bool
    {
        return in_array($this->type, ['select', 'multiselect'], true)
            && $this->setting('source') === 'object';
    }

    public function targetObject(): ?CustomObject
    {
        $id = $this->setting('target_object_id');
        return $id ? CustomObject::find($id) : null;
    }

    public function hasValues(): bool
    {
        return CustomFieldValue::where('custom_field_id', $this->id)->exists();
    }
}
