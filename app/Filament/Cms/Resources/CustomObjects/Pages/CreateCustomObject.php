<?php

namespace App\Filament\Cms\Resources\CustomObjects\Pages;

use App\Filament\Cms\Resources\CustomObjects\CustomObjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomObject extends CreateRecord
{
    protected static string $resource = CustomObjectResource::class;
}
