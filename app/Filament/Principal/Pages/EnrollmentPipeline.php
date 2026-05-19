<?php

namespace App\Filament\Principal\Pages;

use App\Filament\Principal\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\EnrollmentPeriod;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class EnrollmentPipeline extends Page implements HasForms, HasActions
{
    use InteractsWithActions, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?string $title = 'Enrollment Pipeline';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.principal.pages.enrollment-pipeline';

    #[Url] public ?string $period = null;
    #[Url] public ?string $campus = null;
    #[Url] public ?string $grade = null;
    #[Url] public ?string $q = null;

    public function resetFilters(): void
    {
        $this->period = null; $this->campus = null; $this->grade = null; $this->q = null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk_pass')->label('→ Passed')->icon('heroicon-o-check-circle')->color('success')
                ->form([$this->idsField()])->action(fn (array $d) => $this->bulkTransition($d, 'passed', 'Marked passed')),
            Action::make('bulk_fail')->label('→ Failed')->icon('heroicon-o-x-circle')->color('danger')
                ->form([$this->idsField()])->action(fn (array $d) => $this->bulkTransition($d, 'failed', 'Marked failed')),
            Action::make('bulk_waitlist')->label('→ Waitlist')->icon('heroicon-o-clock')->color('warning')
                ->form([$this->idsField()])->action(fn (array $d) => $this->bulkTransition($d, 'waitlisted', 'Moved to waitlist', ['waitlisted' => true])),
            Action::make('bulk_accept')->label('→ Accept')->icon('heroicon-o-hand-thumb-up')->color('success')
                ->form([$this->idsField()])->action(fn (array $d) => $this->bulkTransition($d, 'accepted', 'Accepted')),
            Action::make('apply_threshold')->label('Apply Exam Threshold')->icon('heroicon-o-calculator')->color('primary')
                ->requiresConfirmation()
                ->modalDescription('Auto-promote every applicant with a recorded score to Passed/Failed using their enrollment period thresholds.')
                ->action(function () {
                    $changed = 0;
                    Application::whereNotNull('placement_score')
                        ->whereIn('status', ['exam_scheduled','submitted','payment_confirmed','waitlisted'])
                        ->chunk(100, function ($rows) use (&$changed) {
                            foreach ($rows as $a) { $a->applyExamScore((float)$a->placement_score); $changed++; }
                        });
                    Notification::make()->title("Threshold applied to {$changed} applicants")->success()->send();
                }),
        ];
    }

    public function cardAction(): Action
    {
        return Action::make('cardDecision')
            ->label('')
            ->icon('heroicon-m-ellipsis-vertical')
            ->iconButton()
            ->color('gray')
            ->fillForm(fn (array $arguments) => ['id' => $arguments['id'] ?? null])
            ->form([
                Forms\Components\Hidden::make('id'),
                Forms\Components\Select::make('decision')->required()->options([
                    'passed' => 'Mark Passed',
                    'failed' => 'Mark Failed',
                    'waitlisted' => 'Move to Waitlist',
                    'accepted' => 'Accept',
                    'declined' => 'Decline',
                    'exam_scheduled' => 'Schedule Exam',
                    'score' => 'Enter Score',
                ])->live(),
                Forms\Components\DateTimePicker::make('exam_date')->visible(fn (Forms\Get $g) => $g('decision') === 'exam_scheduled'),
                Forms\Components\TextInput::make('placement_score')->numeric()->minValue(0)->maxValue(100)
                    ->visible(fn (Forms\Get $g) => $g('decision') === 'score'),
                Forms\Components\Textarea::make('decline_reason')->visible(fn (Forms\Get $g) => $g('decision') === 'declined'),
            ])
            ->action(function (array $data) {
                $a = Application::findOrFail($data['id']);
                $decision = $data['decision'];
                if ($decision === 'score') {
                    $status = $a->applyExamScore((float)$data['placement_score']);
                    Notification::make()->title("Score saved · status {$status}")->success()->send();
                    return;
                }
                if ($decision === 'waitlisted') $a->waitlisted = true;
                if ($decision === 'exam_scheduled' && !empty($data['exam_date'])) $a->exam_date = $data['exam_date'];
                if ($decision === 'declined' && !empty($data['decline_reason'])) $a->decline_reason = $data['decline_reason'];
                if ($decision === 'accepted' && empty($a->invoice_no)) {
                    $a->invoice_no = 'INV-' . now()->format('Y') . '-' . str_pad((string)$a->id, 5, '0', STR_PAD_LEFT);
                }
                $a->status = $decision;
                $a->save();
                Notification::make()->title('Application updated')->success()->send();
            });
    }

    protected function idsField(): Forms\Components\Component
    {
        return Forms\Components\Select::make('ids')->label('Applicants')->multiple()->searchable()->required()
            ->options(fn () => $this->queryBase()->orderBy('name')->limit(500)->pluck('name', 'id')->toArray());
    }

    protected function bulkTransition(array $data, string $status, string $msg, array $extra = []): void
    {
        $count = 0;
        foreach ((array)($data['ids'] ?? []) as $id) {
            $a = Application::find($id);
            if (!$a) continue;
            $a->fill(array_merge(['status' => $status], $extra));
            if ($status === 'accepted' && empty($a->invoice_no)) {
                $a->invoice_no = 'INV-' . now()->format('Y') . '-' . str_pad((string)$a->id, 5, '0', STR_PAD_LEFT);
            }
            $a->save();
            $count++;
        }
        Notification::make()->title("{$msg}: {$count}")->success()->send();
    }

    protected function queryBase()
    {
        $q = Application::query();
        if ($this->period) $q->where('enrollment_period_id', $this->period);
        if ($this->campus) $q->where('campus', $this->campus);
        if ($this->grade)  $q->where('grade', $this->grade);
        if ($this->q) {
            $term = '%' . $this->q . '%';
            $q->where(fn ($w) => $w->where('name','like',$term)->orWhere('code','like',$term));
        }
        return $q;
    }

    public function getViewData(): array
    {
        $stages = [
            'submitted'         => ['Submitted',      '#6b7280'],
            'payment_confirmed' => ['Payment ✓',     '#0ea5e9'],
            'exam_scheduled'    => ['Exam Scheduled', '#0284c7'],
            'passed'            => ['Passed',         '#10b981'],
            'waitlisted'        => ['Waitlist',       '#f59e0b'],
            'failed'            => ['Failed',         '#ef4444'],
            'accepted'          => ['Accepted',       '#059669'],
            'declined'          => ['Declined',       '#b91c1c'],
        ];

        $columns = [];
        foreach ($stages as $key => [$label, $color]) {
            $q = $this->queryBase()->where('status', $key);
            $columns[$key] = [
                'label'   => $label,
                'color'   => $color,
                'count'   => (clone $q)->count(),
                'records' => $q->latest('applied_at')->take(30)->get(),
            ];
        }

        return [
            'columns' => $columns,
            'periods' => EnrollmentPeriod::query()->orderByDesc('opens_at')->pluck('name', 'id')->toArray(),
        ];
    }
}
