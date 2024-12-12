<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name','unit', 'price'];

    public function taskServices(): HasMany
    {
        return $this->hasMany(TaskService::class);
    }
}
