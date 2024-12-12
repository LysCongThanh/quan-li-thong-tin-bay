<?php

namespace App\Filament\Resources\EmployeeStatisticResource\Pages;

use App\Filament\Resources\EmployeeStatisticResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeStatistics extends ListRecords
{
    protected static string $resource = EmployeeStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
