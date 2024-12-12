<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';
    protected $fillable = ['name', 'email', 'phone', 'address'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function services()
    {
        return $this->hasManyThrough(
            TaskService::class,
            Task::class,
            'customer_id',
            'task_id'
        )->leftJoin('services', 'services.id', '=', 'task_services.service_id');
    }
}
