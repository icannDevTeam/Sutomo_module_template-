<?php

namespace App\Filament\Principal\Pages;

use App\Models\TimetableDefinition;
use App\Models\TimetableLesson;
use App\Services\Timetable\AutoGenerator;
use App\Services\Timetable\ConflictService;
use App\Services\Timetable\PublishService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TimetableBuilder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Planning';
    protected static ?string $title = 'Timetable Planning';
    protected static ?string $navigationLabel = 'Timetable';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.principal.pages.timetable-builder';

    public string $selectedTeacherId = 'T-001';

    /** Active view: teacher | class | room | master */
    public string $viewMode = 'teacher';
    public ?string $selectedClassCode = null;
    public ?string $selectedRoom = null;

    /** "{session}-{period}-{day}" while a cell editor is open. */
    public ?string $activeCell = null;
    public string $pickerSubject = '';
    public string $pickerClass = '';
    public bool $pickerLocked = false;

    /** Toggles a print-all-teachers stacked view (used right before window.print()). */
    public bool $printAll = false;

    public ?int $definitionId = null;

    public const DAYS = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];

    public const PERIODS_SMP = [
        'pagi' => [
            ['no' => 'I',   'time' => '07.30 – 08.10'],
            ['no' => 'II',  'time' => '08.10 – 08.50'],
            ['no' => 'III', 'time' => '08.50 – 09.30'],
            ['no' => 'IV',  'time' => '09.30 – 10.10'],
            ['no' => 'BR1', 'time' => '10.10 – 10.25', 'break' => 'ISTIRAHAT PAGI'],
            ['no' => 'V',   'time' => '10.25 – 11.05'],
            ['no' => 'VI',  'time' => '11.05 – 11.40'],
            ['no' => 'VII', 'time' => '11.40 – 12.15'],
        ],
        'sore' => [
            ['no' => 'I',   'time' => '12.50 – 13.30'],
            ['no' => 'II',  'time' => '13.30 – 14.05'],
            ['no' => 'III', 'time' => '14.05 – 14.40'],
            ['no' => 'BR1', 'time' => '14.40 – 14.50', 'break' => 'ISTIRAHAT SIANG 1'],
            ['no' => 'IV',  'time' => '14.50 – 15.25'],
            ['no' => 'V',   'time' => '15.25 – 16.00'],
            ['no' => 'BR2', 'time' => '16.00 – 16.10', 'break' => 'ISTIRAHAT SIANG 2'],
            ['no' => 'VI',  'time' => '16.10 – 16.45'],
            ['no' => 'VII', 'time' => '16.45 – 17.20'],
        ],
    ];

    public const PERIODS_SD = [
        'pagi' => [
            ['no' => 'I',   'time' => '07.15 – 07.45'],
            ['no' => 'II',  'time' => '07.45 – 08.15'],
            ['no' => 'III', 'time' => '08.15 – 08.45'],
            ['no' => 'BR1', 'time' => '08.45 – 09.00', 'break' => 'ISTIRAHAT PAGI'],
            ['no' => 'IV',  'time' => '09.00 – 09.30'],
            ['no' => 'V',   'time' => '09.30 – 10.00'],
            ['no' => 'VI',  'time' => '10.00 – 10.30'],
            ['no' => 'BR2', 'time' => '10.30 – 10.45', 'break' => 'ISTIRAHAT SIANG'],
            ['no' => 'VII', 'time' => '10.45 – 11.15'],
        ],
        'sore' => [],
    ];

    public function teachers(): array
    {
        return [
            'T-001' => [
                'name' => 'Hossiana E. Siahaan, S.Pd.', 'subject' => 'Biologi / Lab IPA',
                'unit' => 'SMP Swasta Sutomo 1', 'level' => 'smp', 'color' => '#0ea5e9',
                'classes' => ['P VII-01', 'P VII-02', 'P VII-03', 'P VII-04', 'P VIII-01', 'P VIII-02', 'P VIII-03', 'P VIII-04', 'P IX-01', 'P IX-02'],
            ],
            'T-002' => [
                'name' => 'Andini Putri, S.Pd.', 'subject' => 'Matematika',
                'unit' => 'SD Swasta Sutomo 1', 'level' => 'sd', 'color' => '#6366f1',
                'classes' => ['4-A', '4-B', '5-A', '5-B', '6-A'],
            ],
            'T-003' => [
                'name' => 'Bagus Pratama, S.S.', 'subject' => 'Bahasa Inggris',
                'unit' => 'SMP Swasta Sutomo 1', 'level' => 'smp', 'color' => '#f97316',
                'classes' => ['P VII-01', 'P VIII-01', 'P IX-01', 'P IX-02'],
            ],
        ];
    }

    public function collaborators(): array
    {
        return [
            ['name' => 'Hossiana Siahaan', 'initials' => 'HS', 'color' => '#0ea5e9', 'online' => true,  'where' => 'Editing PAGI'],
            ['name' => 'Andini Putri',     'initials' => 'AP', 'color' => '#6366f1', 'online' => true,  'where' => 'Viewing SD'],
            ['name' => 'Bagus Pratama',    'initials' => 'BP', 'color' => '#f97316', 'online' => false, 'where' => 'Seen 8m ago'],
            ['name' => 'Pak Dwi',          'initials' => 'PD', 'color' => '#f59e0b', 'online' => false, 'where' => 'Seen 1h ago'],
        ];
    }

    public function mount(): void
    {
        $def = TimetableDefinition::firstOrCreate(
            ['term' => 'TP 2025/2026', 'school_unit' => 'ALL'],
            ['status' => 'draft']
        );
        $this->definitionId = $def->id;

        // Deep-link support: /principal/timetable?teacher=T-001&session=pagi&period=II&day=SENIN
        $req = request();
        if ($req->filled('teacher') && isset($this->teachers()[$req->string('teacher')->toString()])) {
            $this->selectedTeacherId = $req->string('teacher')->toString();
        }
        if ($req->filled('view') && in_array($req->string('view')->toString(), ['teacher','class','room','master'], true)) {
            $this->viewMode = $req->string('view')->toString();
        }
        if ($req->filled('session') && $req->filled('period') && $req->filled('day')) {
            $this->activeCell = $req->string('session')->toString()
                . '-' . $req->string('period')->toString()
                . '-' . $req->string('day')->toString();
            $lesson = $this->findLesson($req->string('session')->toString(), $req->string('period')->toString(), $req->string('day')->toString());
            $this->pickerSubject = $lesson?->subject ?? '';
            $this->pickerClass   = $lesson?->class_code ?? '';
        }
    }

    public function selectTeacher(string $id): void
    {
        if (! isset($this->teachers()[$id])) return;
        $this->selectedTeacherId = $id;
        $this->viewMode = 'teacher';
        $this->activeCell = null;
    }

    public function setViewMode(string $mode): void
    {
        if (! in_array($mode, ['teacher', 'class', 'room', 'master'], true)) return;
        $this->viewMode = $mode;
        $this->activeCell = null;

        // Default selections on first switch.
        if ($mode === 'class' && ! $this->selectedClassCode) {
            $classes = $this->allClassCodes();
            $this->selectedClassCode = $classes[0] ?? null;
        }
        if ($mode === 'room' && ! $this->selectedRoom) {
            $rooms = $this->allRooms();
            $this->selectedRoom = $rooms[0] ?? null;
        }
    }

    public function selectClass(string $code): void
    {
        $this->selectedClassCode = $code;
    }

    public function selectRoom(string $room): void
    {
        $this->selectedRoom = $room;
    }

    /** Used by Class/Master view to jump to a teacher's editable sheet. */
    public function jumpToTeacher(string $id): void
    {
        $this->selectTeacher($id);
    }

    private function allClassCodes(): array
    {
        $fromTeachers = collect($this->teachers())->pluck('classes')->flatten()->unique();
        $fromDb       = TimetableLesson::where('definition_id', $this->definitionId)
            ->distinct()->pluck('class_code');
        return $fromTeachers->merge($fromDb)->unique()->filter()->sort()->values()->all();
    }

    private function allRooms(): array
    {
        return TimetableLesson::where('definition_id', $this->definitionId)
            ->whereNotNull('room')->distinct()->orderBy('room')->pluck('room')->all();
    }

    public function editCell(string $session, string $period, string $day): void
    {
        $lesson = $this->findLesson($session, $period, $day);
        $this->pickerSubject = $lesson?->subject ?? '';
        $this->pickerClass   = $lesson?->class_code ?? '';
        $this->pickerLocked  = (bool) ($lesson?->locked ?? false);
        $this->activeCell    = $session . '-' . $period . '-' . $day;
    }

    public function saveCell(ConflictService $conflicts): void
    {
        if (! $this->activeCell) return;
        [$session, $period, $day] = explode('-', $this->activeCell, 3);

        $subject = strtoupper(trim($this->pickerSubject));
        $class   = trim($this->pickerClass);
        $lesson  = $this->findLesson($session, $period, $day);

        if ($subject === '' && $class === '') {
            if ($lesson) {
                $copy = $lesson->replicate();
                $copy->id = $lesson->id;
                $copy->exists = true;
                $lesson->delete();
                $conflicts->rescanSlotNeighbors($copy);
            }
        } else {
            $lesson = $lesson ?? new TimetableLesson([
                'definition_id' => $this->definitionId,
                'teacher_ref'   => $this->selectedTeacherId,
                'session'       => $session,
                'period'        => $period,
                'day'           => $day,
            ]);
            $lesson->fill([
                'definition_id' => $this->definitionId,
                'teacher_ref'   => $this->selectedTeacherId,
                'subject'       => $subject,
                'class_code'    => $class,
                'session'       => $session,
                'period'        => $period,
                'day'           => $day,
                'locked'        => $this->pickerLocked,
            ]);
            $lesson->save();
            $conflicts->checkAndStore($lesson);
            $conflicts->rescanSlotNeighbors($lesson);
        }

        $this->resetPicker();
    }

    public function clearCell(ConflictService $conflicts): void
    {
        if (! $this->activeCell) return;
        [$session, $period, $day] = explode('-', $this->activeCell, 3);
        $lesson = $this->findLesson($session, $period, $day);
        if ($lesson) {
            $copy = $lesson->replicate();
            $copy->id = $lesson->id;
            $copy->exists = true;
            $lesson->delete();
            $conflicts->rescanSlotNeighbors($copy);
        }
        $this->resetPicker();
    }

    public function cancelCell(): void
    {
        $this->resetPicker();
    }

    /** Run the constraint solver, replace all unlocked lessons. */
    public function generateDraft(AutoGenerator $generator): void
    {
        $result = $generator->generate($this->definitionId, $this->teachers(), self::PERIODS_SMP);

        $msg = "Placed {$result['placed']} lessons · kept {$result['kept']} locked";
        if (! empty($result['unsolved'])) {
            $unsolvedLine = collect($result['unsolved'])
                ->take(5)
                ->map(fn ($u) => "{$u['class']} ({$u['subject']}): {$u['remaining']}")
                ->implode(', ');
            $extra = count($result['unsolved']) > 5 ? ' …' : '';
            Notification::make()
                ->title('Draft generated with gaps')
                ->body("{$msg}. Unsolved: {$unsolvedLine}{$extra}")
                ->warning()->persistent()->send();
        } else {
            Notification::make()
                ->title('Draft generated cleanly')
                ->body($msg)
                ->success()->send();
        }
    }

    /** Wipe every unlocked lesson without regenerating. */
    public function resetDraft(): void
    {
        $count = TimetableLesson::where('definition_id', $this->definitionId)
            ->where('locked', false)->count();
        TimetableLesson::where('definition_id', $this->definitionId)
            ->where('locked', false)->delete();
        Notification::make()
            ->title('Draft cleared')
            ->body("Removed {$count} unlocked lessons. Locked cells preserved.")
            ->success()->send();
    }

    /** Freeze current state as an immutable snapshot + mark definition published. */
    public function publishSnapshot(PublishService $svc): void
    {
        $conflictTotal = TimetableLesson::where('definition_id', $this->definitionId)
            ->whereNotNull('conflicts')
            ->where('conflicts', '!=', '[]')
            ->count();

        $snap = $svc->publish(
            $this->definitionId,
            null,
            auth()->user()?->name ?? 'System',
        );

        $note = Notification::make()
            ->title("Snapshot #{$snap->id} saved")
            ->body("{$snap->lesson_count} lessons frozen · {$snap->conflict_count} unresolved conflict(s)");

        $snap->conflict_count > 0 ? $note->warning()->persistent()->send() : $note->success()->send();
    }

    /** Flip flag so view stacks every teacher sheet; the blade fires window.print() after render. */
    public function exportAllTeachers(): void
    {
        $this->printAll = true;
        $this->dispatch('timetable-print-ready');
    }

    #[\Livewire\Attributes\On('reset-print-all')]
    public function resetPrintAll(): void
    {
        $this->printAll = false;
    }

    private function resetPicker(): void
    {
        $this->activeCell = null;
        $this->pickerSubject = '';
        $this->pickerClass = '';
        $this->pickerLocked = false;
    }

    private function findLesson(string $session, string $period, string $day): ?TimetableLesson
    {
        return TimetableLesson::query()
            ->where('definition_id', $this->definitionId)
            ->where('teacher_ref', $this->selectedTeacherId)
            ->where('session', $session)
            ->where('period', $period)
            ->where('day', $day)
            ->first();
    }

    public function getViewData(): array
    {
        $teachers = $this->teachers();
        $current  = $teachers[$this->selectedTeacherId] ?? array_values($teachers)[0];
        $periods  = $current['level'] === 'sd' ? self::PERIODS_SD : self::PERIODS_SMP;
        $sessions = ['pagi' => 'PAGI', 'sore' => 'SORE'];

        // ---- Teacher view (default + edit surface) ----
        $teacherLessons = TimetableLesson::query()
            ->where('definition_id', $this->definitionId)
            ->where('teacher_ref', $this->selectedTeacherId)
            ->get();

        $grid = ['pagi' => [], 'sore' => []];
        $conflictCount = 0;
        $lockedCount = 0;
        foreach ($teacherLessons as $l) {
            $grid[$l->session][$l->period . '-' . $l->day] = [
                'subject'   => $l->subject,
                'class'     => $l->class_code,
                'locked'    => (bool) $l->locked,
                'conflicts' => $l->conflicts ?? [],
            ];
            if (! empty($l->conflicts)) $conflictCount++;
            if ($l->locked) $lockedCount++;
        }

        $counts = [];
        foreach ($grid as $cells) {
            foreach ($cells as $cell) {
                $cls = $cell['class'] ?? null;
                if (! $cls) continue;
                $counts[$cls] = ($counts[$cls] ?? 0) + 1;
            }
        }
        ksort($counts);

        // ---- Class view ----
        $classGrid = ['pagi' => [], 'sore' => []];
        $classPeriods = $periods;
        if ($this->viewMode === 'class' && $this->selectedClassCode) {
            // SD vs SMP classes: detect by code prefix ("P " => SMP, digit => SD)
            $isSmp = str_starts_with($this->selectedClassCode, 'P ');
            $classPeriods = $isSmp ? self::PERIODS_SMP : self::PERIODS_SD;
            $classLessons = TimetableLesson::query()
                ->where('definition_id', $this->definitionId)
                ->where('class_code', $this->selectedClassCode)
                ->get();
            foreach ($classLessons as $l) {
                $classGrid[$l->session][$l->period . '-' . $l->day] = [
                    'subject'     => $l->subject,
                    'teacher_ref' => $l->teacher_ref,
                    'teacher'     => $teachers[$l->teacher_ref]['name'] ?? $l->teacher_ref,
                    'color'       => $teachers[$l->teacher_ref]['color'] ?? '#64748b',
                    'conflicts'   => $l->conflicts ?? [],
                ];
            }
        }

        // ---- Room view ----
        $roomGrid = ['pagi' => [], 'sore' => []];
        if ($this->viewMode === 'room' && $this->selectedRoom) {
            $roomLessons = TimetableLesson::query()
                ->where('definition_id', $this->definitionId)
                ->where('room', $this->selectedRoom)
                ->get();
            foreach ($roomLessons as $l) {
                $roomGrid[$l->session][$l->period . '-' . $l->day] = [
                    'subject'     => $l->subject,
                    'class'       => $l->class_code,
                    'teacher_ref' => $l->teacher_ref,
                    'teacher'     => $teachers[$l->teacher_ref]['name'] ?? $l->teacher_ref,
                    'color'       => $teachers[$l->teacher_ref]['color'] ?? '#64748b',
                    'conflicts'   => $l->conflicts ?? [],
                ];
            }
        }

        // ---- Master view: per-teacher card with mini heatmap ----
        $masterCards = [];
        if ($this->viewMode === 'master') {
            $allRows = TimetableLesson::query()
                ->where('definition_id', $this->definitionId)
                ->get()
                ->groupBy('teacher_ref');

            foreach ($teachers as $tid => $t) {
                $rows = $allRows->get($tid, collect());
                $byDay = []; $conf = 0;
                foreach (self::DAYS as $d) $byDay[$d] = 0;
                foreach ($rows as $r) {
                    $byDay[$r->day] = ($byDay[$r->day] ?? 0) + 1;
                    if (! empty($r->conflicts)) $conf++;
                }
                // load cap (lookup)
                $cap = \App\Models\TeacherLoad::query()
                    ->where('definition_id', $this->definitionId)
                    ->where('teacher_ref', $tid)
                    ->value('weekly_cap');
                $masterCards[] = [
                    'id'        => $tid,
                    'name'      => $t['name'],
                    'subject'   => $t['subject'],
                    'unit'      => $t['unit'],
                    'color'     => $t['color'],
                    'total'     => $rows->count(),
                    'cap'       => $cap,
                    'conflicts' => $conf,
                    'byDay'     => $byDay,
                ];
            }
        }

        // ---- Print-all view: each teacher's sheet pre-built for stacking ----
        $printSheets = [];
        if ($this->printAll) {
            $allRows = TimetableLesson::query()
                ->where('definition_id', $this->definitionId)
                ->get()
                ->groupBy('teacher_ref');
            foreach ($teachers as $tid => $t) {
                $rows = $allRows->get($tid, collect());
                $g = ['pagi' => [], 'sore' => []];
                $cc = []; $cf = 0;
                foreach ($rows as $l) {
                    $g[$l->session][$l->period . '-' . $l->day] = [
                        'subject' => $l->subject,
                        'class'   => $l->class_code,
                        'locked'  => (bool) $l->locked,
                    ];
                    $cc[$l->class_code] = ($cc[$l->class_code] ?? 0) + 1;
                    if (! empty($l->conflicts)) $cf++;
                }
                ksort($cc);
                $printSheets[] = [
                    'teacher' => $t + ['id' => $tid],
                    'periods' => $t['level'] === 'sd' ? self::PERIODS_SD : self::PERIODS_SMP,
                    'grid'    => $g,
                    'counts'  => $cc,
                    'conflicts' => $cf,
                ];
            }
        }

        return [
            'days'              => self::DAYS,
            'sessions'          => $sessions,
            'periods'           => $periods,
            'teachers'          => $teachers,
            'current'           => $current,
            'currentGrid'       => $grid,
            'collaborators'     => collect($this->collaborators()),
            'classCounts'       => $counts,
            'conflictCount'     => $conflictCount,
            'lockedCount'       => $lockedCount,
            // Phase 3
            'viewMode'          => $this->viewMode,
            'allClassCodes'     => $this->allClassCodes(),
            'allRooms'          => $this->allRooms(),
            'selectedClassCode' => $this->selectedClassCode,
            'selectedRoom'      => $this->selectedRoom,
            'classGrid'         => $classGrid,
            'classPeriods'      => $classPeriods,
            'roomGrid'          => $roomGrid,
            'masterCards'       => $masterCards,
            // Phase 6
            'printAll'          => $this->printAll,
            'printSheets'       => $printSheets,
        ];
    }
}
