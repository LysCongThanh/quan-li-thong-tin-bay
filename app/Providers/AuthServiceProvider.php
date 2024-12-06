<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Role;
use App\Models\Service;
use App\Models\Task;
use App\Models\TaskService;
use App\Models\User;
use App\Policies\RolePolicy;
use App\Policies\ServicePolicy;
use App\Policies\TaskPolicy;
use App\Policies\TaskServicePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Role::class => RolePolicy::class,
        User::class  => UserPolicy::class,
        Service::class => ServicePolicy::class,
        Task::class => TaskPolicy::class,
        TaskService::class => TaskServicePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
