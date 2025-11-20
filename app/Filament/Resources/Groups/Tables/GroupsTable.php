<?php

namespace App\Filament\Resources\Groups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('username')
                    ->searchable(),
                TextColumn::make('group_id')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('anti_spam_mode')
                    ->boolean(),
                IconColumn::make('auto_ban_user')
                    ->boolean(),
                TextColumn::make('ban_message')
                    ->searchable(),
                TextColumn::make('max_warnings')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('invite_link')
                    ->searchable(),
                IconColumn::make('bot_is_admin')
                    ->boolean(),
                TextColumn::make('language')
                    ->searchable(),
                IconColumn::make('ban_on_link_username')
                    ->boolean(),
                TextColumn::make('members_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_synced_at')
                    ->dateTime()
                    ->sortable(),
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
