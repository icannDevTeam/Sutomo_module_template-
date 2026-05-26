<?php

namespace App\Filament\Principal\Pages;

use App\Models\Candidate;
use App\Models\Teacher;
use App\Models\TeacherObservation;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;

class OplSessionDetail extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'opl-sessions/{candidate}';
    protected static string $view = 'filament.principal.pages.opl-session-detail';
    protected static ?string $title = 'OPL Session';

    public ?Candidate $candidate = null;

    public function mount(int|string $candidate): void
    {
        $this->candidate = Candidate::findOrFail($candidate);
    }

    protected function getObservations()
    {
        if (! $this->candidate) {
            return collect();
        }
        try {
            $teacher = null;
            if (Schema::hasColumn('candidates', 'teacher_id') && ! empty($this->candidate->teacher_id)) {
                $teacher = Teacher::find($this->candidate->teacher_id);
            }
            if (! $teacher) {
                $teacher = Teacher::where('name', $this->candidate->name)->first();
            }
            if (! $teacher) {
                return collect();
            }
            return TeacherObservation::where('teacher_id', $teacher->id)
                ->with('observer')
                ->orderByDesc('observed_at')
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    public function getViewData(): array
    {
        $c = $this->candidate;
        $observations = $this->getObservations();

        $createdAt = $c?->created_at;
        $daysSinceStart = $createdAt ? (int) abs($createdAt->diffInDays(now())) : 0;

        $progress = [
            ['key' => 'orientation',  'label' => 'Orientation',          'done' => true],
            ['key' => 'mentor_pair',  'label' => 'Mentor pairing',       'done' => true],
            ['key' => 'first_obs',    'label' => 'First observation',    'done' => $observations->count() >= 1],
            ['key' => 'week_2',       'label' => 'Week-2 review',        'done' => $daysSinceStart >= 14],
            ['key' => 'month_1',      'label' => 'Month-1 review',       'done' => $daysSinceStart >= 30],
        ];

        $mentorNotes = [];
        try {
            $metaNotes = data_get($c?->meta, 'opl.mentor_notes', []);
            if (is_array($metaNotes) && ! empty($metaNotes)) {
                $mentorNotes = $metaNotes;
            }
        } catch (\Throwable $e) {
            $mentorNotes = [];
        }

        return [
            'candidate'    => $c,
            'progress'     => $progress,
            'observations' => $observations,
            'mentor_notes' => $mentorNotes,
        ];
    }

    public function promoteToProbation(): void
    {
        if (! $this->candidate) {
            return;
        }
        // Role gate: only principal/vice_principal/admin may promote.
        $role = auth()->user()->role ?? null;
        abort_unless(in_array($role, ['principal', 'vice_principal', 'admin', 'superadmin'], true), 403, 'Insufficient role to promote candidate.');
        // State guard: only OPL-stage candidates may be promoted.
        $stage = $this->candidate->stage ?? null;
        abort_unless(in_array($stage, ['opl', 'onboarding'], true), 409, 'Candidate is not in OPL stage.');

        try {
            \DB::transaction(function () {
                if (Schema::hasColumn('candidates', 'status')) {
                    $this->candidate->forceFill(['status' => 'probation'])->save();
                } elseif (Schema::hasColumn('candidates', 'stage')) {
                    $this->candidate->forceFill(['stage' => 'active'])->save();
                }
                if (class_exists(\App\Models\AuditLog::class)) {
                    try {
                        \App\Models\AuditLog::create([
                            'occurred_at' => now(),
                            'user_name'   => optional(auth()->user())->name ?? 'system',
                            'role'        => $role ?? 'principal',
                            'action'      => 'candidate.promote_to_probation',
                            'target'      => 'Candidate:' . $this->candidate->id,
                            'from_value'  => $stage,
                            'to_value'    => 'probation',
                        ]);
                    } catch (\Throwable $e) {
                        // audit failure must not block
                    }
                }
            });
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Could not promote candidate')
                ->body($e->getMessage())
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title('Candidate promoted to Probation')
            ->success()
            ->send();

        $this->redirect(static::getUrl(['candidate' => $this->candidate->id]));
    }
}
