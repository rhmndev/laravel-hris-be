<?php

namespace App\Filament\Resources\TaxSettings\Pages;

use App\Filament\Resources\TaxSettings\TaxSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListTaxSettings extends ListRecords
{
    protected static string $resource = TaxSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
