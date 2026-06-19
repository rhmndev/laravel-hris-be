<?php

namespace App\Filament\Resources\AppReleases\Pages;

use App\Filament\Resources\AppReleases\AppReleaseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateAppRelease extends CreateRecord
{
    protected static string $resource = AppReleaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['file_path'])) {
            $data['file_size'] = Storage::disk('public')->size($data['file_path']);
        }

        return $data;
    }
}
