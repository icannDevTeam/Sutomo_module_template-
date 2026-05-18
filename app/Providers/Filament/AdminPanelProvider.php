<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->globalSearch()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchFieldSuffix(fn () => '⌘K')
            ->brandName('Sutomo HR')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
                'gray'    => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
                'info'    => Color::Sky,
            ])
            ->font('Inter')
            ->navigationGroups([
                'Hiring',
                'People',
                'Finance',
                'Admin',
            ])
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                'settings' => MenuItem::make()
                    ->label('Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn () => route('filament.admin.pages.settings')),
                MenuItem::make()
                    ->label('Audit log')
                    ->icon('heroicon-o-shield-check')
                    ->url(fn () => route('filament.admin.resources.audit-logs.index')),
                MenuItem::make()
                    ->label('Hiring pipeline')
                    ->icon('heroicon-o-view-columns')
                    ->url(fn () => route('filament.admin.pages.pipeline')),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\DashboardHero::class,
                \App\Filament\Widgets\KpiOverview::class,
                \App\Filament\Widgets\ApplicationsTrendChart::class,
                \App\Filament\Widgets\CandidatesByDepartmentChart::class,
                \App\Filament\Widgets\TeachersByCampusChart::class,
                \App\Filament\Widgets\DepositsCollectedChart::class,
                \App\Filament\Widgets\HiringFunnel::class,
                \App\Filament\Widgets\UpcomingApprovals::class,
                \App\Filament\Widgets\RecentApprovals::class,
                \App\Filament\Widgets\RecentActivity::class,
            ])
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
