<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\DutyAssignment;
use App\Models\ParentCommunication;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeacherDocument;
use App\Models\TeacherEmploymentEvent;
use App\Models\TeacherGoal;
use App\Models\TeacherJournalEntry;
use App\Models\TeacherLeave;
use App\Models\TeacherObservation;
use App\Models\TeacherTraining;
use App\Models\VoluntaryRequest;
use App\Services\Timetable\TeacherSchedule;
use App\Support\TeacherWarnings;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacher extends ViewRecord
{
    protected static string $resource = TeacherResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make()->schema([
                ViewEntry::make('header')
                    ->view('filament.principal.teacher.header')
                    ->viewData(fn ($record) => [
                        'record' => $record,
                        'tags'   => $record->tags()->orderBy('name')->get(),
                    ]),
            ]),

            Section::make()->schema([
                ViewEntry::make('status_banner')
                    ->view('filament.principal.teacher.status-banner')
                    ->viewData(fn ($record) => [
                        'teacher' => $record,
                        'current' => TeacherSchedule::for($record)->currentSlot(),
                    ]),
            ]),

            Section::make('Active Warnings')->collapsible()->schema([
                ViewEntry::make('warnings')
                    ->view('filament.principal.teacher.warnings')
                    ->viewData(fn ($record) => ['warnings' => TeacherWarnings::for($record)]),
            ]),

            Tabs::make('Profile')->columnSpanFull()->tabs([

                Tabs\Tab::make('Class Overview')->icon('heroicon-o-user-group')->schema([
                    Section::make('Identity')->columns(3)->schema([
                        TextEntry::make('employee_no')->label('Employee No.')->placeholder('—'),
                        TextEntry::make('dob')->label('Date of Birth')->date('d M Y')->placeholder('—'),
                        TextEntry::make('gender')->formatStateUsing(fn ($s) => $s === 'M' ? 'Male' : ($s === 'F' ? 'Female' : '—')),
                        TextEntry::make('email')->placeholder('—')->copyable(),
                        TextEntry::make('phone')->placeholder('—'),
                        TextEntry::make('city')->placeholder('—'),
                    ]),

                    Section::make('Emergency Contact')->schema([
                        ViewEntry::make('emergency')
                            ->view('filament.principal.teacher.emergency-contact')
                            ->viewData(fn ($record) => ['record' => $record]),
                    ]),

                    Section::make('Homeroom Classes')->schema([
                        ViewEntry::make('homerooms')
                            ->view('filament.principal.teacher.homeroom-classes')
                            ->viewData(fn ($record) => [
                                'classes' => $record->homeroomClasses()->orderBy('code')->get(),
                            ]),
                    ]),

                    Section::make('Contract Snapshot')->columns(3)->schema([
                        TextEntry::make('contract')->placeholder('—'),
                        TextEntry::make('joined_at')->date('d M Y')->placeholder('—'),
                        TextEntry::make('contract_end')->date('d M Y')->placeholder('—'),
                        TextEntry::make('tenure')->placeholder('—'),
                        TextEntry::make('employment')->badge()->color('gray'),
                        TextEntry::make('title')->badge()
                            ->formatStateUsing(fn ($s) => Teacher::TITLES[$s] ?? ($s ?: '—'))
                            ->color(fn ($s) => Teacher::TITLE_COLORS[$s] ?? 'gray'),
                    ]),

                    Section::make('Children Tuition Quota')
                        ->description('Children of this teacher enrolled with tuition benefit')
                        ->schema([
                            ViewEntry::make('children')
                                ->view('filament.principal.teacher.children-quota')
                                ->viewData(fn ($record) => [
                                    'teacher'  => $record,
                                    'quota'    => $record->children_quota,
                                    'children' => $record->childrenStudents()->limit(20)->get(),
                                ]),
                        ])
                        ->visible(fn ($record) => $record->children_quota !== null
                            || $record->childrenStudents()->exists()),

                    Section::make('Awards')->collapsible()->schema([
                        ViewEntry::make('awards')
                            ->view('filament.principal.teacher.chip-list')
                            ->viewData(fn ($record) => [
                                'items' => $record->awards ?? [],
                                'empty' => 'No awards recorded.',
                                'class' => 'sp-teacher-chip--award',
                            ]),
                    ])->visible(fn ($record) => !empty($record->awards)),

                    Section::make('Initiatives')->collapsible()->schema([
                        ViewEntry::make('initiatives')
                            ->view('filament.principal.teacher.chip-list')
                            ->viewData(fn ($record) => [
                                'items' => $record->initiatives ?? [],
                                'empty' => 'No initiatives recorded.',
                                'class' => 'sp-teacher-chip--init',
                            ]),
                    ])->visible(fn ($record) => !empty($record->initiatives)),

                    Section::make('Recent Duties')
                        ->description('From Duty Assignments queue')
                        ->collapsible()
                        ->schema([
                            ViewEntry::make('duties')
                                ->view('filament.principal.teacher.duty-list')
                                ->viewData(fn ($record) => [
                                    'duties' => DutyAssignment::where('teacher_id', $record->id)
                                        ->orderByDesc('starts_at')->limit(10)->get(),
                                ]),
                        ]),

                    Section::make('Parent Communications')
                        ->collapsible()
                        ->schema([
                            ViewEntry::make('parent_comms')
                                ->view('filament.principal.teacher.parent-comms')
                                ->viewData(fn ($record) => [
                                    'summary' => $record->parentCommSummary(),
                                    'recent'  => ParentCommunication::with('student')
                                        ->where('teacher_id', $record->id)
                                        ->orderByDesc('occurred_at')->limit(10)->get(),
                                ]),
                        ]),
                ]),

                Tabs\Tab::make('Schedule')->icon('heroicon-o-calendar')->schema([
                    Section::make('Classes I Teach')->schema([
                        ViewEntry::make('classes_taught')
                            ->view('filament.principal.teacher.classes-taught')
                            ->viewData(fn ($record) => ['classes' => TeacherSchedule::for($record)->classesTaught()]),
                    ]),

                    Section::make('Weekly Timetable')->schema([
                        ViewEntry::make('timetable_grid')
                            ->view('filament.principal.teacher.timetable-grid')
                            ->viewData(fn ($record) => ['grid' => TeacherSchedule::for($record)->weekly()]),
                    ]),

                    Section::make('Workload')->schema([
                        ViewEntry::make('workload_meter')
                            ->view('filament.principal.teacher.workload-meter')
                            ->viewData(function ($record) {
                                $dutyHours = DutyAssignment::where('teacher_id', $record->id)
                                    ->whereIn('status', ['assigned','accepted'])
                                    ->where('recurrence', 'weekly')
                                    ->count();
                                return [
                                    'workload'  => TeacherSchedule::for($record)->workload(),
                                    'dutyHours' => $dutyHours,
                                ];
                            }),
                    ]),

                    Section::make('Department Peers')
                        ->description('Same department — candidates for substitute cover')
                        ->collapsible()
                        ->schema([
                            ViewEntry::make('peers')
                                ->view('filament.principal.teacher.department-peers')
                                ->viewData(fn ($record) => ['peers' => TeacherSchedule::for($record)->departmentPeers()]),
                        ]),
                ]),

                Tabs\Tab::make('Substitutes')->icon('heroicon-o-arrow-path-rounded-square')->schema([
                    Section::make('Substitute Pool')->schema([
                        ViewEntry::make('subs')
                            ->view('filament.principal.teacher.substitutes-list')
                            ->viewData(fn ($record) => [
                                'pinned'    => $record->preferredSubstitutes()->get(),
                                'suggested' => Teacher::suggestSubstitutes($record),
                            ]),
                    ]),
                ]),

                Tabs\Tab::make('Attendance')->icon('heroicon-o-check-badge')->schema([
                    Section::make('This Month')->schema([
                        ViewEntry::make('att_summary')
                            ->view('filament.principal.teacher.attendance-summary')
                            ->viewData(function ($record) {
                                $start = now()->startOfMonth();
                                $rows  = TeacherAttendance::where('teacher_id', $record->id)
                                    ->where('date', '>=', $start)->get();
                                $p = $rows->where('status','present')->count();
                                $l = $rows->where('status','late')->count();
                                $a = $rows->where('status','absent')->count();
                                $v = $rows->where('status','leave')->count();
                                $base = $p + $l + $a;
                                return ['summary' => [
                                    'present' => $p, 'late' => $l, 'absent' => $a, 'leave' => $v,
                                    'rate'    => $base ? round(($p / $base) * 100) : 0,
                                ]];
                            }),
                    ]),

                    Section::make('Last 30 Days')->schema([
                        ViewEntry::make('att_cal')
                            ->view('filament.principal.teacher.attendance-calendar')
                            ->viewData(function ($record) {
                                $rows = TeacherAttendance::where('teacher_id', $record->id)
                                    ->where('date', '>=', now()->subDays(30))
                                    ->orderBy('date')->get(['date','status']);
                                return ['days' => $rows->map(fn ($r) => ['date' => $r->date, 'status' => $r->status])->all()];
                            }),
                    ]),

                    Section::make('Log (last 20)')->collapsible()->schema([
                        ViewEntry::make('att_log')
                            ->view('filament.principal.teacher.attendance-log')
                            ->viewData(fn ($record) => [
                                'logs' => TeacherAttendance::where('teacher_id', $record->id)
                                    ->orderByDesc('date')->limit(20)->get(),
                            ]),
                    ]),
                ]),

                Tabs\Tab::make('Career & Contract')->icon('heroicon-o-briefcase')->schema([
                    Section::make('Current Contract')->columns(3)->schema([
                        TextEntry::make('contract')->placeholder('—'),
                        TextEntry::make('joined_at')->date('d M Y')->placeholder('—'),
                        TextEntry::make('contract_end')->date('d M Y')->placeholder('—'),
                        TextEntry::make('status')->badge()
                            ->formatStateUsing(fn ($s) => Teacher::STATUSES[$s] ?? $s),
                        TextEntry::make('employment')->badge()->color('gray'),
                        TextEntry::make('dept')->label('Department')->placeholder('—'),
                    ]),

                    Section::make('Leave History')
                        ->description(fn ($record) => static::leaveSummary($record))
                        ->collapsible()
                        ->schema([
                            ViewEntry::make('leaves')
                                ->view('filament.principal.teacher.leave-history')
                                ->viewData(fn ($record) => [
                                    'leaves'  => TeacherLeave::where('teacher_id', $record->id)
                                        ->orderByDesc('starts_at')->limit(30)->get(),
                                    'summary' => static::leaveStats($record),
                                ]),
                        ]),

                    Section::make('Mentorship')->collapsible()->schema([
                        ViewEntry::make('mentorship')
                            ->view('filament.principal.teacher.mentorship')
                            ->viewData(fn ($record) => [
                                'mentor'  => $record->mentor,
                                'mentees' => $record->mentees()->orderBy('name')->get(),
                            ]),
                    ]),

                    Section::make('Employment History')->collapsible()->schema([
                        ViewEntry::make('employment_events')
                            ->view('filament.principal.teacher.employment-timeline')
                            ->viewData(fn ($record) => [
                                'events' => TeacherEmploymentEvent::where('teacher_id', $record->id)
                                    ->orderByDesc('event_date')->get(),
                            ]),
                    ]),
                ]),

                Tabs\Tab::make('Development')->icon('heroicon-o-academic-cap')->schema([
                    Section::make('🇮🇩 Formal Certifications')->collapsible()->schema([
                        ViewEntry::make('formal_certs')
                            ->view('filament.principal.teacher.certifications-list')
                            ->viewData(fn ($record) => [
                                'certifications' => $record->formalCertifications()->orderByDesc('issued_at')->get(),
                            ]),
                    ]),

                    Section::make('CPD Hours Tracker')->schema([
                        ViewEntry::make('cpd')
                            ->view('filament.principal.teacher.cpd-tracker')
                            ->viewData(function ($record) {
                                $ay    = (now()->month >= 7 ? now()->year.'/'.(now()->year+1) : (now()->year-1).'/'.now()->year);
                                $hours = $record->cpdHoursForYear($ay);
                                return ['hours' => $hours, 'target' => 40, 'academicYear' => $ay];
                            }),
                    ]),

                    Section::make('Quick credential tags')->collapsible()->schema([
                        ViewEntry::make('certifications')
                            ->view('filament.principal.teacher.chip-list')
                            ->viewData(fn ($record) => [
                                'items' => $record->certifications ?? [],
                                'empty' => 'No quick tags recorded.',
                                'class' => 'sp-teacher-chip--cert',
                            ]),
                    ]),
                    Section::make('Languages')->collapsible()->schema([
                        ViewEntry::make('languages')
                            ->view('filament.principal.teacher.chip-list')
                            ->viewData(fn ($record) => [
                                'items' => $record->languages ?? [],
                                'empty' => 'No languages recorded.',
                                'class' => 'sp-teacher-chip--lang',
                            ]),
                    ]),
                    Section::make('Training History')
                        ->description(fn ($record) => static::trainingSummary($record))
                        ->schema([
                            ViewEntry::make('trainings')
                                ->view('filament.principal.teacher.training-list')
                                ->viewData(fn ($record) => [
                                    'trainings' => TeacherTraining::where('teacher_id', $record->id)
                                        ->orderByDesc('starts_on')->get(),
                                ]),
                        ]),

                    Section::make('Observations (recent 5)')->collapsible()->schema([
                        ViewEntry::make('observations')
                            ->view('filament.principal.teacher.observations-list')
                            ->viewData(fn ($record) => [
                                'observations' => TeacherObservation::with('observer')
                                    ->where('teacher_id', $record->id)
                                    ->orderByDesc('observed_at')->limit(5)->get(),
                            ]),
                    ]),

                    Section::make('Goals')->collapsible()->schema([
                        ViewEntry::make('goals')
                            ->view('filament.principal.teacher.goals-list')
                            ->viewData(fn ($record) => [
                                'goals' => TeacherGoal::where('teacher_id', $record->id)
                                    ->orderBy('status')->orderBy('target_date')->get(),
                            ]),
                    ]),

                    Section::make('Self-Reflection Journal (last 3, private)')->collapsible()->collapsed()->schema([
                        ViewEntry::make('journal')
                            ->view('filament.principal.teacher.journal-list')
                            ->viewData(fn ($record) => [
                                'entries' => TeacherJournalEntry::where('teacher_id', $record->id)
                                    ->orderByDesc('entry_date')->limit(3)->get(),
                            ]),
                    ]),

                    Section::make('Student Feedback')->collapsible()->collapsed()->schema([
                        ViewEntry::make('survey')
                            ->view('filament.principal.teacher.survey-placeholder'),
                    ]),

                    Section::make('Voluntary Requests')
                        ->collapsible()
                        ->schema([
                            ViewEntry::make('voluntary')
                                ->view('filament.principal.teacher.voluntary-list')
                                ->viewData(fn ($record) => [
                                    'items' => VoluntaryRequest::where('teacher_id', $record->id)
                                        ->orderByDesc('created_at')->limit(15)->get(),
                                ]),
                        ]),
                ]),

                Tabs\Tab::make('Documents')->icon('heroicon-o-document-text')
                    ->badge(fn ($record) => TeacherDocument::where('teacher_id', $record->id)
                        ->where('status', 'pending')->count() ?: null)
                    ->badgeColor('warning')
                    ->schema([
                        Section::make('Expiry Dashboard')->schema([
                            ViewEntry::make('expiry')
                                ->view('filament.principal.teacher.expiry-dashboard')
                                ->viewData(fn ($record) => ['buckets' => static::expiryBuckets($record)]),
                        ]),

                        Section::make('Clearances')->collapsible()->schema([
                            ViewEntry::make('clearances')
                                ->view('filament.principal.teacher.clearances-list')
                                ->viewData(fn ($record) => [
                                    'clearances' => $record->clearances()->orderBy('type')->get(),
                                ]),
                        ]),

                        Section::make('Uploaded Documents')->schema([
                            ViewEntry::make('docs')
                                ->view('filament.principal.teacher.documents-list')
                                ->viewData(fn ($record) => [
                                    'docs' => TeacherDocument::where('teacher_id', $record->id)
                                        ->orderByDesc('created_at')->get(),
                                ]),
                        ]),
                    ]),
            ]),
        ]);
    }

    protected static function expiryBuckets(Teacher $teacher): array
    {
        $today = now()->startOfDay();
        $items = collect();

        // Aggregate from documents + clearances + certifications
        $items = $items->merge(TeacherDocument::where('teacher_id', $teacher->id)->whereNotNull('expires_at')->pluck('expires_at'));
        $items = $items->merge($teacher->clearances()->whereNotNull('expires_at')->pluck('expires_at'));
        $items = $items->merge($teacher->formalCertifications()->whereNotNull('expires_at')->pluck('expires_at'));

        $b = ['expired' => 0, '30' => 0, '60' => 0, '90' => 0];
        foreach ($items as $d) {
            $days = $today->diffInDays($d, false);
            if ($days < 0)       $b['expired']++;
            elseif ($days <= 30) $b['30']++;
            elseif ($days <= 60) $b['60']++;
            elseif ($days <= 90) $b['90']++;
        }
        return $b;
    }

    protected static function leaveStats(Teacher $teacher): array
    {
        $all = TeacherLeave::where('teacher_id', $teacher->id)->get();
        return [
            'total'    => $all->count(),
            'pending'  => $all->where('status', 'pending')->count(),
            'approved' => $all->where('status', 'approved')->count(),
            'days'     => $all->where('status', 'approved')->sum(fn ($l) => max(1, $l->starts_at->diffInDays($l->ends_at) + 1)),
        ];
    }

    protected static function leaveSummary(Teacher $teacher): string
    {
        $s = static::leaveStats($teacher);
        return "{$s['total']} total · {$s['approved']} approved ({$s['days']} days) · {$s['pending']} pending";
    }

    protected static function trainingSummary(Teacher $teacher): string
    {
        $tr = TeacherTraining::where('teacher_id', $teacher->id)->get();
        $hours = $tr->sum('hours');
        return $tr->count().' trainings · '.number_format($hours, 1).' hours';
    }
}
