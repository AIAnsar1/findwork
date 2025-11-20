<?php

namespace App\Filament\Resources\Resumes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResumesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->searchable(),
                TextColumn::make('age')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('address')
                    ->searchable(),
                TextColumn::make('position')
                    ->searchable(),
                TextColumn::make('salary')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('employment')
                    ->searchable(),
                TextColumn::make('format')
                    ->searchable(),
                TextColumn::make('experience_years')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('auto_posting')
                    ->boolean(),
                TextColumn::make('last_posted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('telegram')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('telegramUser.id')
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
