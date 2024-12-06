<?php

namespace App\Providers\Filament;

use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\TaskResource;
use App\Filament\Resources\TaskServiceResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\DashboardChart;
use App\Filament\Widgets\DashboardStatsOverview;
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
            ->default()
                        ->brandLogo('https://goldenbeeltd.vn/storage/images/logo-goldenbee.png')
            ->favicon('https://goldenbeeltd.vn/storage/images/logo-goldenbee.png')
                        ->brandLogoHeight('45px')
            ->id('admin')
            ->path('')
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
                TaskResource::class,
                UserResource::class,
                ServiceResource::class,
                TaskServiceResource::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ]);
    }
}
