<?php

namespace App\Filament\Resources\Advertisings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdvertisingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('language'),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('expires_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('telegram_post_id')
                    ->placeholder('-'),
                TextEntry::make('post_url')
                    ->placeholder('-'),
                TextEntry::make('link')
                    ->placeholder('-'),
                TextEntry::make('views')
                    ->numeric(),
                TextEntry::make('reactions_count')
                    ->numeric(),
                TextEntry::make('channel.title')
                    ->label('Channel'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
