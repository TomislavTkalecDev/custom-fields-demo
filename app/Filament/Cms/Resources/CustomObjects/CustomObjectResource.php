<?php

namespace App\Filament\Cms\Resources\CustomObjects;

use App\Filament\Cms\Resources\CustomObjects\Pages\CreateCustomObject;
use App\Filament\Cms\Resources\CustomObjects\Pages\EditCustomObject;
use App\Filament\Cms\Resources\CustomObjects\Pages\ListCustomObjects;
use App\Filament\Cms\Resources\CustomObjects\Schemas\CustomObjectForm;
use App\Filament\Cms\Resources\CustomObjects\Tables\CustomObjectsTable;
use App\Models\CustomObject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Cms\Resources\CustomObjects\RelationManagers\FieldsRelationManager;

class CustomObjectResource extends Resource
{
    protected static ?string $model = CustomObject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return CustomObjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomObjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            FieldsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomObjects::route('/'),
            'create' => CreateCustomObject::route('/create'),
            'edit' => EditCustomObject::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
