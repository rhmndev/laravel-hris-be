<?php

namespace App\Filament\Resources\LandingPages\Pages;

use App\Filament\Resources\LandingPages\LandingPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLandingPage extends EditRecord
{
    protected static string $resource = LandingPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Map the 'value' column to the correct field before filling the form.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $value = $data['value'] ?? null;
        $type = $data['type'] ?? 'text';

        // Map value to the correct field based on type
        $data['value_text'] = ($type === 'text') ? $value : null;
        $data['value_textarea'] = ($type === 'textarea') ? $value : null;
        $data['value_image'] = ($type === 'image') ? $value : null;

        // Remove the original value key so it doesn't conflict
        unset($data['value']);

        return $data;
    }

    /**
     * Map the active field's value back to the 'value' column before saving.
     */
    protected function mutateFormDataBeforeSave(array $data): array
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
