<?php

namespace App\Filament\Principal\Pages;

use App\Models\Application;
use App\Models\EnrollmentPeriod;
use App\Models\PlacementExamSession;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Url;

class PlacementExam extends Page implements HasForms, HasActions, HasTable
{
    use InteractsWithActions, InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?string $title = 'Placement Exam';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.principal.pages.placement-exam';

    #[Url] public ?int $selected_period = null;

    public ?array $examSetupData = [];

    public function mount(): void
    {
        $latest = EnrollmentPeriod::query()->where('status', 'open')->orderByDesc('opens_at')->first()
            ?? EnrollmentPeriod::query()->orderByDesc('opens_at')->first();
        $this->selected_period = $this->selected_period ?? $latest?->id;
        $this->fillExamSetup();
    }

    public function updatedSelectedPeriod(): void
    {
        $this->fillExamSetup();
    }

    protected function fillExamSetup(): void
    {
        $p = $this->selected_period ? EnrollmentPeriod::find($this->selected_period) : null;
        $this->examSetupForm->fill([
            'exam_starts_at'    => $p?->exam_starts_at,
            'exam_venue'        => $p?->exam_venue,
            'exam_instructions' => $p?->exam_instructions,
            'pass_threshold'    => $p?->pass_threshold ?? 70,
            'fail_threshold'    => $p?->fail_threshold ?? 50,
        ]);
    }

    public function examSetupForm(Form $form): Form
    {
        return $form->statePath('examSetupData')->schema([
            Forms\Components\DateTimePicker::make('exam_starts_at')->label('Exam Date & Time'),
            Forms\Components\TextInput::make('exam_venue'),
            Forms\Components\TextInput::make('pass_threshold')->numeric()->suffix('/ 100'),
            Forms\Components\TextInput::make('fail_threshold')->numeric()->suffix('/ 100'),
            Forms\Components\Textarea::make('exam_instructions')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    protected function getForms(): array
    {
        return ['examSetupForm'];
    }

    public function saveExamSetup(): void
    {
        if (! $this->selected_period) {
            Notification::make()->title('Select an enrollment period first')->warning()->send();
            return;
        }
        $data = $this->examSetupForm->getState();
        EnrollmentPeriod::find($this->selected_period)?->update($data);
        Notification::make()->title('Placement exam details saved')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('schedule_eligible')->label('Schedule Eligible Applicants')->icon('heroicon-o-calendar-days')->color('success')
                ->visible(fn () => filled($this->selected_period))
                ->requiresConfirmation()
                ->modalDescription('Move all payment-confirmed applicants in this period to "Exam Scheduled" using the period exam date.')
                ->action(function () {
                    $period = EnrollmentPeriod::find($this->selected_period);
                    if (! $period || ! $period->exam_starts_at) {
                        Notification::make()->title('Set exam date first')->warning()->send();
                        return;
                    }
                    $count = Application::where('enrollment_period_id', $period->id)
                        ->where('status', 'payment_confirmed')
                        ->update(['status' => 'exam_scheduled', 'exam_date' => $period->exam_starts_at]);
                    Notification::make()->title("Scheduled {$count} applicants for the exam")
                        ->body("Date: " . $period->exam_starts_at->format('d M Y H:i'))->success()->send();
                }),
            Action::make('apply_threshold')->label('Apply Threshold')->icon('heroicon-o-calculator')->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    $changed = 0;
                    Application::whereNotNull('placement_score')
                        ->when($this->selected_period, fn ($q) => $q->where('enrollment_period_id', $this->selected_period))
                        ->whereIn('status', Application::ELIGIBLE_FOR_EXAM)
                        ->chunk(100, function ($rows) use (&$changed) {
                            foreach ($rows as $a) { $a->applyExamScore((float)$a->placement_score); $changed++; }
                        });
                    Notification::make()->title("Threshold applied to {$changed} applicants")->success()->send();
                }),
        ];
    }

    public function enterScoreAction(): Action
    {
        return Action::make('enterScore')
            ->label('')
            ->icon('heroicon-o-pencil-square')
            ->iconButton()
            ->color('primary')
            ->fillForm(fn (array $arguments) => [
                'id' => $arguments['id'] ?? null,
                'placement_score' => $arguments['score'] ?? null,
            ])
            ->form([
                Forms\Components\Hidden::make('id'),
                Forms\Components\TextInput::make('placement_score')->numeric()->minValue(0)->maxValue(100)->required(),
            ])
            ->action(function (array $data) {
                $a = Application::findOrFail($data['id']);
                $status = $a->applyExamScore((float)$data['placement_score']);
                Notification::make()->title("Score saved · status {$status}")->success()->send();
            });
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PlacementExamSession::query()
                    ->with('supervisor')
                    ->when($this->selected_period, fn ($q) => $q->where('enrollment_period_id', $this->selected_period))
            )
            ->heading('Exam Sessions & Supervisors')
            ->headerActions([
                Tables\Actions\Action::make('addSession')
                    ->label('Add Test / Supervisor')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->visible(fn () => filled($this->selected_period))
                    ->form([
                        Forms\Components\TextInput::make('subject')->required()->placeholder('e.g. Matematika'),
                        Forms\Components\Select::make('grade_band')->options([
                            'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA', 'All' => 'All Grades',
                        ])->default('All'),
                        Forms\Components\DateTimePicker::make('starts_at')->required()->seconds(false),
                        Forms\Components\TextInput::make('duration_minutes')->numeric()->default(90)->suffix('min'),
                        Forms\Components\TextInput::make('room'),
                        Forms\Components\TextInput::make('capacity')->numeric(),
                        Forms\Components\Select::make('supervisor_teacher_id')
                            ->label('Supervisor (Teacher)')
                            ->options(fn () => Teacher::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        PlacementExamSession::create($data + ['enrollment_period_id' => $this->selected_period]);
                        Notification::make()->title('Session added')->success()->send();
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('subject')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('grade_band')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('starts_at')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')->label('Min')->alignCenter(),
                Tables\Columns\TextColumn::make('room'),
                Tables\Columns\TextColumn::make('supervisor.name')->label('Supervisor')->placeholder('— unassigned —'),
                Tables\Columns\TextColumn::make('capacity')->alignCenter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('subject')->required(),
                        Forms\Components\Select::make('grade_band')->options([
                            'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA', 'All' => 'All Grades',
                        ]),
                        Forms\Components\DateTimePicker::make('starts_at')->required()->seconds(false),
                        Forms\Components\TextInput::make('duration_minutes')->numeric()->suffix('min'),
                        Forms\Components\TextInput::make('room'),
                        Forms\Components\TextInput::make('capacity')->numeric(),
                        Forms\Components\Select::make('supervisor_teacher_id')
                            ->label('Supervisor (Teacher)')
                            ->options(fn () => Teacher::orderBy('name')->pluck('name', 'id'))
                            ->searchable()->preload(),
                        Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('No exam sessions yet')
            ->emptyStateDescription('Add subject tests and assign teacher supervisors for this enrollment period.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->defaultSort('starts_at');
    }

    public function getViewData(): array
    {
        $periods = EnrollmentPeriod::query()->orderByDesc('opens_at')->pluck('name', 'id')->toArray();
        $period  = $this->selected_period ? EnrollmentPeriod::find($this->selected_period) : null;
        $scope = Application::query()->when($this->selected_period, fn ($q) => $q->where('enrollment_period_id', $this->selected_period));
        $kpi = [
            'eligible'  => (clone $scope)->where('status', 'payment_confirmed')->count(),
            'scheduled' => (clone $scope)->where('status', 'exam_scheduled')->count(),
            'passed'    => (clone $scope)->where('status', 'passed')->count(),
            'failed'    => (clone $scope)->where('status', 'failed')->count(),
            'avg'       => round((float) (clone $scope)->avg('placement_score'), 1),
        ];

        $eligible    = (clone $scope)->where('status', 'payment_confirmed')->orderBy('name')->take(40)->get();
        $scheduled   = (clone $scope)->where('status', 'exam_scheduled')->orderBy('exam_date')->take(40)->get();
        $awaitScore  = (clone $scope)->where('status', 'exam_scheduled')->whereNull('placement_score')->orderBy('exam_date')->take(50)->get();
        $scored      = (clone $scope)->whereNotNull('placement_score')->whereIn('status', Application::POST_EXAM_OUTCOMES)->latest('updated_at')->take(20)->get();

        return compact('periods', 'period', 'kpi', 'eligible', 'scheduled', 'awaitScore', 'scored');
    }
}
