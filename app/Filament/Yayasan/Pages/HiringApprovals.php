<?php

namespace App\Filament\Yayasan\Pages;

use App\Models\AuditLog;
use App\Models\Candidate;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HiringApprovals extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Approvals';
    protected static ?string $title = 'Hiring Approvals';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.yayasan.pages.hiring-approvals';

    // Hold modal
    public ?int $holdCandidateId = null;
    public string $holdNote = '';

    public function getViewData(): array
    {
        return [
            'pending'  => Candidate::where('stage', 'yayasan')->with('vacancy')->orderByDesc('updated_at')->get(),
            'approved' => Candidate::whereJsonContains('meta->yayasan->status', 'approved')->with('vacancy')->latest('updated_at')->limit(30)->get(),
            'rejected' => Candidate::whereJsonContains('meta->yayasan->status', 'rejected')->with('vacancy')->latest('updated_at')->limit(30)->get(),
            'held'     => Candidate::whereJsonContains('meta->yayasan->status', 'on_hold')->where('stage', 'yayasan')->with('vacancy')->get(),
        ];
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

    public function approve(int $id): void
    {
        $c = Candidate::find($id);
        if (! $c) return;

        $meta = $c->meta ?? [];
        $meta['yayasan'] = array_merge($meta['yayasan'] ?? [], [
            'status'    => 'approved',
            'date'      => now()->toDateString(),
            'committee' => auth()->user()?->name,
        ]);
        $from = $c->stage;
        $c->update(['meta' => $meta, 'stage' => 'opl']);

        AuditLog::create([
            'occurred_at' => now(),
            'user_name'   => auth()->user()?->name ?? 'Yayasan',
            'role'        => auth()->user()?->role ?? 'unknown',
            'action'      => 'candidate.yayasan_approved',
            'target'      => $c->code,
            'from_value'  => $from,
            'to_value'    => 'opl',
            'note'        => 'Yayasan approval granted.',
        ]);

        Notification::make()->title($c->name . ' approved — advanced to OPL.')->success()->send();
    }

    public function reject(int $id): void
    {
        $c = Candidate::find($id);
        if (! $c) return;

        $meta = $c->meta ?? [];
        $meta['yayasan'] = array_merge($meta['yayasan'] ?? [], [
            'status'    => 'rejected',
            'date'      => now()->toDateString(),
            'committee' => auth()->user()?->name,
        ]);
        $from = $c->stage;
        $c->update(['meta' => $meta, 'stage' => 'rejected']);

        AuditLog::create([
            'occurred_at' => now(),
            'user_name'   => auth()->user()?->name ?? 'Yayasan',
            'role'        => auth()->user()?->role ?? 'unknown',
            'action'      => 'candidate.yayasan_rejected',
            'target'      => $c->code,
            'from_value'  => $from,
            'to_value'    => 'rejected',
            'note'        => 'Yayasan rejected.',
        ]);

        Notification::make()->title($c->name . ' rejected.')->danger()->send();
    }

    public function openHoldModal(int $id): void
    {
        $this->holdCandidateId = $id;
        $this->holdNote = '';
    }

    public function submitHold(): void
    {
        if (! $this->holdCandidateId) return;

        $c = Candidate::find($this->holdCandidateId);
        if (! $c) return;

        $meta = $c->meta ?? [];
        $meta['yayasan'] = array_merge($meta['yayasan'] ?? [], [
            'status'    => 'on_hold',
            'date'      => now()->toDateString(),
            'committee' => auth()->user()?->name,
            'hold_note' => $this->holdNote,
        ]);
        $c->update(['meta' => $meta]);

        AuditLog::create([
            'occurred_at' => now(),
            'user_name'   => auth()->user()?->name ?? 'Yayasan',
            'role'        => auth()->user()?->role ?? 'unknown',
            'action'      => 'candidate.yayasan_on_hold',
            'target'      => $c->code,
            'from_value'  => 'yayasan',
            'to_value'    => 'on_hold',
            'note'        => $this->holdNote ?: 'Placed on hold.',
        ]);

        $this->holdCandidateId = null;
        $this->holdNote = '';
        Notification::make()->title($c->name . ' placed on hold.')->warning()->send();
    }

    public function resumeFromHold(int $id): void
    {
        $c = Candidate::find($id);
        if (! $c) return;

        $meta = $c->meta ?? [];
        $meta['yayasan'] = array_merge($meta['yayasan'] ?? [], [
            'status'    => 'under_review',
            'date'      => now()->toDateString(),
            'committee' => auth()->user()?->name,
        ]);
        $c->update(['meta' => $meta]);

        Notification::make()->title($c->name . ' returned to active review.')->success()->send();
    }
}
