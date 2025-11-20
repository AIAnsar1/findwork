<?php

namespace App\Filament\Resources\Resumes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ResumeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('full_name'),
                TextInput::make('age')
                    ->numeric(),
                TextInput::make('address'),
                TextInput::make('position'),
                TextInput::make('salary')
                    ->numeric(),
                TextInput::make('employment')
                    ->required()
                    ->default('full'),
                TextInput::make('format')
                    ->required()
                    ->default('office'),
                TextInput::make('experience_years')
                    ->numeric(),
                Textarea::make('skills')
                    ->columnSpanFull(),
                TextInput::make('work_experience'),
                Toggle::make('auto_posting')
                    ->required(),
                DateTimePicker::make('last_posted_at'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('telegram')
                    ->tel(),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
                Textarea::make('about')
                    ->columnSpanFull(),
                Select::make('telegram_user_id')
                    ->relationship('telegramUser', 'id')
                    ->required(),
            ]);
    }
}
