<?php

namespace App\CustomFields\Services;

use App\CustomFields\FieldTypeRegistry;
use App\Models\CustomField;
use App\Models\CustomObject;
use Illuminate\Database\Eloquent\Builder;

class QueryBuilder
{
    public static function applySort(Builder $query, CustomField $field, string $direction): Builder
    {
        $column = app(FieldTypeRegistry::class)->get($field->type)->storageColumn();

        if (! $column) {
            return $query;
        }

        $alias = "sort_{$field->id}";

        return $query
            ->leftJoin("custom_field_values as {$alias}", function ($join) use ($alias, $field) {
                $join->on("{$alias}.custom_record_id", '=', 'custom_records.id')
                     ->where("{$alias}.custom_field_id", '=', $field->id);
            })
            ->orderBy("{$alias}.{$column}", $direction)
            ->select('custom_records.*');
    }

    public static function applySearch(Builder $query, CustomObject $object, string $term): Builder
    {
        $fieldIds = $object->activeFields()
            ->whereIn('type', ['text', 'textarea', 'email', 'url'])
            ->pluck('id');

        if ($fieldIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('values', fn ($v) => $v
            ->whereIn('custom_field_id', $fieldIds)
            ->where(fn ($q) => $q
                ->where('value_string', 'like', "%{$term}%")
                ->orWhere('value_text', 'like', "%{$term}%")));
    }
}