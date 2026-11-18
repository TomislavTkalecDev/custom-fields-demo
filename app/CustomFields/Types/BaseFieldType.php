<?php

namespace App\CustomFields\Types;

use App\CustomFields\Contracts\FieldType;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Filament\Schemas\Components\Component;
use Filament\Tables\Filters\BaseFilter;

abstract class BaseFieldType implements FieldType
{
    public function settingsSchema(): array
    {
        return [];
    }

    public function tableFilter(CustomField $field): ?BaseFilter
    {
        return null;
    }

    public function isSortable(): bool
    {
        return true;
    }

    public function canBeTitle(): bool
    {
        return false;
    }

    public function validationRules(CustomField $field): array
    {
        return [$field->is_required ? 'required' : 'nullable'];
    }

    public function toStorage(mixed $value, CustomField $field): mixed
    {
        return blank($value) ? null : $value;
    }

    public function fromStorage(?CustomFieldValue $value, CustomField $field): mixed
    {
        $column = $this->storageColumn();

        return $column ? $value?->{$column} : null;
    }

    /** Zajedničke postavke svakog polja u formi za unos. */
    protected function applyCommon(CustomField $field, Component $component): Component
    {
        return $component
            ->label($field->label)
            ->helperText($field->help_text)
            ->required($field->is_required);
    }

    /** Naziv atributa pod kojim polje živi u formi. */
    protected function statePath(CustomField $field): string
    {
        return "custom_fields.{$field->key}";
    }

    /** Naziv atributa pod kojim vrijednost živi na modelu nakon pivota. */
    protected function columnName(CustomField $field): string
    {
        return "cf_{$field->key}";
    }
}