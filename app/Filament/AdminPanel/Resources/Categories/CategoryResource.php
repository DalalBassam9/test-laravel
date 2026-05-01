<?php

namespace App\Filament\AdminPanel\Resources\Categories;

use App\Filament\AdminPanel\Resources\Categories\Pages\CreateCategory;
use App\Filament\AdminPanel\Resources\Categories\Pages\EditCategory;
use App\Filament\AdminPanel\Resources\Categories\Pages\ListCategories;
use App\Filament\AdminPanel\Resources\Categories\Pages\ViewCategory;
use App\Filament\AdminPanel\Resources\Categories\Schemas\CategoryForm;
use App\Filament\AdminPanel\Resources\Categories\Schemas\CategoryInfolist;
use App\Filament\AdminPanel\Resources\Categories\Tables\CategoriesTable;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Category';

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'view' => ViewCategory::route('/{record}'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
