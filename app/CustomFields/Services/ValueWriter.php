<?php

namespace App\CustomFields\Services;

use App\CustomFields\FieldTypeRegistry;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\CustomRecord;
use Illuminate\Support\Facades\DB;

class ValueWriter
{
    public function __construct(private FieldTypeRegistry $registry) {}

    /** @param array<string, mixed> $payload ključ polja => vrijednost iz forme */
    public function write(CustomRecord $record, array $payload): void
    {
        DB::transaction(function () use ($record, $payload) {
            $fields = $record->object->activeFields()->get()->keyBy('key');

            foreach ($payload as $key => $raw) {
                if ($field = $fields->get($key)) {
                    $this->writeValue($record, $field, $raw);
                }
            }

            $record->forceFill(['updated_by' => auth()->id()])->save();
        });
    }

    private function writeValue(CustomRecord $record, CustomField $field, mixed $raw): void
    {
        $type   = $this->registry->get($field->type);
        $column = $type->storageColumn();
        $value  = $type->toStorage($raw, $field);

        $existing = CustomFieldValue::where('custom_field_id', $field->id)
            ->where('custom_record_id', $record->id);

        if (blank($value)) {
            $existing->delete();
            return;
        }

        CustomFieldValue::updateOrCreate(
            ['custom_field_id' => $field->id, 'custom_record_id' => $record->id],
            [$column => $value],
        );
    }
}