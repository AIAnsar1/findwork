<?php

namespace App\Filament\Resources\Vacancies\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VacancyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('company')
                    ->placeholder('-'),
                TextEntry::make('salary')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('experience')
                    ->placeholder('-'),
                TextEntry::make('employment'),
                TextEntry::make('schedule')
                    ->placeholder('-'),
                TextEntry::make('work_hours')
                    ->placeholder('-'),
                TextEntry::make('format'),
                TextEntry::make('responsibilities')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('requirements')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('conditions')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('benefits')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('auto_posting')
                    ->boolean(),
                TextEntry::make('last_posted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('position')
                    ->placeholder('-'),
                TextEntry::make('contact_name')
                    ->placeholder('-'),
                TextEntry::make('contact_phone')
                    ->placeholder('-'),
                TextEntry::make('contact_email')
                    ->placeholder('-'),
                TextEntry::make('contact_telegram')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('address')
                    ->placeholder('-'),
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
