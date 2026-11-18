<?php

namespace App\Filament\Cms\Resources\CustomObjects\Tables;

use App\Models\CustomObject;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomObjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('key')
                    ->label('Ključ')
                    ->fontFamily('mono')
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('fields_count')
                    ->counts('fields')
                    ->label('Broj polja')
                    ->alignEnd(),

                TextColumn::make('records_count')
                    ->counts('records')
                    ->label('Broj zapisa')
                    ->alignEnd(),

                IconColumn::make('is_active')
                    ->label('Aktivan')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Kreiran')
                    ->date('d.m.Y.')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Aktivan'),
                TrashedFilter::make()->label('Obrisani'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(fn (CustomObject $record) => "Obriši objekt {$record->title}?")
                    ->modalDescription(fn (CustomObject $record) => sprintf(
                        'Objekt ima %d zapisa i %d polja. Nestaje iz CRM navigacije, podaci ostaju sačuvani.',
                        $record->records()->count(),
                        $record->fields()->count(),
                    )),
                RestoreAction::make(),
            ])
            ->emptyStateHeading('Nema custom objekata')
            ->emptyStateDescription('Kreirajte prvi objekt da biste definirali novu evidenciju.')
            ->defaultSort('title');
    }
}