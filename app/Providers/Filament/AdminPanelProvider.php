<?php

namespace App\Providers\Filament;

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\TaskResource;
use App\Filament\Resources\TaskServiceResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\DashboardChart;
use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\EmployeeCompleteTaskChart;
use App\Filament\Widgets\RevenueFromCustomerChart;
use App\Filament\Widgets\TaskServiceChart;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Pages\Auth\Login;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->spa()
            ->breadcrumbs(true)
            ->sidebarWidth('300px')
            ->collapsedSidebarWidth('5rem')
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            // *** Khi nào cần cho người dùng quản trị đăng ký
            // ->registration(Register::class)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                DashboardStatsOverview::class,
                DashboardChart::class,
                TaskServiceChart::class,
                EmployeeCompleteTaskChart::class,
                RevenueFromCustomerChart::class
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authGuard('web')
            ->authMiddleware([
                Authenticate::class,
            ])
            ->resources([
                UserResource::class,
                TaskResource::class,
                ServiceResource::class,
                TaskServiceResource::class,
//                CustomerResource::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ]);
    }
}
