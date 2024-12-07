<?php

namespace App\Filament\Resources\TaskServiceResource\Pages;

use App\Filament\Resources\TaskServiceResource;
use App\Models\Task;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTaskService extends CreateRecord
{
    protected static string $resource = TaskServiceResource::class;

    public function getBreadcrumbs(): array
    {
        $task = Task::find($this->task_id);

        return [
            '/tasks' => 'Công việc',
            '/task-services/' . $this->task_id => 'Báo cáo công việc: ' . ($task ? $task->name : ''),
            '' => 'Tạo'
        ];
    }

    public function getTitle(): string {
        return "Báo cáo công việc";
    }

    public int $task_id;

    public function mount($task_id = null): void
    {
        $this->task_id = $task_id;
        parent::mount();
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['task_id'] = $this->task_id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('list', [
            'task_id' => $this->task_id
        ]);
    }
}
