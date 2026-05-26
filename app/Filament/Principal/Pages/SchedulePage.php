<?php

namespace App\Filament\Principal\Pages;

use App\Models\Teacher;
use App\Models\TimetableLesson;
use App\Services\Timetable\TeacherSchedule;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class SchedulePage extends Page
{
    protected static ?string $navigationGroup = 'Academics';
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $title = 'Schedule & Timetable';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.principal.pages.schedule';

    /** 'classes' | 'teachers' | 'rooms' */
    public string $activeView = 'classes';

    public ?string $selectedClass = null;
    public ?string $selectedTeacherCode = null;
    public ?string $selectedRoom = null;

    public function setView(string $v): void
    {
        if (! in_array($v, ['classes', 'teachers', 'rooms'], true)) {
            return;
        }
        $this->activeView = $v;
        $this->selectedClass = null;
        $this->selectedTeacherCode = null;
        $this->selectedRoom = null;
    }

    public function selectClass(?string $code): void
    {
        $this->selectedClass = $this->selectedClass === $code ? null : $code;
    }

    public function selectTeacher(?string $code): void
    {
        $this->selectedTeacherCode = $this->selectedTeacherCode === $code ? null : $code;
    }

    public function selectRoom(?string $room): void
    {
        $this->selectedRoom = $this->selectedRoom === $room ? null : $room;
    }

    protected function activeDefinitionId(): ?int
    {
        return TeacherSchedule::activeDefinitionId();
    }

    /** Build a [day][period] map from a collection of lessons. */
    protected function gridify(Collection $lessons): array
    {
        $grid = [];
        foreach (TeacherSchedule::DAYS as $d) {
            foreach (TeacherSchedule::PERIODS as $p) {
                $grid[$d][$p] = null;
            }
        }
        foreach ($lessons as $l) {
            if (isset($grid[$l->day]) && array_key_exists($l->period, $grid[$l->day])) {
                $grid[$l->day][$l->period] = $l;
            }
        }
        return $grid;
    }

    protected function getViewData(): array
    {
        $defId = $this->activeDefinitionId();

        $lessonsQuery = TimetableLesson::query();
        if ($defId) {
            $lessonsQuery->where('definition_id', $defId);
        }
        $allLessons = $defId ? $lessonsQuery->get() : collect();

        // Resolve teachers by code first; for synthetic refs (T-001 etc.)
        // that don't match real teacher codes, fall back to a deterministic
        // subject-based mapping so the UI shows real names.
        $teacherCodes = $allLessons->pluck('teacher_ref')->filter()->unique()->values();
        $directMatches = Teacher::query()
            ->whereIn('code', $teacherCodes)
            ->get()
            ->keyBy('code');

        $teachersByRef = $this->resolveTeacherRefs($allLessons, $directMatches);

        // Unit head index by subject (first teacher flagged is_unit_head per subject).
        $unitHeads = Teacher::query()
            ->where('is_unit_head', true)
            ->get()
            ->keyBy('unit_head_subject');

        // -------- Classes --------
        $classes = collect();
        $classGrouped = $allLessons->groupBy('class_code');
        foreach ($classGrouped as $code => $group) {
            $teacherRefs = $group->pluck('teacher_ref')->filter()->unique()->values();
            $subjects = $group->pluck('subject')->filter()->unique()->values();
            $avatars = $teacherRefs
                ->map(fn ($ref) => $teachersByRef->get($ref))
                ->filter()
                ->take(5)
                ->map(fn (Teacher $t) => [
                    'code'    => $t->code,
                    'name'    => $t->name,
                    'initials' => $this->initials($t->name),
                    'avatar'  => $t->avatar_url,
                ])->values();

            $headSubject = $subjects->first();
            $head = $headSubject ? $unitHeads->get($this->normalizeSubject($headSubject)) : null;
            if (! $head) {
                // Fallback: first resolved teacher in this class.
                $head = $teacherRefs
                    ->map(fn ($ref) => $teachersByRef->get($ref))
                    ->filter()
                    ->first();
            }

            $classes->push((object) [
                'code'           => $code,
                'subjects'       => $subjects->all(),
                'avatars'        => $avatars,
                'weekly_hours'   => $group->count(),
                'student_count'  => 0,
                'unit_head'      => $head ? (object) [
                    'name'    => $head->name,
                    'subject' => $head->unit_head_subject ?? $head->subject,
                ] : null,
            ]);
        }
        $classes = $classes->sortBy('code')->values();

        // -------- Lessons for selected class --------
        $selectedClassLessons = [];
        if ($this->selectedClass) {
            $selectedClassLessons = $this->gridify(
                $allLessons->where('class_code', $this->selectedClass)->values()
            );
        }

        // -------- Teachers view --------
        $teachers = collect();
        $selectedTeacherLessons = [];
        if ($this->activeView === 'teachers') {
            $teacherGrouped = $allLessons->groupBy('teacher_ref');
            foreach ($teacherGrouped as $ref => $group) {
                $t = $teachersByRef->get($ref);
                $classCount = $group->pluck('class_code')->unique()->count();
                $subjects = $group->pluck('subject')->filter()->unique()->values();
                $teachers->push((object) [
                    'code'         => $ref,
                    'name'         => $t?->name ?? $ref,
                    'subject'      => $t?->subject ?? $subjects->first(),
                    'subjects'     => $subjects->all(),
                    'avatar'       => $t?->avatar_url,
                    'initials'     => $this->initials($t?->name ?? $ref),
                    'lesson_count' => $group->count(),
                    'class_count'  => $classCount,
                    'is_unit_head' => (bool) ($t?->is_unit_head ?? false),
                    'unit_head_subject' => $t?->unit_head_subject,
                ]);
            }
            $teachers = $teachers->sortBy('name')->values();

            if ($this->selectedTeacherCode) {
                $selectedTeacherLessons = $this->gridify(
                    $allLessons->where('teacher_ref', $this->selectedTeacherCode)->values()
                );
            }
        }

        // -------- Rooms view --------
        $rooms = collect();
        $selectedRoomLessons = [];
        if ($this->activeView === 'rooms') {
            $roomGrouped = $allLessons
                ->filter(fn ($l) => filled($l->room))
                ->groupBy('room');
            foreach ($roomGrouped as $room => $group) {
                $rooms->push((object) [
                    'room'         => $room,
                    'lesson_count' => $group->count(),
                    'class_count'  => $group->pluck('class_code')->unique()->count(),
                ]);
            }
            $rooms = $rooms->sortBy('room')->values();

            if ($this->selectedRoom) {
                $selectedRoomLessons = $this->gridify(
                    $allLessons->where('room', $this->selectedRoom)->values()
                );
            }
        }

        $periods = collect(TeacherSchedule::PERIODS)
            ->map(fn ($p) => ['no' => $p, 'time' => null])
            ->all();

        return [
            'view'                    => $this->activeView,
            'classes'                 => $classes,
            'lessons'                 => $selectedClassLessons,
            'selectedClass'           => $this->selectedClass,
            'teachers'                => $teachers,
            'selectedTeacherCode'     => $this->selectedTeacherCode,
            'selectedTeacher'         => $this->selectedTeacherCode
                ? ($teachers->firstWhere('code', $this->selectedTeacherCode) ?? null)
                : null,
            'selectedTeacherLessons'  => $selectedTeacherLessons,
            'rooms'                   => $rooms,
            'selectedRoom'            => $this->selectedRoom,
            'selectedRoomLessons'     => $selectedRoomLessons,
            'days'                    => TeacherSchedule::DAYS,
            'periods'                 => $periods,
        ];
    }

    /**
     * Build a [ref => Teacher] map. Real codes resolve directly; synthetic
     * refs (T-001) deterministically map to a real teacher whose subject
     * matches the lessons assigned to that ref.
     */
    protected function resolveTeacherRefs(Collection $lessons, Collection $direct): Collection
    {
        $resolved = collect();
        $used = collect();

        // Group lessons by ref to find each ref's dominant subject.
        $byRef = $lessons->groupBy('teacher_ref');

        // Pool of all teachers, indexed for stable assignment.
        $allTeachers = Teacher::query()->orderBy('id')->get();

        foreach ($byRef as $ref => $group) {
            // 1) Real code match.
            if ($direct->has($ref)) {
                $resolved->put($ref, $direct->get($ref));
                $used->push($direct->get($ref)->id);
                continue;
            }

            // 2) Find teacher by dominant subject of this ref.
            $rawSubject = optional($group->first())->subject;
            $normalized = $this->normalizeSubject($rawSubject);
            $candidate = $allTeachers
                ->reject(fn (Teacher $t) => $used->contains($t->id))
                ->first(fn (Teacher $t) => $normalized
                    && stripos((string) $t->subject, $normalized) !== false);

            // 3) Fallback: deterministic by hash.
            if (! $candidate && $allTeachers->isNotEmpty()) {
                $idx = abs(crc32($ref)) % $allTeachers->count();
                $candidate = $allTeachers[$idx];
            }

            if ($candidate) {
                $resolved->put($ref, $candidate);
                $used->push($candidate->id);
            }
        }

        return $resolved;
    }

    /** Translate seeder subject labels (often Indonesian uppercase) to English keys. */
    protected function normalizeSubject(?string $subject): ?string
    {
        if (! $subject) {
            return null;
        }
        $key = strtoupper(trim($subject));
        $map = [
            'MATEMATIKA'    => 'Mathematics',
            'BIOLOGI'       => 'Biology',
            'FISIKA'        => 'Physics',
            'KIMIA'         => 'Chemistry',
            'BAHASA INGGRIS' => 'English',
            'INGGRIS'       => 'English',
            'BAHASA INDONESIA' => 'Indonesian',
            'INDONESIA'     => 'Indonesian',
            'EKONOMI'       => 'Economics',
            'SEJARAH'       => 'History',
            'GEOGRAFI'      => 'Geography',
            'SOSIOLOGI'     => 'Sociology',
            'PPKN'          => 'Civics',
            'AGAMA'         => 'Religion',
            'OLAHRAGA'      => 'PE',
            'PJOK'          => 'PE',
            'SENI'          => 'Arts',
            'MANDARIN'      => 'Mandarin',
        ];
        return $map[$key] ?? $subject;
    }

    protected function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $second = mb_substr($parts[1] ?? '', 0, 1);
        return strtoupper($first . $second) ?: '?';
    }
}
