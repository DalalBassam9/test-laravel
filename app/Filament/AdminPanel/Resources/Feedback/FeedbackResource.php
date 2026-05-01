<?php

namespace App\Filament\AdminPanel\Resources\Feedback;

use App\Filament\AdminPanel\Resources\Feedback\Pages\EditFeedback;
use App\Filament\AdminPanel\Resources\Feedback\Pages\ViewFeedback;
use App\Filament\AdminPanel\Resources\Feedback\Schemas\FeedbackForm;
use App\Filament\AdminPanel\Resources\Feedback\Schemas\FeedbackInfolist;
use App\Filament\AdminPanel\Resources\Feedback\Tables\FeedbackTable;
use App\Models\Feedback;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Actions\DeleteAction;
use App\Filament\AdminPanel\Resources\Feedback\Pages\ListFeedback;
use App\Filament\AdminPanel\Resources\Feedback\Pages\CreateFeedback;
use Filament\Support\Icons\Heroicon;
class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $recordTitleAttribute = 'Feedback';
    protected static ?string $navigationLabel = 'اللقطات';
    protected static ?string $modelLabel = 'لقطة';
    protected static ?string $pluralModelLabel = 'اللقطات';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            FileUpload::make('screenshot_path')
                ->label('صورة اللقطة')
                ->image()
                ->maxSize(2048) // 2MB
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                ImageColumn::make('screenshot_path')
                    ->label('الصورة')
                    ->height(80)
                    ->width(80)
                    ->square(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d M Y - H:i')
                    ->sortable(),
            ])
            ->actions([
                DeleteAction::make()
                    ->label('حذف'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
        'index'  => ListFeedback::route('/'),
        'create' => CreateFeedback::route('/create'),
    ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return FeedbackInfolist::configure($schema);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }


}
