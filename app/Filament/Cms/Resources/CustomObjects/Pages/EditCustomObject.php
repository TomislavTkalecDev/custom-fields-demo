<?php

namespace App\Filament\Cms\Resources\CustomObjects\Pages;

use App\Filament\Cms\Resources\CustomObjects\CustomObjectResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomObject extends EditRecord
{
    protected static string $resource = CustomObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
