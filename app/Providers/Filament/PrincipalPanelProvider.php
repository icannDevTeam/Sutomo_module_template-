<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PrincipalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('principal')
            ->path('principal')
            ->login()
            ->profile()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->globalSearch()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchFieldSuffix(fn () => '⌘K')
            ->brandName('Sutomo Principal')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Indigo,
                'gray'    => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
                'info'    => Color::Sky,
            ])
            ->font('Inter')
            ->navigationGroups([
                'Overview',
                'Enrollment',
                'Academics',
                'Students',
                'Teachers',
                'Approvals',
                'Operations',
            ])
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                MenuItem::make()
                    ->label('Back to HR admin')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->url(fn () => '/admin'),
            ])
            ->discoverResources(in: app_path('Filament/Principal/Resources'), for: 'App\\Filament\\Principal\\Resources')
            ->discoverPages(in: app_path('Filament/Principal/Pages'), for: 'App\\Filament\\Principal\\Pages')
            ->pages([
                \App\Filament\Principal\Pages\PrincipalDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Principal/Widgets'), for: 'App\\Filament\\Principal\\Widgets')
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
