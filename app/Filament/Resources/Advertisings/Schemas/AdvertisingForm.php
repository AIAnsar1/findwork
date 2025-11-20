<?php

namespace App\Filament\Resources\Advertisings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AdvertisingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Заголовок')
                    ->required()
                    ->maxLength(255),

                RichEditor::make('description')
                    ->label('Описание')
                    ->required()
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'codeBlock',
                    ]),

                FileUpload::make('content')
                    ->label('Фото или Видео')
                    ->disk('public')
                    ->directory('advertisings')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/*', 'video/*'])
                    ->maxFiles(10)
                    ->multiple()
                    ->helperText('Можно загрузить несколько фото или одно видео')
                    ->dehydrated(true),

                Toggle::make('publish_now')
                    ->label('Опубликовать сразу')
                    ->default(false)
                    ->live()
                    ->helperText('Если включено, реклама будет опубликована немедленно'),

                DateTimePicker::make('scheduled_at')
                    ->label('Дата публикации')
                    ->required()
                    ->visible(fn (Get $get) => ! $get('publish_now'))
                    ->default(now()->addHour())
                    ->helperText('Когда опубликовать рекламу'),

                Hidden::make('scheduled_at')
                    ->dehydrateStateUsing(function (Get $get) {
                        if ($get('publish_now')) {
                            return now();
                        }

                        return $get('scheduled_at');
                    }),

                DateTimePicker::make('expires_at')
                    ->label('Дата окончания')
                    ->required()
                    ->minDate(fn (Get $get) => $get('scheduled_at') ?? now())
                    ->helperText('После этой даты реклама будет автоматически удалена из канала'),

                Select::make('language')
                    ->label('Язык')
                    ->required()
                    ->options([
                        'ru' => 'Русский',
                        'en' => 'English',
                        'uz' => 'Uzbek',
                    ])
                    ->default('ru')
                    ->native(false),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'scheduled' => 'Запланирован',
                        'published' => 'Опубликован',
                        'failed' => 'Ошибка',
                        'archived' => 'Архивирован',
                    ])
                    ->default('draft')
                    ->required()
                    ->native(false)
                    ->disabled(fn (Get $get) => $get('publish_now')),
            ]);
    }
}
