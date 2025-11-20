<?php

namespace App\Filament\Resources\Advertisings\Pages;

use App\Filament\Resources\Advertisings\AdvertisingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdvertisings extends ListRecords
{
    protected static string $resource = AdvertisingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
