<?php

namespace App\Filament\Resources\LandingPages\Pages;

use App\Filament\Resources\LandingPages\LandingPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLandingPage extends CreateRecord
{
    protected static string $resource = LandingPageResource::class;

    /**
     * Map the active field's value back to the 'value' column before creating.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['value'] = match ($data['type'] ?? 'text') {
            'text' => $data['value_text'] ?? null,
            'textarea' => $data['value_textarea'] ?? null,
            'image' => is_array($data['value_image'] ?? null)
                ? ($data['value_image'][0] ?? null)
                : ($data['value_image'] ?? null),
            default => null,
        };

        unset($data['value_text'], $data['value_textarea'], $data['value_image']);

        return $data;
    }
}
