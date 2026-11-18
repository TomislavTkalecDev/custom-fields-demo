<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;          // ← ovo
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomObject extends Model
{
    use SoftDeletes;

    //protected $table = 'custom_object';
    protected $fillable = ['key', 'title', 'title_field_id', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function fields(): HasMany
    {
        return $this->hasMany(CustomField::class)->orderBy('sort');
    }

    public function activeFields(): HasMany
    {
        return $this->fields()->where('is_active', true);
    }

    public function records(): HasMany
    {
        return $this->hasMany(CustomRecord::class);
    }

    public function titleField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'title_field_id');
    }

    public function permissionNames(): array
    {
        return collect(['view', 'create', 'update', 'delete'])
            ->map(fn ($a) => "object.{$this->key}.{$a}")->all();
    }
}
