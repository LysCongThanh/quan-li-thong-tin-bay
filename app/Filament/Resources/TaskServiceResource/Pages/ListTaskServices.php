<?php

namespace App\Filament\Resources\TaskServiceResource\Pages;

use App\Filament\Resources\TaskServiceResource;
use Filament\Actions;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListTaskServices extends ListRecords
{
    protected static string $resource = TaskServiceResource::class;

    public function getHeaderWidgets(): array
    {
        return [
            TaskServiceResource\Widgets\TaskServiceStats::make([
                'task_id' => request()->route('task_id')
            ]),
        ];
    }

    public function getTitle(): string
    {
        $taskId = request()->route('task_id');
        $task = \App\Models\Task::find($taskId);

        return "Báo cáo công việc: " . ($task ? $task->name : '');
    }

    public function getBreadcrumbs(): array
    {
        $taskId = request()->route('task_id');
        $task = \App\Models\Task::find($taskId);

        return [
            '/tasks' => 'Công việc',
            '/task-services/' . $taskId => 'Báo cáo',
            '' => 'Danh sách'
        ];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getTableQuery();

        $taskId = request()->route('task_id');

        if ($taskId) {
            $query->where('task_id', $taskId);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        if (!request()->route('task_id')) {
            return [];
        }

        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->label('Báo cáo công việc')
                ->url(fn () => TaskServiceResource::getUrl('create', [
                    'task_id' => request()->route('task_id')
                ]))
        ];
    }
}
