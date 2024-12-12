<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WidgetPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        // Lấy tên class của widget để check permission
        $widgetClass = class_basename(static::class);
        return $user->can('widget_' . $widgetClass);
    }
}
