<?php

namespace App\Filament\Resources\TaskServiceResource\Pages;

use App\Filament\Resources\TaskServiceResource;
use App\Models\Task;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaskService extends EditRecord
{
    protected static string $resource = TaskServiceResource::class;

    public $task_id;

    public function mount($record): void
    {
        $this->task_id = request()->query('task_id');
        parent::mount($record);
    }

    public function getBreadcrumbs(): array
    {
        $task = Task::find($this->task_id);

        return [
            '/tasks' => 'Công việc',
            '/task-services/' . $this->task_id => 'Báo cáo công việc: ' . ($task ? $task->name : ''),
            '' => 'Chỉnh sửa'
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('list', [
            'task_id' => $this->task_id
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
