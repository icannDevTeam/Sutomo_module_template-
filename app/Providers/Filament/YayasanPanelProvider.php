<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class YayasanPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('yayasan')
            ->path('yayasan')
            ->login()
            ->profile()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->brandName('Sutomo Yayasan')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Rose,
                'gray'    => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
                'info'    => Color::Sky,
            ])
            ->font('Inter')
            ->navigationGroups([
                'Overview',
                'Approvals',
                'HR Tasks',
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('15rem')
            ->collapsedSidebarWidth('4.5rem')
            ->maxContentWidth('full')
            ->pages([
                \App\Filament\Yayasan\Pages\YayasanDashboard::class,
                \App\Filament\Yayasan\Pages\ApprovalInbox::class,
                \App\Filament\Yayasan\Pages\HiringApprovals::class,
                \App\Filament\Yayasan\Pages\HrTasksOverview::class,
                \App\Filament\Yayasan\Pages\HistoryPage::class,
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
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => Blade::render('<link rel="stylesheet" href="{{ asset(\'css/principal.css\') }}?v=' . (file_exists(public_path('css/principal.css')) ? filemtime(public_path('css/principal.css')) : '1') . '">'
                    . '<link rel="stylesheet" href="{{ asset(\'css/approvals.css\') }}?v=' . (file_exists(public_path('css/approvals.css')) ? filemtime(public_path('css/approvals.css')) : '1') . '">')
            );
    }
}
