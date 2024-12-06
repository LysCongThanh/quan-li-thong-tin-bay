<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'email', 'phone', 'address'];
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'customer.name', 'name'); // Nếu liên kết qua tên
    }
}
