<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\DutyAssignment;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use App\Models\TeacherLeave;
use App\Models\TeacherTraining;
use App\Models\VoluntaryRequest;
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
            Section::make()
                ->schema([
                    TextEntry::make('name')->size('lg')->weight('bold')->columnSpan(2),
                    TextEntry::make('code')->label('Code'),
                    TextEntry::make('subject')->placeholder('—'),
                    TextEntry::make('campus')->formatStateUsing(fn ($s) => strtoupper((string) $s))->placeholder('—'),
                    TextEntry::make('status')->badge()
                        ->formatStateUsing(fn ($s) => Teacher::STATUSES[$s] ?? $s),
                ])->columns(5),

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

                    Section::make('Contract Snapshot')->columns(3)->schema([
                        TextEntry::make('contract')->placeholder('—'),
                        TextEntry::make('joined_at')->date('d M Y')->placeholder('—'),
                        TextEntry::make('contract_end')->date('d M Y')->placeholder('—'),
                        TextEntry::make('tenure')->placeholder('—'),
                        TextEntry::make('employment')->badge()->color('gray'),
                        TextEntry::make('rating')->numeric(1)->suffix(' /5')->placeholder('—'),
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

                    Section::make('Reviews')->columns(2)->schema([
                        TextEntry::make('rating')->numeric(1)->suffix(' /5')->placeholder('—'),
                        TextEntry::make('last_review')->date('d M Y')->placeholder('—'),
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
                ]),

                Tabs\Tab::make('Development')->icon('heroicon-o-academic-cap')->schema([
                    Section::make('Certifications')->collapsible()->schema([
                        ViewEntry::make('certifications')
                            ->view('filament.principal.teacher.chip-list')
                            ->viewData(fn ($record) => [
                                'items' => $record->certifications ?? [],
                                'empty' => 'No certifications recorded.',
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

    protected static function leaveStats($record): array
    {
        $base = TeacherLeave::where('teacher_id', $record->id);
        return [
            'total'    => (clone $base)->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'pending'  => (clone $base)->where('status', 'pending')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
            'days_used_year' => (clone $base)
                ->where('status', 'approved')
                ->whereYear('starts_at', now()->year)
                ->get()
                ->sum(fn ($l) => max(1, $l->starts_at->diffInDays($l->ends_at) + 1)),
        ];
    }

    protected static function leaveSummary($record): string
    {
        $s = static::leaveStats($record);
        return "{$s['days_used_year']} days used in " . now()->year
            . " · {$s['pending']} pending · {$s['approved']} approved · {$s['rejected']} rejected";
    }

    protected static function trainingSummary($record): string
    {
        $rows = TeacherTraining::where('teacher_id', $record->id);
        $count = (clone $rows)->count();
        $hours = (clone $rows)->where('status', 'completed')->sum('hours');
        $yearHours = (clone $rows)
            ->where('status', 'completed')
            ->whereYear('ends_on', now()->year)
            ->sum('hours');
        return "{$count} sessions · {$hours} PD hours total · {$yearHours} hours in " . now()->year;
    }
}
