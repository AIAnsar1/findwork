<?php

namespace App\Filament\Resources\HeadHunterVacancies;

use App\Filament\Resources\HeadHunterVacancies\Pages\CreateHeadHunterVacancy;
use App\Filament\Resources\HeadHunterVacancies\Pages\EditHeadHunterVacancy;
use App\Filament\Resources\HeadHunterVacancies\Pages\ListHeadHunterVacancies;
use App\Filament\Resources\HeadHunterVacancies\Pages\ViewHeadHunterVacancy;
use App\Filament\Resources\HeadHunterVacancies\Schemas\HeadHunterVacancyForm;
use App\Filament\Resources\HeadHunterVacancies\Schemas\HeadHunterVacancyInfolist;
use App\Filament\Resources\HeadHunterVacancies\Tables\HeadHunterVacanciesTable;
use App\Models\HeadHunterVacancy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HeadHunterVacancyResource extends Resource
{
    protected static ?string $model = HeadHunterVacancy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return HeadHunterVacancyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HeadHunterVacancyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HeadHunterVacanciesTable::configure($table);
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
            'index' => ListHeadHunterVacancies::route('/'),
            'create' => CreateHeadHunterVacancy::route('/create'),
            'view' => ViewHeadHunterVacancy::route('/{record}'),
            'edit' => EditHeadHunterVacancy::route('/{record}/edit'),
        ];
    }
}
