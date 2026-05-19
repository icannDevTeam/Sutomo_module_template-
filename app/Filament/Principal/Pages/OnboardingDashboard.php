<?php

namespace App\Filament\Principal\Pages;

use App\Models\Application;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\ClassPlacement;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class OnboardingDashboard extends Page implements HasForms, HasActions
{
    use InteractsWithActions, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Enrollment';
    protected static ?string $title = 'Onboarding Workflow';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.principal.pages.onboarding-dashboard';

    public array $proposals = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('issue_temp_id')->label('Issue Temp IDs')->icon('heroicon-o-identification')->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Assign TMP-#### to all accepted applicants that do not yet have a temporary ID.')
                ->action(function () {
                    $accepted = Application::where('status', 'accepted')->whereNull('assigned_temp_id')->get();
                    $n = 0;
                    foreach ($accepted as $a) {
                        $a->update(['assigned_temp_id' => 'TMP-' . str_pad((string)$a->id, 5, '0', STR_PAD_LEFT)]);
                        $n++;
                    }
                    Notification::make()->title("Issued {$n} temporary IDs")->success()->send();
                }),
            Action::make('auto_assign')->label('Auto-Assign Classes')->icon('heroicon-o-rectangle-group')->color('primary')
                ->form([
                    Forms\Components\Select::make('campus')->options(['sd'=>'SD','smp'=>'SMP','sma'=>'SMA','int'=>'International'])->required(),
                    Forms\Components\TextInput::make('grade')->required(),
                    Forms\Components\TextInput::make('stream')->placeholder('Optional (ipa/ips/umum)'),
                ])
                ->action(function (array $data) {
                    $classes = SchoolClass::query()
                        ->where('campus', $data['campus'])->where('grade', $data['grade'])
                        ->when($data['stream'] ?? null, fn ($q, $v) => $q->where('stream', $v))
                        ->get();
                    if ($classes->isEmpty()) {
                        Notification::make()->title('No classes match the selection')->warning()->send();
                        return;
                    }
                    $students = Student::query()->whereNull('school_class_id')
                        ->where('campus', $data['campus'])->where('grade', $data['grade'])
                        ->when($data['stream'] ?? null, fn ($q, $v) => $q->where('stream', $v))
                        ->get();
                    if ($students->isEmpty()) {
                        Notification::make()->title('No unassigned students for this filter')->warning()->send();
                        return;
                    }
                    $service = new ClassPlacement();
                    $this->proposals = $service->propose($students, $classes);
                    Notification::make()->title("Proposed {$students->count()} placements — review below and Apply.")->success()->send();
                }),
            Action::make('apply_proposals')->label('Apply Proposed Placements')->icon('heroicon-o-check')->color('success')
                ->visible(fn () => !empty($this->proposals))
                ->requiresConfirmation()
                ->action(function () {
                    $n = (new ClassPlacement())->apply($this->proposals);
                    $this->proposals = [];
                    Notification::make()->title("Applied {$n} class placements")->success()->send();
                }),
        ];
    }

    public function advanceAction(): Action
    {
        return Action::make('advance')
            ->label('')
            ->icon('heroicon-o-arrow-right-circle')
            ->iconButton()
            ->color('success')
            ->fillForm(fn (array $arguments) => ['id' => $arguments['id'] ?? null, 'step' => $arguments['step'] ?? null])
            ->form([
                Forms\Components\Hidden::make('id'),
                Forms\Components\Hidden::make('step'),
            ])
            ->requiresConfirmation()
            ->action(function (array $data) {
                $step = $data['step'];
                if ($step === 'temp_id') {
                    $a = Application::find($data['id']);
                    if ($a) $a->update(['assigned_temp_id' => 'TMP-' . str_pad((string)$a->id, 5, '0', STR_PAD_LEFT)]);
                } elseif ($step === 'dev_fee') {
                    Application::where('id', $data['id'])->update(['status' => 'dev_fee', 'payment_status' => 'paid']);
                } elseif ($step === 'books') {
                    Application::where('id', $data['id'])->update(['status' => 'books']);
                } elseif ($step === 'class_assigned') {
                    $a = Application::find($data['id']);
                    if ($a) {
                        // Create Student from Application
                        $student = Student::firstOrCreate(
                            ['nis' => $a->assigned_temp_id ?? ('S-A-' . $a->id)],
                            [
                                'name' => $a->name, 'gender' => $a->gender, 'dob' => $a->dob,
                                'religion' => $a->religion, 'ethnicity' => $a->ethnicity, 'city' => $a->city,
                                'campus' => $a->campus, 'unit' => $a->unit, 'grade' => $a->grade, 'stream' => $a->stream,
                                'status' => 'active', 'enrolled_at' => now()->toDateString(),
                                'parent_name' => $a->parent_name, 'parent_phone' => $a->parent_phone,
                                'parent_email' => $a->parent_email, 'temporary_id' => $a->assigned_temp_id,
                            ]
                        );
                        $a->update(['status' => 'class_assigned']);
                        Notification::make()->title("Student created · run Auto-Assign Classes to place into a class")->success()->send();
                        return;
                    }
                } elseif ($step === 'attendance') {
                    $a = Application::find($data['id']);
                    if ($a) {
                        $student = Student::where('nis', $a->assigned_temp_id)->orWhere('temporary_id', $a->assigned_temp_id)->first();
                        if ($student) {
                            $student->increment('attendance_days_count');
                            if (! $student->first_attendance_at) $student->update(['first_attendance_at' => now()->toDateString()]);
                            if ($student->attendance_days_count >= 5) {
                                $a->update(['status' => 'observing']);
                                Notification::make()->title("Attendance milestone reached (5 days) — advanced to Observation")->success()->send();
                                return;
                            }
                            Notification::make()->title("Recorded attendance day {$student->attendance_days_count} of 5")->success()->send();
                            return;
                        }
                    }
                } elseif ($step === 'permanent_id') {
                    $a = Application::find($data['id']);
                    if ($a) {
                        $nis = 'S' . now()->format('y') . str_pad((string)$a->id, 5, '0', STR_PAD_LEFT);
                        $va  = '880' . str_pad((string)$a->id, 10, '0', STR_PAD_LEFT);
                        $a->update(['status' => 'id_issued', 'assigned_student_no' => $nis]);
                        Student::where('temporary_id', $a->assigned_temp_id)->update(['nis' => $nis, 'va_number' => $va]);
                    }
                } elseif ($step === 'activated') {
                    $a = Application::find($data['id']);
                    if ($a) {
                        $a->update(['status' => 'activated']);
                        Student::where('temporary_id', $a->assigned_temp_id)->update(['account_activated_at' => now()->toDateString()]);
                    }
                }
                Notification::make()->title('Step completed')->success()->send();
            });
    }

    public function getViewData(): array
    {
        // Step counts driven by application status (since onboarding lives on Application)
        $counts = [
            'accepted'      => Application::where('status', 'accepted')->count(),
            'temp_id'       => Application::where('status', 'accepted')->whereNotNull('assigned_temp_id')->count(),
            'dev_fee'       => Application::where('status', 'dev_fee')->count(),
            'books'         => Application::where('status', 'books')->count(),
            'class_assigned'=> Application::where('status', 'class_assigned')->count(),
            'observing'     => Application::where('status', 'observing')->count(),
            'id_issued'     => Application::where('status', 'id_issued')->count(),
            'activated'     => Application::where('status', 'activated')->count(),
        ];

        $queues = [
            'accepted'       => ['Accepted',        'Issue Temp ID',         'temp_id', Application::where('status','accepted')->whereNull('assigned_temp_id')->latest('applied_at')->take(8)->get()],
            'temp_id_done'   => ['Temp ID Issued',  'Confirm Dev Fee Paid',  'dev_fee', Application::where('status','accepted')->whereNotNull('assigned_temp_id')->latest('applied_at')->take(8)->get()],
            'dev_fee'        => ['Dev Fee Paid',    'Issue Books',           'books',   Application::where('status','dev_fee')->latest('applied_at')->take(8)->get()],
            'books'          => ['Books Issued',    'Create Student + Class',         'class_assigned', Application::where('status','books')->latest('applied_at')->take(8)->get()],
            'class_assigned' => ['Class Assigned',  'Record Attendance Day',          'attendance', Application::where('status','class_assigned')->latest('applied_at')->take(8)->get()],
            'observing'      => ['5-Day Observation','Issue Permanent ID + VA',       'permanent_id', Application::where('status','observing')->latest('applied_at')->take(8)->get()],
            'id_issued'      => ['Permanent ID',    'Activate Account',               'activated', Application::where('status','id_issued')->latest('applied_at')->take(8)->get()],
        ];

        return compact('counts', 'queues');
    }
}
