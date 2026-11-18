<?php

namespace App\CustomFields\Services;

use App\CustomFields\FieldTypeRegistry;
use App\Models\CustomFieldValue;
use App\Models\CustomObject;
use App\Models\CustomRecord;
use Illuminate\Support\Collection;

class ValueReader
{
    public function __construct(private FieldTypeRegistry $registry) {}

    /** Vrijednosti jednog zapisa za popunjavanje forme. */
    public function forForm(CustomRecord $record): array
    {
        $values = $record->values()->get()->keyBy('custom_field_id');
        $out = [];

        foreach ($record->object->activeFields as $field) {
            $out[$field->key] = $this->registry
                ->get($field->type)
                ->fromStorage($values->get($field->id), $field);
        }

        return $out;
    }

    /**
     * Pivot. Vrijednosti iz redova postaju atributi cf_<key> na svakom zapisu.
     * Jedan upit za cijelu stranicu, neovisno o broju polja.
     */
    public function hydrate(Collection $records, CustomObject $object): Collection
    {
        if ($records->isEmpty()) {
            return $records;
        }

        $fields = $object->activeFields()->get();

        $values = CustomFieldValue::query()
            ->whereIn('custom_record_id', $records->pluck('id'))
            ->get()
            ->groupBy('custom_record_id');

        foreach ($records as $record) {
            $recordValues = ($values[$record->id] ?? collect())->keyBy('custom_field_id');

            foreach ($fields as $field) {
                $column = $this->registry->get($field->type)->storageColumn();

                $record->setAttribute(
                    "cf_{$field->key}",
                    $column ? $recordValues->get($field->id)?->{$column} : null
                );
            }
        }

        return $records;
    }
}