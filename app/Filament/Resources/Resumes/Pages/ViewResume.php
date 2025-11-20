<?php

namespace App\Filament\Resources\Resumes\Pages;

use App\Filament\Resources\Resumes\ResumeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewResume extends ViewRecord
{
    protected static string $resource = ResumeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
