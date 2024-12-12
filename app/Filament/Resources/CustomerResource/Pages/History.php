<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class History extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    public function getTitle(): string
    {
        return 'Lịch sử sử dụng dịch vụ';
    }

    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        $customerId = request()->route('customer_id');

        return parent::getTableQuery()
            ->whereHas('tasks', fn($q) => $q->where('customer_id', $customerId))
            ->select([
                '*'
            ]);
    }
}
