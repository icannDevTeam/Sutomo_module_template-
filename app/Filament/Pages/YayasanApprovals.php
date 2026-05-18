<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Support\CsvExporter;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class YayasanApprovals extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Hiring';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.yayasan-approvals';
    protected static ?string $title = 'Yayasan Approvals';
    protected static ?string $navigationLabel = 'Yayasan Approvals';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPending')
                ->label('Export pending')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn () => CsvExporter::download(
                    Candidate::where('stage', 'yayasan')->with('vacancy')->get(),
                    CandidateResource::csvColumns(),
                    CsvExporter::filename('yayasan-pending'),
                )),
        ];
    }

    public function approve(int $id): void
    {
        $c = Candidate::find($id);
        if (!$c) return;
        $meta = $c->meta ?? [];
        $meta['yayasan'] = array_merge($meta['yayasan'] ?? [], [
            'status'    => 'approved',
            'date'      => now()->toDateString(),
            'committee' => auth()->user()?->name ?? 'Yayasan board',
        ]);
        $from = $c->stage;
        $c->update(['meta' => $meta, 'stage' => 'opl']);
        AuditLog::create([
            'occurred_at' => now(), 'user_name' => auth()->user()?->name ?? 'System',
            'role' => 'Yayasan', 'action' => 'approval.yayasan', 'target' => $c->code,
            'from_value' => $from, 'to_value' => 'opl', 'note' => 'Yayasan approval granted.',
        ]);
        Notification::make()->title($c->name . ' approved')->success()->send();
    }

    public function reject(int $id): void
    {
        $c = Candidate::find($id);
        if (!$c) return;
        $meta = $c->meta ?? [];
        $meta['yayasan'] = array_merge($meta['yayasan'] ?? [], [
            'status'    => 'rejected',
            'date'      => now()->toDateString(),
            'committee' => auth()->user()?->name ?? 'Yayasan board',
        ]);
        $from = $c->stage;
        $c->update(['meta' => $meta, 'stage' => 'rejected']);
        AuditLog::create([
            'occurred_at' => now(), 'user_name' => auth()->user()?->name ?? 'System',
            'role' => 'Yayasan', 'action' => 'approval.yayasan', 'target' => $c->code,
            'from_value' => $from, 'to_value' => 'rejected', 'note' => 'Yayasan rejected.',
        ]);
        Notification::make()->title($c->name . ' rejected')->danger()->send();
    }

    public function getViewData(): array
    {
        return [
            'pending'  => Candidate::where('stage', 'yayasan')->with('vacancy')->get(),
            'approved' => Candidate::whereJsonContains('meta->yayasan->status', 'approved')->with('vacancy')->limit(50)->get(),
            'rejected' => Candidate::whereJsonContains('meta->yayasan->status', 'rejected')->with('vacancy')->limit(50)->get(),
        ];
    }
}
