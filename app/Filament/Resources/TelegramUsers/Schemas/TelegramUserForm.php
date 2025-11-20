<?php

namespace App\Filament\Resources\TelegramUsers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TelegramUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('username'),
                TextInput::make('first_name'),
                TextInput::make('last_name'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('language')
                    ->required()
                    ->default('ru'),
                Toggle::make('language_selected')
                    ->required(),
                Toggle::make('is_bot')
                    ->required(),
                Toggle::make('is_premium')
                    ->required(),
            ]);
    }
}
