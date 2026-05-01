<?php

namespace App\Filament\AdminPanel\Resources\Orders\Pages;

use App\Filament\AdminPanel\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
