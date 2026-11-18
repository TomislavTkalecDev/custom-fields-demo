<?php

namespace App\CustomFields\Types;

use App\Models\CustomField;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class TextType extends BaseFieldType
{
    public function key(): string
    {
        return 'text';
    }

    public function label(): string
    {
        return 'Tekst';
    }

    public function icon(): string
    {
        return 'heroicon-o-bars-3-bottom-left';
    }

    public function storageColumn(): ?string
    {
        return 'value_string';
    }

    public function canBeTitle(): bool
    {
        return true;
    }

    public function settingsSchema(): array
    {
        return [
            TextInput::make('settings.maxlength')
                ->label('Maksimalna duljina')
                ->numeric()
                ->minValue(1)
                ->maxValue(255),

            TextInput::make('settings.placeholder')
                ->label('Placeholder')
                ->maxLength(100),
        ];
    }

    public function formComponent(CustomField $field): Component
    {
        return $this->applyCommon(
            $field,
            TextInput::make($this->statePath($field))
                ->maxLength($field->setting('maxlength', 255))
                ->placeholder($field->setting('placeholder'))
        );
    }

    public function tableColumn(CustomField $field): Column
    {
        return TextColumn::make($this->columnName($field))
            ->label($field->label)
            ->placeholder('—')
            ->wrap();
    }

    public function tableFilter(CustomField $field): ?BaseFilter
    {
        return Filter::make($this->columnName($field))
            ->form([
                TextInput::make('value')->label($field->label),
            ])
            ->query(function (Builder $query, array $data) use ($field): Builder {
                if (blank($data['value'] ?? null)) {
                    return $query;
                }

                return $query->whereHas('values', fn ($v) => $v
                    ->where('custom_field_id', $field->id)
                    ->where('value_string', 'like', '%' . $data['value'] . '%'));
            })
            ->indicateUsing(function (array $data) use ($field): ?string {
                return filled($data['value'] ?? null)
                    ? $field->label . ': ' . $data['value']
                    : null;
            });
    }

    public function validationRules(CustomField $field): array
    {
        return array_values(array_filter([
            $field->is_required ? 'required' : 'nullable',
            'string',
            'max:' . ($field->setting('maxlength') ?? 255),
        ]));
    }

    public function toStorage(mixed $value, CustomField $field): mixed
    {
        $value = is_string($value) ? trim($value) : $value;

        return blank($value) ? null : (string) $value;
    }
}