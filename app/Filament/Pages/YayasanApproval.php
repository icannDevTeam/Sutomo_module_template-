<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Models\Candidate;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class YayasanApproval extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Hiring';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.yayasan-approval';
    protected static ?string $title = 'Yayasan Approval';
    protected static ?string $slug = 'yayasan-approval';

    /** Status codes mirrored by Yayasan Board (visible to principal as read-only). */
    public const YAYASAN_STATUSES = [
        'received'     => 'Received',
        'under_review' => 'Under Review',
        'on_hold'      => 'On Hold',
        'approved'     => 'Approved',
        'rejected'     => 'Rejected',
    ];

    public function getViewData(): array
    {
        $queue = Candidate::with('vacancy')->where('stage', 'yayasan')->orderByDesc('updated_at')->get();

        $approved30 = AuditLog::where('action', 'candidate.yayasan_approved')
            ->where('occurred_at', '>=', now()->subDays(30))->count();
        $returned30 = AuditLog::where('action', 'candidate.yayasan_returned')
            ->where('occurred_at', '>=', now()->subDays(30))->count();
        $rejected30 = AuditLog::where('action', 'candidate.yayasan_rejected')
            ->where('occurred_at', '>=', now()->subDays(30))->count();

        return [
            'queue'    => $queue,
            'statuses' => self::YAYASAN_STATUSES,
            'kpi'      => [
                'awaiting' => $queue->count(),
                'approved' => $approved30,
                'returned' => $returned30,
                'rejected' => $rejected30,
            ],
            'recent'   => AuditLog::whereIn('action', [
                'candidate.yayasan_approved',
                'candidate.yayasan_returned',
                'candidate.yayasan_rejected',
                'candidate.yayasan_status_changed',
            ])->latest('occurred_at')->limit(8)->get(),
        ];
    }

    /* ---------- Principal-side action: attach a recommendation note. ---------- */
    public function noteAction(): Action
    {
        return Action::make('note')
            ->label('Add note')
            ->icon('heroicon-m-pencil-square')
            ->color('gray')
            ->size('sm')
            ->form([
                Textarea::make('note')
                    ->label('Principal recommendation or context')
                    ->rows(4)
                    ->required(),
            ])
            ->action(function (array $arguments, array $data): void {
                $c = Candidate::find($arguments['id']);
                if (! $c) return;

                $meta = $c->meta ?? [];
                $meta['yayasan_notes'] = array_merge($meta['yayasan_notes'] ?? [], [[
                    'by'   => auth()->user()?->name ?? 'Principal',
                    'at'   => now()->toIso8601String(),
                    'note' => $data['note'],
                ]]);
                $c->meta = $meta;
                $c->save();

                AuditLog::create([
                    'occurred_at' => now(),
                    'user_name'   => auth()->user()?->name ?? 'Principal',
                    'role'        => 'principal',
                    'action'      => 'candidate.yayasan_note_added',
                    'subject_type'=> 'candidate',
                    'subject_id'  => $c->id,
                    'detail'      => "Principal note added for {$c->name}",
                ]);

                Notification::make()->title('Note added for Yayasan board')->success()->send();
            });
    }

    /* ---------- RETIRED — decisions now live in the Yayasan portal ----------
       Principal can no longer simulate Yayasan decisions. Real Yayasan users
       approve/reject/hold candidates at /yayasan/hiring-approvals. ---------- */
    public function simulateYayasanAction(): Action
    {
        return Action::make('simulateYayasan')
            ->label('Yayasan decisions moved to Yayasan portal')
            ->icon('heroicon-m-arrow-top-right-on-square')
            ->color('gray')
            ->size('sm')
            ->disabled()
            ->action(fn () => Notification::make()
                ->title('Decisions are now recorded in the Yayasan portal at /yayasan/hiring-approvals')
                ->warning()->send());
    }

    public static function getNavigationBadge(): ?string
    {
        $n = Candidate::where('stage', 'yayasan')->count();
        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }
}

