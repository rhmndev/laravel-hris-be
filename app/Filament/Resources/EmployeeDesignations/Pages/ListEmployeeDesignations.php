<?php

namespace App\Filament\Resources\EmployeeDesignations\Pages;

use App\Filament\Resources\EmployeeDesignations\EmployeeDesignationResource;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeDesignations extends ListRecords
{
    protected static string $resource = EmployeeDesignationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
