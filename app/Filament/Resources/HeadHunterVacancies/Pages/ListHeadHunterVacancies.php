<?php

namespace App\Filament\Resources\HeadHunterVacancies\Pages;

use App\Filament\Resources\HeadHunterVacancies\HeadHunterVacancyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHeadHunterVacancies extends ListRecords
{
    protected static string $resource = HeadHunterVacancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
