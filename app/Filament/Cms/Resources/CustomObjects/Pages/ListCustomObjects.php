<?php

namespace App\Filament\Cms\Resources\CustomObjects\Pages;

use App\Filament\Cms\Resources\CustomObjects\CustomObjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomObjects extends ListRecords
{
    protected static string $resource = CustomObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
