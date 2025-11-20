<?php

namespace App\Filament\Resources\Advertisings\Pages;

use App\Filament\Resources\Advertisings\AdvertisingResource;
use App\Jobs\PublishAdvertisingJob;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateAdvertising extends CreateRecord
{
    protected static string $resource = AdvertisingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Определяем статус на основе publish_now
        if (isset($data['publish_now']) && $data['publish_now']) {
            $data['status'] = 'scheduled';
            $data['scheduled_at'] = now();
        } else {
            $data['status'] = 'scheduled';
        }

        // Сохраняем content как массив путей к файлам
        if (isset($data['content']) && is_array($data['content'])) {
            $data['content'] = $data['content'];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $advertising = $this->record;

        // Если нужно опубликовать сразу
        if ($advertising->status === 'scheduled' && $advertising->scheduled_at <= now()) {
            try {
                PublishAdvertisingJob::dispatch($advertising);
            } catch (\Exception $e) {
                Log::error("Failed to dispatch PublishAdvertisingJob for advertising {$advertising->id}: ".$e->getMessage());
            }
        } elseif ($advertising->status === 'scheduled' && $advertising->scheduled_at > now()) {
            // Планируем публикацию на указанное время
            try {
                PublishAdvertisingJob::dispatch($advertising)
                    ->delay($advertising->scheduled_at);
            } catch (\Exception $e) {
                Log::error("Failed to schedule PublishAdvertisingJob for advertising {$advertising->id}: ".$e->getMessage());
            }
        }
    }
}
