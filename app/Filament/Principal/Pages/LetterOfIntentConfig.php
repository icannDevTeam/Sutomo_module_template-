<?php

namespace App\Filament\Principal\Pages;

use App\Models\LetterOfIntentSchedule;
use App\Models\LetterOfIntentTemplate;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class LetterOfIntentConfig extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Teachers';
    protected static ?string $navigationLabel = 'LOI Configuration';
    protected static ?string $title = 'Letter of Intent — Configuration';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.principal.pages.letter-of-intent-config';
    protected static ?string $slug = 'letter-of-intent-config';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'templates' => LetterOfIntentTemplate::orderByDesc('is_default')->orderBy('name')
                ->get()
                ->map(fn ($t) => [
                    'id'                    => $t->id,
                    'name'                  => $t->name,
                    'slug'                  => $t->slug,
                    'audience'              => $t->audience,
                    'audience_value'        => $t->audience_value,
                    'subject_line'          => $t->subject_line,
                    'default_deadline_days' => $t->default_deadline_days,
                    'is_default'            => $t->is_default,
                    'active'                => $t->active,
                    'body'                  => $t->body,
                ])->toArray(),
            'schedules' => LetterOfIntentSchedule::with('template')
                ->orderByDesc('active')->orderBy('name')
                ->get()
                ->map(fn ($s) => [
                    'id'                   => $s->id,
                    'name'                 => $s->name,
                    'template_id'          => $s->template_id,
                    'frequency'            => $s->frequency,
                    'day_of_month'         => $s->day_of_month,
                    'month_of_year'        => $s->month_of_year,
                    'run_on'               => optional($s->run_on)->format('Y-m-d'),
                    'target_scope'         => $s->target_scope,
                    'target_value'         => $s->target_value ?? [],
                    'deadline_days'        => $s->deadline_days,
                    'academic_year_offset' => $s->academic_year_offset,
                    'active'               => $s->active,
                    'next_run_at'          => optional($s->next_run_at)->format('d M Y H:i'),
                    'last_run_at'          => optional($s->last_run_at)->format('d M Y H:i'),
                    'last_run_count'       => $s->last_run_count,
                ])->toArray(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Templates')
                    ->description('Draft reusable letter bodies. Use placeholders like {{teacher_name}}, {{position}}, {{academic_year}}, {{deadline}}, {{principal_name}}.')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Placeholder::make('placeholder_help')
                            ->label('Available placeholders')
                            ->content(fn () => implode('  ·  ', LetterOfIntentTemplate::PLACEHOLDERS)),
                        Repeater::make('templates')
                            ->hiddenLabel()
                            ->itemLabel(fn (array $state) => trim($state['name'] ?? '') !== '' ? $state['name'] : 'New template')
                            ->collapsed()
                            ->collapsible()
                            ->cloneable()
                            ->reorderable(false)
                            ->addActionLabel('+ Add template')
                            ->defaultItems(0)
                            ->schema([
                                Hidden::make('id'),
                                Grid::make(12)->schema([
                                    TextInput::make('name')->required()->columnSpan(6)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (?string $state, $set, $get) {
                                            if (! $get('slug') && $state) {
                                                $set('slug', Str::slug($state));
                                            }
                                        }),
                                    TextInput::make('slug')->required()->columnSpan(4)
                                        ->helperText('Unique identifier'),
                                    Toggle::make('is_default')->label('Default')->columnSpan(1)->inline(false),
                                    Toggle::make('active')->label('Active')->default(true)->columnSpan(1)->inline(false),
                                ]),
                                Grid::make(12)->schema([
                                    Select::make('audience')
                                        ->options(LetterOfIntentTemplate::AUDIENCES)
                                        ->default('all')
                                        ->live()
                                        ->columnSpan(4),
                                    TextInput::make('audience_value')
                                        ->placeholder('e.g. Mathematics, SMA, Homeroom')
                                        ->visible(fn ($get) => $get('audience') !== 'all')
                                        ->columnSpan(4),
                                    TextInput::make('subject_line')
                                        ->placeholder('Subject line')
                                        ->columnSpan(4),
                                    TextInput::make('default_deadline_days')
                                        ->numeric()->minValue(1)->maxValue(180)
                                        ->default(14)
                                        ->label('Default deadline (days)')
                                        ->columnSpan(4),
                                ]),
                                Textarea::make('body')->required()->rows(10)->columnSpanFull()
                                    ->placeholder('Dear {{teacher_name}}, ...'),
                            ]),
                    ]),

                Section::make('Schedules')
                    ->description('Schedule automatic letter sends. Yearly is ideal for annual re-commitment letters; monthly for rolling reminders; one-off for ad-hoc batches.')
                    ->icon('heroicon-o-calendar-days')
                    ->collapsible()
                    ->schema([
                        Repeater::make('schedules')
                            ->hiddenLabel()
                            ->itemLabel(fn (array $state) => trim($state['name'] ?? '') !== '' ? $state['name'] : 'New schedule')
                            ->collapsed()
                            ->collapsible()
                            ->reorderable(false)
                            ->addActionLabel('+ Add schedule')
                            ->defaultItems(0)
                            ->schema([
                                Hidden::make('id'),
                                Grid::make(12)->schema([
                                    TextInput::make('name')->required()->columnSpan(6)
                                        ->placeholder('e.g. Annual LOI — April batch'),
                                    Select::make('template_id')
                                        ->label('Template')
                                        ->options(fn () => LetterOfIntentTemplate::orderBy('name')->pluck('name', 'id'))
                                        ->required()
                                        ->columnSpan(4),
                                    Toggle::make('active')->default(true)->columnSpan(2)->inline(false),
                                ]),
                                Grid::make(12)->schema([
                                    Select::make('frequency')
                                        ->options(LetterOfIntentSchedule::FREQUENCIES)
                                        ->default('yearly')
                                        ->live()
                                        ->columnSpan(4),
                                    Select::make('month_of_year')
                                        ->label('Month')
                                        ->options(collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => date('F', mktime(0, 0, 0, $m, 1))])->toArray())
                                        ->visible(fn ($get) => $get('frequency') === 'yearly')
                                        ->columnSpan(4),
                                    Select::make('day_of_month')
                                        ->label('Day of month')
                                        ->options(collect(range(1, 28))->mapWithKeys(fn ($d) => [$d => (string) $d])->toArray())
                                        ->visible(fn ($get) => in_array($get('frequency'), ['yearly', 'monthly'], true))
                                        ->columnSpan(4),
                                    TextInput::make('run_on')
                                        ->type('date')
                                        ->label('Run on')
                                        ->visible(fn ($get) => $get('frequency') === 'once')
                                        ->columnSpan(4),
                                ]),
                                Grid::make(12)->schema([
                                    Select::make('target_scope')
                                        ->options(LetterOfIntentSchedule::SCOPES)
                                        ->default('all_active')
                                        ->live()
                                        ->columnSpan(4),
                                    TagsInput::make('target_value')
                                        ->placeholder('Add value and press Enter')
                                        ->visible(fn ($get) => $get('target_scope') !== 'all_active')
                                        ->helperText(fn ($get) => match ($get('target_scope')) {
                                            'by_dept'    => 'Departments to include (e.g. Mathematics, Science)',
                                            'by_subject' => 'Subjects to include',
                                            'by_unit'    => 'SD, SMP, SMA',
                                            'specific'   => 'Teacher IDs (comma separated)',
                                            default      => null,
                                        })
                                        ->columnSpan(8),
                                ]),
                                Grid::make(12)->schema([
                                    TextInput::make('deadline_days')
                                        ->numeric()->minValue(1)->maxValue(180)
                                        ->default(14)
                                        ->label('Deadline (days after send)')
                                        ->columnSpan(4),
                                    Select::make('academic_year_offset')
                                        ->options([0 => 'Current AY', 1 => 'Next AY (+1)', 2 => '+2 years'])
                                        ->default(1)
                                        ->label('Target academic year')
                                        ->columnSpan(4),
                                    Placeholder::make('next_run_at')
                                        ->label('Next run')
                                        ->content(fn ($get) => $get('next_run_at') ?: '— (will be computed on save)')
                                        ->columnSpan(4),
                                ]),
                                Placeholder::make('last_run_at')
                                    ->label('Last run')
                                    ->content(fn ($get) => $get('last_run_at')
                                        ? ($get('last_run_at') . ' · ' . ((int) ($get('last_run_count') ?? 0)) . ' letters created')
                                        : 'Never run yet')
                                    ->visible(fn ($get) => (bool) $get('last_run_at')),
                            ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        // Templates
        $keepTemplateIds = [];
        foreach ($state['templates'] ?? [] as $row) {
            $slug = $row['slug'] ?? Str::slug($row['name'] ?? '');
            if (! $slug || ! ($row['name'] ?? null) || ! ($row['body'] ?? null)) {
                continue;
            }
            $payload = [
                'name'                  => $row['name'],
                'slug'                  => $slug,
                'audience'              => $row['audience'] ?? 'all',
                'audience_value'        => $row['audience_value'] ?? null,
                'subject_line'          => $row['subject_line'] ?? null,
                'default_deadline_days' => (int) ($row['default_deadline_days'] ?? 14),
                'is_default'            => (bool) ($row['is_default'] ?? false),
                'active'                => (bool) ($row['active'] ?? true),
                'body'                  => $row['body'],
            ];
            if (! empty($row['id'])) {
                $tpl = LetterOfIntentTemplate::find($row['id']);
                if ($tpl) {
                    $tpl->update($payload);
                    $keepTemplateIds[] = $tpl->id;
                }
            } else {
                $tpl = LetterOfIntentTemplate::create($payload);
                $keepTemplateIds[] = $tpl->id;
            }
        }
        // Ensure only one default
        if (LetterOfIntentTemplate::where('is_default', true)->count() > 1) {
            $first = LetterOfIntentTemplate::where('is_default', true)->orderBy('id')->first();
            LetterOfIntentTemplate::where('is_default', true)->where('id', '!=', $first->id)->update(['is_default' => false]);
        }
        // Delete removed templates that have no schedules attached
        LetterOfIntentTemplate::whereNotIn('id', $keepTemplateIds ?: [0])
            ->doesntHave('schedules')
            ->delete();

        // Schedules
        $keepScheduleIds = [];
        foreach ($state['schedules'] ?? [] as $row) {
            if (! ($row['name'] ?? null) || ! ($row['template_id'] ?? null)) {
                continue;
            }
            $payload = [
                'name'                 => $row['name'],
                'template_id'          => (int) $row['template_id'],
                'frequency'            => $row['frequency'] ?? 'yearly',
                'day_of_month'         => $row['day_of_month'] ? (int) $row['day_of_month'] : null,
                'month_of_year'        => $row['month_of_year'] ? (int) $row['month_of_year'] : null,
                'run_on'               => $row['run_on'] ?: null,
                'target_scope'         => $row['target_scope'] ?? 'all_active',
                'target_value'         => $row['target_value'] ?? [],
                'deadline_days'        => (int) ($row['deadline_days'] ?? 14),
                'academic_year_offset' => (int) ($row['academic_year_offset'] ?? 1),
                'active'               => (bool) ($row['active'] ?? true),
            ];

            if (! empty($row['id'])) {
                $sched = LetterOfIntentSchedule::find($row['id']);
                if ($sched) {
                    $sched->update($payload);
                    $sched->next_run_at = $sched->computeNextRun();
                    $sched->save();
                    $keepScheduleIds[] = $sched->id;
                }
            } else {
                $sched = LetterOfIntentSchedule::create($payload);
                $sched->next_run_at = $sched->computeNextRun();
                $sched->save();
                $keepScheduleIds[] = $sched->id;
            }
        }
        LetterOfIntentSchedule::whereNotIn('id', $keepScheduleIds ?: [0])->delete();

        Notification::make()
            ->title('LOI configuration saved')
            ->body(count($keepTemplateIds) . ' template(s), ' . count($keepScheduleIds) . ' schedule(s) active.')
            ->success()
            ->send();

        $this->mount();
    }
}
