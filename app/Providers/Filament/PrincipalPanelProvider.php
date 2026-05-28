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
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
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
                'Hiring',          // Teacher Recruitment (Vacancy / Candidate / Interview / Deposit / Pipeline / Assessments)
                'People',          // Teacher Onboarding (Teacher master + OPL/Probation/Contract board)
                'Approvals',
                'Procurement',
                'SSC',
                'CCA / ECA',
                'Operations',
                'Planning',
                'Communication',
                'Settings',
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('15rem')
            ->collapsedSidebarWidth('4.5rem')
            ->maxContentWidth('full')
            ->userMenuItems([
                MenuItem::make()
                    ->label('Back to HR admin')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->url(fn () => '/admin'),
            ])
            ->discoverResources(in: app_path('Filament/Principal/Resources'), for: 'App\\Filament\\Principal\\Resources')
            ->resources([
                // Teacher Recruitment & Onboarding module (shared with Admin panel).
                \App\Filament\Resources\VacancyResource::class,
                \App\Filament\Resources\CandidateResource::class,
                \App\Filament\Resources\InterviewResource::class,
                \App\Filament\Resources\DepositResource::class,
                \App\Filament\Resources\TeacherResource::class,
                \App\Filament\Principal\Resources\LetterOfIntentResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Principal/Pages'), for: 'App\\Filament\\Principal\\Pages')
            ->pages([
                \App\Filament\Principal\Pages\PrincipalDashboard::class,
                \App\Filament\Principal\Pages\SchedulePage::class,
                \App\Filament\Pages\Pipeline::class,
                \App\Filament\Pages\Assessments::class,
                \App\Filament\Pages\YayasanApproval::class,
                \App\Filament\Pages\Onboarding::class,
                \App\Filament\Principal\Pages\OplSessionDetail::class,
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
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => Blade::render('<link rel="stylesheet" href="{{ asset(\'css/principal.css\') }}?v=' . filemtime(public_path('css/principal.css')) . '">')
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn () => view('filament.principal.topbar.context-pill')->render()
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => view('filament.principal.topbar.quick-icons')->render()
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('filament.principal.topbar.floating-chat')->render()
            );
    }
}
