<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'customer_name',
        'pilot_id',
        'support_id'
    ];

    public function pilot(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pilot_id');
    }

    public function support(): BelongsTo
    {
        return $this->belongsTo(User::class, 'support_id');
    }

    public function taskServices(): HasMany
    {
        return $this->hasMany(TaskService::class);
    }
}
