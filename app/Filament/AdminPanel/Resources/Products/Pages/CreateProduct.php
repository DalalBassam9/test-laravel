<?php

namespace App\Filament\AdminPanel\Resources\Products\Pages;

use App\Filament\AdminPanel\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
