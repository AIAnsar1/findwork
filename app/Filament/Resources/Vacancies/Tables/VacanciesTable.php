<?php

namespace App\Filament\Resources\Vacancies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VacanciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company')
                    ->searchable(),
                TextColumn::make('salary')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('experience')
                    ->searchable(),
                TextColumn::make('employment')
                    ->searchable(),
                TextColumn::make('schedule')
                    ->searchable(),
                TextColumn::make('work_hours')
                    ->searchable(),
                TextColumn::make('format')
                    ->searchable(),
                IconColumn::make('auto_posting')
                    ->boolean(),
                TextColumn::make('last_posted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('position')
                    ->searchable(),
                TextColumn::make('contact_name')
                    ->searchable(),
                TextColumn::make('contact_phone')
                    ->searchable(),
                TextColumn::make('contact_email')
                    ->searchable(),
                TextColumn::make('contact_telegram')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('address')
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
