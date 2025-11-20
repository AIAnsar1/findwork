<?php

namespace App\Filament\Resources\Vacancies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VacancyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company'),
                TextInput::make('salary')
                    ->numeric(),
                TextInput::make('experience'),
                TextInput::make('employment')
                    ->required()
                    ->default('full'),
                TextInput::make('schedule'),
                TextInput::make('work_hours'),
                TextInput::make('format')
                    ->required()
                    ->default('office'),
                Textarea::make('responsibilities')
                    ->columnSpanFull(),
                Textarea::make('requirements')
                    ->columnSpanFull(),
                Textarea::make('conditions')
                    ->columnSpanFull(),
                Textarea::make('benefits')
                    ->columnSpanFull(),
                Toggle::make('auto_posting')
                    ->required(),
                DateTimePicker::make('last_posted_at'),
                TextInput::make('position'),
                TextInput::make('contact_name'),
                TextInput::make('contact_phone')
                    ->tel(),
                TextInput::make('contact_email')
                    ->email(),
                TextInput::make('contact_telegram')
                    ->tel(),
                TextInput::make('status')
                    ->required()
                    ->default('open'),
                TextInput::make('address'),
                Select::make('telegram_user_id')
                    ->relationship('telegramUser', 'id')
                    ->required(),
            ]);
    }
}
