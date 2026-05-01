<?php

namespace App\Filament\AdminPanel\Resources\Feedback\Pages;

use App\Filament\AdminPanel\Resources\Feedback\FeedbackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedback extends ListRecords
{
    protected static string $resource = FeedbackResource::class;
  protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة لقطة جديدة'),
        ];
    }
  
}
