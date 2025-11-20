<?php

namespace App\Filament\Resources\Resumes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ResumeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('full_name')
                    ->placeholder('-'),
                TextEntry::make('age')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->placeholder('-'),
                TextEntry::make('position')
                    ->placeholder('-'),
                TextEntry::make('salary')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('employment'),
                TextEntry::make('format'),
                TextEntry::make('experience_years')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('skills')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('auto_posting')
                    ->boolean(),
                TextEntry::make('last_posted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('telegram')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('about')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('telegramUser.id')
                    ->label('Telegram user'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
