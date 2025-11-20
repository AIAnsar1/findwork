<?php

namespace App\Filament\Resources\Groups\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title'),
                TextInput::make('username'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('group_id')
                    ->numeric(),
                Toggle::make('anti_spam_mode')
                    ->required(),
                Toggle::make('auto_ban_user')
                    ->required(),
                TextInput::make('ban_message'),
                TextInput::make('banned_words'),
                TextInput::make('banned_links'),
                TextInput::make('banned_usernames'),
                TextInput::make('max_warnings')
                    ->required()
                    ->numeric()
                    ->default(3),
                TextInput::make('invite_link'),
                Toggle::make('bot_is_admin')
                    ->required(),
                TextInput::make('language')
                    ->required()
                    ->default('en'),
                Toggle::make('ban_on_link_username')
                    ->required(),
                TextInput::make('members_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('last_synced_at'),
            ]);
    }
}
