<?php

namespace App\Filament\AdminPanel\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('quantity')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_featured')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('max_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                // ← أضف هذا القسم
                Repeater::make('images')
                    ->relationship('images')
                    ->schema([
                        FileUpload::make('path')
                            ->image()
                            ->directory('products')
                            ->label('الصورة')
                            ->required(),
                        TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->label('الترتيب'),
                    ])
                    ->columns(2)
                    ->label('صور المنتج')
                    ->columnSpanFull(),
            ]);
          
    }
}
