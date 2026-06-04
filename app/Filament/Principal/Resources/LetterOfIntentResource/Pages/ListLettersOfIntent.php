<?php

namespace App\Filament\Principal\Resources\LetterOfIntentResource\Pages;

use App\Filament\Principal\Resources\LetterOfIntentResource;
use App\Models\LetterOfIntent;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListLettersOfIntent extends ListRecords
{
    protected static string $resource = LetterOfIntentResource::class;

    /** Selected academic year filter ('all' = no filter). URL-bound. */
    #[Url(as: 'ay')]
    public ?string $academicYear = 'current';

    public function mount(): void
    {
        parent::mount();

        if ($this->academicYear === 'current' || blank($this->academicYear)) {
            $this->academicYear = LetterOfIntentResource::currentAcademicYear();
        }
    }

    public function getHeading(): string
    {
        return 'Letters of Intent — Overview';
    }

    public function getHeader(): ?View
    {
        return view('filament.principal.loi.list-header', [
            'heading'        => $this->getHeading(),
            'subheading'     => 'Track continuation, declined & resigned-pending cases across academic years. Pick a year below to focus the list; archive past years when fully closed.',
            'actions'        => $this->getCachedHeaderActions(),
            'years'          => $this->academicYearSummary(),
            'currentAy'      => LetterOfIntentResource::currentAcademicYear(),
            'selectedAy'     => $this->academicYear,
        ]);
    }

    /**
     * Build a summary row per academic year used by the CTA strip.
     */
    protected function academicYearSummary(): array
    {
        $rows = LetterOfIntent::query()
            ->selectRaw('academic_year, COUNT(*) as total, SUM(CASE WHEN archived_at IS NULL THEN 1 ELSE 0 END) as active, SUM(CASE WHEN archived_at IS NULL AND status IN ("draft","sent") THEN 1 ELSE 0 END) as pending')
            ->groupBy('academic_year')
            ->orderByDesc('academic_year')
            ->get()
            ->map(fn ($r) => [
                'year'    => $r->academic_year,
                'total'   => (int) $r->total,
                'active'  => (int) $r->active,
                'pending' => (int) $r->pending,
            ])
            ->toArray();

        $current = LetterOfIntentResource::currentAcademicYear();
        if (! collect($rows)->contains('year', $current)) {
            array_unshift($rows, ['year' => $current, 'total' => 0, 'active' => 0, 'pending' => 0]);
        }

        return $rows;
    }

    public function selectAcademicYear(string $year): void
    {
        $this->academicYear = $year;
        $this->resetTable();
    }

    public function archiveAcademicYear(string $year): void
    {
        if ($year === LetterOfIntentResource::currentAcademicYear()) {
            Notification::make()
                ->title('Cannot archive the current academic year')
                ->danger()
                ->send();
            return;
        }

        $count = LetterOfIntent::query()
            ->where('academic_year', $year)
            ->whereNull('archived_at')
            ->update(['archived_at' => now()]);

        Notification::make()
            ->title("Archived {$count} letter" . ($count === 1 ? '' : 's') . " for {$year}")
            ->success()
            ->send();

        $this->resetTable();
    }

    public function unarchiveAcademicYear(string $year): void
    {
        $count = LetterOfIntent::query()
            ->where('academic_year', $year)
            ->whereNotNull('archived_at')
            ->update(['archived_at' => null]);

        Notification::make()
            ->title("Restored {$count} letter" . ($count === 1 ? '' : 's') . " for {$year}")
            ->success()
            ->send();

        $this->resetTable();
    }

    protected function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery() ?? static::getResource()::getEloquentQuery();

        if ($this->academicYear && $this->academicYear !== 'all') {
            $query->where('academic_year', $this->academicYear);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Send New LOI')
                ->icon('heroicon-o-paper-airplane'),
            Actions\Action::make('configure')
                ->label('Configuration')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->url(fn () => \App\Filament\Principal\Pages\LetterOfIntentConfig::getUrl()),
        ];
    }

    public function getTabs(): array
    {
        $continuation = fn (Builder $query) => $query->whereIn('status', ['draft', 'sent'])->whereNull('archived_at');
        $declined     = fn (Builder $query) => $query->where('status', 'declined')
            ->where(function (Builder $sub) {
                $sub->whereNull('follow_up_status')
                    ->orWhereNotIn('follow_up_status', ['closed']);
            })
            ->whereNull('archived_at');

        return [
            'continuation' => Tab::make('Continuation')
                ->icon('heroicon-o-arrow-path')
                ->modifyQueryUsing($continuation)
                ->badge(LetterOfIntent::query()->tap($continuation)->count())
                ->badgeColor('info'),

            'declined' => Tab::make('Declined / Resigned Pending')
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing($declined)
                ->badge(LetterOfIntent::query()->tap($declined)->count())
                ->badgeColor('warning'),

            'signed' => Tab::make('Signed')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'signed')->whereNull('archived_at')),

            'all' => Tab::make('All')
                ->icon('heroicon-o-rectangle-stack')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('archived_at')),

            'archived' => Tab::make('Archived')
                ->icon('heroicon-o-archive-box')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('archived_at')),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'continuation';
    }
}
