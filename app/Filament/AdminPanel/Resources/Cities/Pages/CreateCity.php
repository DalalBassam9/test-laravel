<?php

namespace App\Filament\AdminPanel\Resources\Cities\Pages;

use App\Filament\AdminPanel\Resources\Cities\CityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCity extends CreateRecord
{
    protected static string $resource = CityResource::class;
}
