<?php

namespace App\Filament\Resources\Groups\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->placeholder('-'),
                TextEntry::make('username')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('group_id')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('anti_spam_mode')
                    ->boolean(),
                IconEntry::make('auto_ban_user')
                    ->boolean(),
                TextEntry::make('ban_message')
                    ->placeholder('-'),
                TextEntry::make('max_warnings')
                    ->numeric(),
                TextEntry::make('invite_link')
                    ->placeholder('-'),
                IconEntry::make('bot_is_admin')
                    ->boolean(),
                TextEntry::make('language'),
                IconEntry::make('ban_on_link_username')
                    ->boolean(),
                TextEntry::make('members_count')
                    ->numeric(),
                TextEntry::make('last_synced_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
