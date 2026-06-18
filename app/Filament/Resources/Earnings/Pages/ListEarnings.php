<?php

namespace App\Filament\Resources\Earnings\Pages;

use App\Filament\Resources\Earnings\EarningResource;
use Filament\Resources\Pages\ListRecords;

class ListEarnings extends ListRecords
{
    protected static string $resource = EarningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
