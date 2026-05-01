<?php

namespace App\Filament\AdminPanel\Resources\Feedback\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeedbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('screenshot_path')
                    ->required(),
            ]);
    }
}
