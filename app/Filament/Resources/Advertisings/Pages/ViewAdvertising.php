<?php

namespace App\Filament\Resources\Advertisings\Pages;

use App\Filament\Resources\Advertisings\AdvertisingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdvertising extends ViewRecord
{
    protected static string $resource = AdvertisingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
