<?php

namespace App\Filament\Resources\Advertisings\Tables;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\{Get, Set};
use Filament\Forms\Components\{DateTimePicker, FileUpload, MarkdownEditor, RichEditor, Select, TextInput, Textarea, Hidden, Toggle};

class AdvertisingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('language')
                    ->searchable(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('telegram_post_id')
                    ->searchable(),
                TextColumn::make('post_url')
                    ->searchable(),
                TextColumn::make('link')
                    ->searchable(),
                TextColumn::make('views')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reactions_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('channel.title')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
