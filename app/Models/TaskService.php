<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskService extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'service_name',
        'service_unit',
        'task_id',
        'quantity',
        'money_received',
        'status',
        'note',
        'reported_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($taskService) {
            if ($service = $taskService->service) {
                $taskService->service_name = $service->name;
                $taskService->service_unit = $service->unit;
            }
        });

        static::updating(function ($taskService) {
            if ($service = $taskService->service) {
                $taskService->service_name = $service->name;
                $taskService->service_unit = $service->unit;
            }
        });
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
