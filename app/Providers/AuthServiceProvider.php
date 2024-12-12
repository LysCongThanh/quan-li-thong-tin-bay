<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Filament\Widgets\DashboardStatsOverview' => 'App\Policies\WidgetPolicy',
        'App\Filament\Widgets\DashboardChart' => 'App\Policies\WidgetPolicy',
        'App\Filament\Widgets\TaskServiceChart' => 'App\Policies\WidgetPolicy',
        'App\Filament\Widgets\EmployeeCompleteTaskChart' => 'App\Policies\WidgetPolicy',
        'App\Filament\Widgets\RevenueFromCustomerChart' => 'App\Policies\WidgetPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
