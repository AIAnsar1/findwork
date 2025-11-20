<?php

namespace App\Filament\Resources\Advertisings\Pages;

use App\Filament\Resources\Advertisings\AdvertisingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAdvertising extends EditRecord
{
    protected static string $resource = AdvertisingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
