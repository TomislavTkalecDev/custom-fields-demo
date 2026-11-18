<?php

namespace App\Filament\Cms\Resources\CustomObjects\RelationManagers;

use App\CustomFields\FieldTypeRegistry;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    protected static ?string $title = 'Polja';

    protected static ?string $modelLabel = 'polje';

    protected static ?string $pluralModelLabel = 'polja';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Osnovno')
                ->columns(2)
                ->schema([
                    Select::make('type')
                        ->label('Vrsta polja')
                        ->options(app(FieldTypeRegistry::class)->options())
                        ->required()
                        ->live()
                        ->native(false)
                        ->disabled(fn (?CustomField $record) => $record?->hasValues() ?? false)
                        ->helperText(fn (?CustomField $record) => ($record?->hasValues() ?? false)
                            ? 'Vrsta se ne može promijeniti jer postoje unesene vrijednosti.'
                            : null),

                    TextInput::make('label')
                        ->label('Labela')
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
                        ->rule('regex:/^[a-z][a-z0-9_]*$/')
                        ->disabledOn('edit')
                        ->dehydrated()
                        ->extraInputAttributes(['class' => 'font-mono'])
                        ->helperText('Nakon spremanja se ne može mijenjati.')
                        ->rules([
                            fn (?CustomField $record) => function (string $attribute, $value, callable $fail) use ($record) {
                                $exists = CustomField::withTrashed()
                                    ->where('custom_object_id', $this->getOwnerRecord()->id)
                                    ->where('key', $value)
                                    ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                                    ->exists();

                                if ($exists) {
                                    $fail('Polje s tim ključem već postoji na ovom objektu.');
                                }
                            },
                        ]),

                    TextInput::make('help_text')
                        ->label('Pomoćni tekst')
                        ->maxLength(255)
                        ->helperText('Prikazuje se ispod polja u formi za unos.'),
                ]),

            Section::make('Postavke')
                ->columns(2)
                ->schema(fn (Get $get): array => filled($get('type'))
                    ? app(FieldTypeRegistry::class)->get($get('type'))->settingsSchema()
                    : [])
                ->visible(fn (Get $get): bool => filled($get('type'))
                    && count(app(FieldTypeRegistry::class)->get($get('type'))->settingsSchema()) > 0),

            Section::make('Ponašanje')
                ->columns(3)
                ->schema([
                    Toggle::make('is_required')
                        ->label('Obavezno')
                        ->helperText('Vrijedi od sljedećeg spremanja.'),

                    Toggle::make('show_in_table')
                        ->label('Prikaži u tablici')
                        ->default(true),

                    Toggle::make('is_active')
                        ->label('Aktivno')
                        ->default(true),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        $registry = app(FieldTypeRegistry::class);

        return $table
            ->recordTitleAttribute('label')
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('label')
                    ->label('Labela')
                    ->weight('medium')
                    ->description(fn (CustomField $record) => $record->help_text),

                TextColumn::make('key')
                    ->label('Ključ')
                    ->fontFamily('mono')
                    ->color('gray'),

                TextColumn::make('type')
                    ->label('Vrsta')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $registry->has($state)
                        ? $registry->get($state)->label()
                        : $state),

                IconColumn::make('is_required')->label('Obavezno')->boolean(),
                IconColumn::make('show_in_table')->label('U tablici')->boolean(),
                IconColumn::make('is_active')->label('Aktivno')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Dodaj polje')
                    ->modalWidth('2xl')
                    ->mutateDataUsing(function (array $data): array {
                        $data['sort'] = (int) CustomField::where('custom_object_id', $this->getOwnerRecord()->id)
                            ->max('sort') + 1;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()->modalWidth('2xl'),

                DeleteAction::make()
                    ->modalHeading(fn (CustomField $record) => "Obriši polje {$record->label}?")
                    ->modalDescription(fn (CustomField $record) => sprintf(
                        'Polje nestaje iz forme i tablice. Vrijednosti na %d zapisa ostaju sačuvane i vratit će se ako polje obnovite.',
                        CustomFieldValue::where('custom_field_id', $record->id)->count(),
                    ))
                    ->modalSubmitActionLabel('Obriši polje'),
            ])
            ->emptyStateHeading('Nema definiranih polja')
            ->emptyStateDescription('Dodajte prvo polje kako bi se objekt mogao koristiti u CRM-u.');
    }
}