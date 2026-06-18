<?php

namespace App\Filament\Resources\Deductions\Pages;

use App\Filament\Resources\Deductions\DeductionResource;
use Filament\Resources\Pages\ListRecords;

class ListDeductions extends ListRecords
{
    protected static string $resource = DeductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
