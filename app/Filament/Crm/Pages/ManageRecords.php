<?php

namespace App\Filament\Crm\Pages;

use App\CustomFields\FieldTypeRegistry;
use App\CustomFields\Services\QueryBuilder as CfQueryBuilder;
use App\CustomFields\Services\ValueReader;
use App\CustomFields\Services\ValueWriter;
use App\Models\CustomObject;
use App\Models\CustomRecord;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Panel;
use Illuminate\Contracts\Pagination\Paginator;

class ManageRecords extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected string $view = 'filament.crm.pages.manage-records';

    protected static bool $shouldRegisterNavigation = false;

    public CustomObject $object;

    public static function getRoutePath(Panel $panel): string
    {
        return '/objects/{objectKey}';
    }

    public function mount(string $objectKey): void
    {
        $this->object = CustomObject::query()
            ->where('key', $objectKey)
            ->where('is_active', true)
            ->with('activeFields')
            ->firstOrFail();
    }

    public function getTitle(): string
    {
        return $this->object->title;
    }

    public function table(Table $table): Table
    {
        $registry = app(FieldTypeRegistry::class);
        $object = $this->object;

        $tableFields = $object->activeFields->where('show_in_table', true);

        return $table
            ->query(CustomRecord::query()->where('custom_object_id', $object->id))

            ->columns([
                ...$tableFields
                    ->map(fn ($field) => $registry->get($field->type)->tableColumn($field))
                    ->values()
                    ->all(),

                TextColumn::make('created_at')
                    ->label('Kreirano')
                    ->dateTime('d.m.Y. H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters(
                $object->activeFields
                    ->map(fn ($field) => $registry->get($field->type)->tableFilter($field))
                    ->filter()
                    ->values()
                    ->all()
            )

            ->searchable()
            ->modifyQueryUsing(function (Builder $query) use ($object): Builder {
                $term = $this->getTableSearch();

                return filled($term)
                    ? CfQueryBuilder::applySearch($query, $object, $term)
                    : $query;
            })

            ->headerActions([
                CreateAction::make()
                    ->label('Novi zapis')
                    ->modalHeading("Novi zapis — {$object->title}")
                    ->modalWidth('xl')
                    ->model(CustomRecord::class)
                    ->schema($this->recordSchema())
                    ->using(function (array $data) use ($object): CustomRecord {
                        $record = CustomRecord::create([
                            'custom_object_id' => $object->id,
                            'created_by' => auth()->id(),
                        ]);

                        app(ValueWriter::class)->write($record, $data['custom_fields'] ?? []);

                        return $record;
                    })
            ])

            ->recordActions([
                EditAction::make()
                    ->modalWidth('xl')
                    ->schema($this->recordSchema())
                    ->fillForm(fn (CustomRecord $record) => [
                        'custom_fields' => app(ValueReader::class)->forForm($record),
                    ])
                    ->using(function (CustomRecord $record, array $data): CustomRecord {
                        app(ValueWriter::class)->write($record, $data['custom_fields'] ?? []);

                        return $record;
                    }),

                DeleteAction::make(),
            ])

            ->emptyStateHeading('Nema zapisa')
            ->emptyStateDescription("Dodajte prvi zapis u evidenciju {$object->title}.")
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    /** Forma generirana iz definicije polja. */
    protected function recordSchema(): array
    {
        $registry = app(FieldTypeRegistry::class);

        return $this->object->activeFields
            ->map(fn ($field) => $registry->get($field->type)->formComponent($field))
            ->values()
            ->all();
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        $perPage = $this->getTableRecordsPerPage();

        $paginator = $query->paginate(
            $perPage === 'all' ? $query->count() : $perPage,
            ['*'],
            $this->getTablePaginationPageName(),
        );

        app(ValueReader::class)->hydrate($paginator->getCollection(), $this->object);

        return $paginator;
    }
}