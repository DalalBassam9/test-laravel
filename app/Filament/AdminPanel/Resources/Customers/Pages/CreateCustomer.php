<?php

namespace App\Filament\AdminPanel\Resources\Customers\Pages;

use App\Filament\AdminPanel\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
