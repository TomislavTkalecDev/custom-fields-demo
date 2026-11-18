<?php

namespace App\Filament\Cms\Resources\CustomObjects\Schemas;

use App\CustomFields\FieldTypeRegistry;
use App\Models\CustomObject;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CustomObjectForm
{
    public static function configure(Schema $schema): Schema
    {
return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Naziv')
                        ->required()
                        ->maxLength(100)
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            fn (?string $state, Set $set, string $operation) => $operation === 'create'
                                ? $set('key', Str::slug((string) $state, '_'))
                                : null
                        ),

                    TextInput::make('key')
                        ->label('Ključ')
                        ->required()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true)
                        ->rule('regex:/^[a-z][a-z0-9_]*$/')
                        ->disabledOn('edit')
                        ->dehydrated()
                        ->extraInputAttributes(['class' => 'font-mono'])
                        ->helperText('Jedinstveni identifikator. Nakon spremanja se ne može mijenjati.'),

                    Select::make('title_field_id')
                        ->label('Naslovno polje')
                        ->options(fn (?CustomObject $record) => $record
                            ? $record->fields()
                                ->whereIn('type', app(FieldTypeRegistry::class)->titleCapableKeys())
                                ->pluck('label', 'id')
                            : [])
                        ->native(false)
                        ->hiddenOn('create')
                        ->helperText('Polje koje predstavlja zapis u popisima. Bez njega se zapis prikazuje kao "Vozila #12".'),

                    Toggle::make('is_active')
                        ->label('Aktivan')
                        ->default(true)
                        ->helperText('Neaktivan objekt se ne prikazuje u CRM-u. Podaci ostaju sačuvani.')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
