<?php

namespace App\Filament\Resources\HeadHunterVacancies\Pages;

use App\Filament\Resources\HeadHunterVacancies\HeadHunterVacancyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHeadHunterVacancy extends ViewRecord
{
    protected static string $resource = HeadHunterVacancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
