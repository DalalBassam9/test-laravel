<?php

namespace App\Filament\AdminPanel\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('address_id')
                    ->numeric()
                    ->default(null),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'placed' => 'Placed',
            'confirmed' => 'Confirmed',
            'shipped' => 'Shipped',
            'cancelled' => 'Cancelled',
            'forDelivery' => 'For delivery',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
        ])
                    ->default('pending')
                    ->required(),
                TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('delivery_fee')
                    ->required()
                    ->numeric()
                    ->default(15.0),
                Select::make('payment_method')
                    ->options(['cash' => 'Cash', 'click' => 'Click'])
                    ->required(),
                TextInput::make('click_id')
                    ->default(null),
            ]);
    }
}
