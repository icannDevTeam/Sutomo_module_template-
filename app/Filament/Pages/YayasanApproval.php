<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Models\Candidate;
use Filament\Actions\Action;
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

    public function getViewData(): array
    {
        $queue = Candidate::with('vacancy')->where('stage', 'yayasan')->orderByDesc('updated_at')->get();

        // Approvals in last 30 days
        $approved30 = AuditLog::where('action', 'candidate.yayasan_approved')
            ->where('occurred_at', '>=', now()->subDays(30))->count();
        $returned30 = AuditLog::where('action', 'candidate.yayasan_returned')
            ->where('occurred_at', '>=', now()->subDays(30))->count();
        $rejected30 = AuditLog::where('action', 'candidate.yayasan_rejected')
            ->where('occurred_at', '>=', now()->subDays(30))->count();

        return [
            'queue'      => $queue,
            'kpi'        => [
                'awaiting' => $queue->count(),
                'approved' => $approved30,
                'returned' => $returned30,
                'rejected' => $rejected30,
            ],
            'recent'     => AuditLog::whereIn('action', [
                'candidate.yayasan_approved',
                'candidate.yayasan_returned',
                'candidate.yayasan_rejected',
            ])->latest('occurred_at')->limit(8)->get(),
        ];
    }

    /* ---------- Row actions (Principal monitoring view) ----------
       Principal cannot approve/reject on this page — only Yayasan does that.
       But for the prototype we expose simulated actions for the panel demo.
       The Principal's primary affordance here is to add a recommendation note
       and view the audit trail. The Yayasan board approves elsewhere. */

    public function noteAction(): Action
    {
        return Action::make('note')
            ->label('Add note')
            ->icon('heroicon-m-pencil-square')
            ->color('gray')
            ->size('sm')
            ->form([
                Textarea::make('note')
                    ->label('Principal recommendation / context')
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

    public function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Mark approved (Yayasan)')
            ->icon('heroicon-m-check-circle')
            ->color('success')
            ->size('sm')
            ->requiresConfirmation()
            ->modalHeading('Mark candidate as Yayasan-approved')
            ->modalDescription('This simulates Yayasan board approval and moves the candidate to OPL Probation.')
            ->action(function (array $arguments): void {
                $c = Candidate::find($arguments['id']);
                if (! $c) return;

                $c->stage = 'opl';
                $c->save();

                AuditLog::create([
                    'occurred_at' => now(),
                    'user_name'   => 'Yayasan Board',
                    'role'        => 'yayasan',
                    'action'      => 'candidate.yayasan_approved',
                    'subject_type'=> 'candidate',
                    'subject_id'  => $c->id,
                    'detail'      => "{$c->name} approved by Yayasan — moved to OPL",
                ]);

                Notification::make()
                    ->title('Yayasan approval recorded')
                    ->body("{$c->name} moved to OPL Probation.")
                    ->success()
                    ->sendToDatabase(auth()->user());

                Notification::make()->title("{$c->name} approved")->success()->send();
            });
    }

    public function returnAction(): Action
    {
        return Action::make('return')
            ->label('Return for review')
            ->icon('heroicon-m-arrow-uturn-left')
            ->color('warning')
            ->size('sm')
            ->form([
                Textarea::make('reason')->label('Reason')->rows(3)->required(),
            ])
            ->action(function (array $arguments, array $data): void {
                $c = Candidate::find($arguments['id']);
                if (! $c) return;

                $c->stage = 'interview';
                $c->save();

                AuditLog::create([
                    'occurred_at' => now(),
                    'user_name'   => 'Yayasan Board',
                    'role'        => 'yayasan',
                    'action'      => 'candidate.yayasan_returned',
                    'subject_type'=> 'candidate',
                    'subject_id'  => $c->id,
                    'detail'      => "Returned for review: {$data['reason']}",
                ]);

                Notification::make()->title("{$c->name} returned for review")->warning()->send();
            });
    }

    public function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->icon('heroicon-m-x-circle')
            ->color('danger')
            ->size('sm')
            ->requiresConfirmation()
            ->form([
                Textarea::make('reason')->label('Reason')->rows(3)->required(),
            ])
            ->action(function (array $arguments, array $data): void {
                $c = Candidate::find($arguments['id']);
                if (! $c) return;

                $c->stage = 'rejected';
                $c->save();

                AuditLog::create([
                    'occurred_at' => now(),
                    'user_name'   => 'Yayasan Board',
                    'role'        => 'yayasan',
                    'action'      => 'candidate.yayasan_rejected',
                    'subject_type'=> 'candidate',
                    'subject_id'  => $c->id,
                    'detail'      => "Rejected: {$data['reason']}",
                ]);

                Notification::make()->title("{$c->name} rejected")->danger()->send();
            });
    }

    public static function getNavigationBadge(): ?string
    {
        $n = Candidate::where('stage', 'yayasan')->count();
        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
