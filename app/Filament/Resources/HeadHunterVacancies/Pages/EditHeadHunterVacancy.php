<?php

namespace App\Filament\Resources\HeadHunterVacancies\Pages;

use App\Filament\Resources\HeadHunterVacancies\HeadHunterVacancyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditHeadHunterVacancy extends EditRecord
{
    protected static string $resource = HeadHunterVacancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
