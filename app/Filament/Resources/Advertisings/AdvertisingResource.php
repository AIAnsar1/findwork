<?php

namespace App\Filament\Resources\Advertisings;

use App\Filament\Resources\Advertisings\Pages\CreateAdvertising;
use App\Filament\Resources\Advertisings\Pages\EditAdvertising;
use App\Filament\Resources\Advertisings\Pages\ListAdvertisings;
use App\Filament\Resources\Advertisings\Pages\ViewAdvertising;
use App\Filament\Resources\Advertisings\Schemas\AdvertisingForm;
use App\Filament\Resources\Advertisings\Schemas\AdvertisingInfolist;
use App\Filament\Resources\Advertisings\Tables\AdvertisingsTable;
use App\Models\Advertising;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdvertisingResource extends Resource
{
    protected static ?string $model = Advertising::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AdvertisingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdvertisingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdvertisingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdvertisings::route('/'),
            'create' => CreateAdvertising::route('/create'),
            'view' => ViewAdvertising::route('/{record}'),
            'edit' => EditAdvertising::route('/{record}/edit'),
        ];
    }
}
