<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\EnrollmentPeriod;
use App\Models\PlacementExamSession;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class PlacementExamDemoSeeder extends Seeder
{
    private const MARKER = '__demo__ placement_exam';

    public function run(): void
    {
        $period = EnrollmentPeriod::query()
            ->where('campus', 'sma')
            ->where('status', 'open')
            ->orderByDesc('opens_at')
            ->first() ?? EnrollmentPeriod::query()->orderByDesc('opens_at')->first();

        if (! $period) {
            $this->command?->warn('PlacementExamDemoSeeder: no enrollment period found.');
            return;
        }

        $examStart = now()->addDays(2)->setTime(8, 0);
        $period->update([
            'exam_starts_at' => $examStart,
            'exam_venue' => $period->exam_venue ?: 'Main Hall Lt. 2',
            'exam_instructions' => $period->exam_instructions ?: 'Bring exam card, writing tools, and arrive 30 minutes before start.',
            'pass_threshold' => $period->pass_threshold ?? 70,
            'fail_threshold' => $period->fail_threshold ?? 50,
        ]);

        $teachers = Teacher::query()->orderBy('id')->take(8)->get();

        PlacementExamSession::query()
            ->where('enrollment_period_id', $period->id)
            ->where('notes', 'like', self::MARKER . '%')
            ->delete();

        $sessionDefs = [
            ['Mathematics', 'SMA', $examStart->copy(), 90, 'R-201'],
            ['Bahasa Indonesia', 'SMA', $examStart->copy()->addMinutes(110), 80, 'R-202'],
            ['English', 'SMA', $examStart->copy()->addDay(), 90, 'R-301'],
            ['Science Reasoning', 'SMA', $examStart->copy()->addDay()->addMinutes(110), 90, 'R-302'],
            ['Psychology Screening', 'All', $examStart->copy()->addDays(2), 60, 'Counseling Room'],
            ['Interview Slot', 'All', $examStart->copy()->addDays(2)->addMinutes(80), 30, 'Meeting Room A'],
        ];

        foreach ($sessionDefs as $i => [$subject, $gradeBand, $startsAt, $duration, $room]) {
            PlacementExamSession::create([
                'enrollment_period_id' => $period->id,
                'subject' => $subject,
                'grade_band' => $gradeBand,
                'starts_at' => $startsAt,
                'duration_minutes' => $duration,
                'room' => $room,
                'capacity' => 30,
                'supervisor_teacher_id' => $teachers->get($i % max(1, $teachers->count()))?->id,
                'notes' => self::MARKER . ' / scenario=' . str_replace(' ', '_', strtolower($subject)),
            ]);
        }

        $apps = Application::query()
            ->where('enrollment_period_id', $period->id)
            ->orderBy('id')
            ->take(24)
            ->get();

        $pass = (float) ($period->pass_threshold ?? 70);
        $fail = (float) ($period->fail_threshold ?? 50);

        $idx = 0;
        foreach ($apps as $app) {
            if ($idx < 6) {
                $app->update([
                    'status' => 'payment_confirmed',
                    'placement_score' => null,
                    'exam_date' => null,
                ]);
            } elseif ($idx < 14) {
                $app->update([
                    'status' => 'exam_scheduled',
                    'exam_date' => $examStart->copy()->addDays($idx % 3)->toDateString(),
                    'placement_score' => null,
                ]);
            } elseif ($idx < 18) {
                $score = min(100, $pass + 8 + ($idx - 14));
                $app->update([
                    'status' => 'exam_scheduled',
                    'exam_date' => $examStart->copy()->subDay()->toDateString(),
                ]);
                $app->applyExamScore($score);
            } elseif ($idx < 21) {
                $score = max($fail, $pass - 7 + ($idx - 18));
                $app->update([
                    'status' => 'exam_scheduled',
                    'exam_date' => $examStart->copy()->subDay()->toDateString(),
                ]);
                $app->applyExamScore($score);
            } else {
                $score = max(0, $fail - 8 - ($idx - 21));
                $app->update([
                    'status' => 'exam_scheduled',
                    'exam_date' => $examStart->copy()->subDay()->toDateString(),
                ]);
                $app->applyExamScore($score);
            }

            $idx++;
        }

        $this->command?->info('PlacementExamDemoSeeder: sessions + score scenarios seeded for period #' . $period->id);
    }
}
