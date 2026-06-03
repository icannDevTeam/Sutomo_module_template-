<?php

namespace App\Console\Commands;

use App\Mail\LoiReminderMail;
use App\Models\LetterOfIntent;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendLoiReminders extends Command
{
    protected $signature = 'loi:send-reminders {--dry-run}';

    protected $description = 'Send Letter of Intent reminders (in-app + email) for pending, overdue, and follow-up cases.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $now = Carbon::now();

        $counts = ['pending' => 0, 'overdue' => 0, 'follow_up' => 0];

        // 1) Pending signature reminders
        $pending = LetterOfIntent::query()
            ->where('status', 'sent')
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '>=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('last_reminder_at')
                  ->orWhere('last_reminder_at', '<', $now->copy()->subDays(3));
            })
            ->get();

        foreach ($pending as $letter) {
            if ($this->dispatchKind($letter, 'pending', $dry)) {
                $counts['pending']++;
            }
        }

        // 2) Overdue reminders
        $overdue = LetterOfIntent::query()
            ->where('status', 'sent')
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('last_reminder_at')
                  ->orWhere('last_reminder_at', '<', $now->copy()->subDay());
            })
            ->get();

        foreach ($overdue as $letter) {
            if ($this->dispatchKind($letter, 'overdue', $dry)) {
                $counts['overdue']++;
            }
        }

        // 3) Follow-up pending reminders
        $followUp = LetterOfIntent::query()
            ->where('status', 'declined')
            ->where(function ($q) {
                $q->whereNull('follow_up_status')
                  ->orWhereIn('follow_up_status', ['meeting_logged', 'resignation_pending']);
            })
            ->where('updated_at', '<', $now->copy()->subDays(5))
            ->where(function ($q) use ($now) {
                $q->whereNull('last_reminder_at')
                  ->orWhere('last_reminder_at', '<', $now->copy()->subDays(5));
            })
            ->get();

        foreach ($followUp as $letter) {
            if ($this->dispatchKind($letter, 'follow_up_pending', $dry)) {
                $counts['follow_up']++;
            }
        }

        $this->info(sprintf(
            'LOI reminders summary — pending: %d, overdue: %d, follow_up: %d%s',
            $counts['pending'],
            $counts['overdue'],
            $counts['follow_up'],
            $dry ? ' (dry-run)' : ''
        ));

        return self::SUCCESS;
    }

    protected function dispatchKind(LetterOfIntent $letter, string $kind, bool $dry): bool
    {
        try {
            $teacher = $letter->teacher;
            $principal = $letter->principal;

            $teacherEmail = $teacher?->email;
            $principalEmail = $principal?->email;

            $teacherUser = $teacherEmail ? User::where('email', $teacherEmail)->first() : null;
            $principalUser = $principal instanceof User ? $principal : null;

            $sendToTeacher = in_array($kind, ['pending', 'overdue'], true);
            $sendToPrincipal = in_array($kind, ['overdue', 'follow_up_pending'], true);

            if ($dry) {
                $this->line(sprintf(
                    '[dry-run] LOI #%d kind=%s teacher=%s principal=%s',
                    $letter->id,
                    $kind,
                    $sendToTeacher ? ($teacherEmail ?? 'no-email') : '-',
                    $sendToPrincipal ? ($principalEmail ?? 'no-email') : '-',
                ));
                Log::info('loi:send-reminders dry-run', [
                    'letter_id' => $letter->id,
                    'kind' => $kind,
                ]);
                return true;
            }

            $sentAny = false;

            if ($sendToTeacher) {
                if ($teacherEmail) {
                    Mail::to($teacherEmail)->queue(new LoiReminderMail($letter, $kind));
                    $sentAny = true;
                }
                if ($teacherUser) {
                    $this->notifyUser($teacherUser, $letter, $kind);
                    $sentAny = true;
                }
            }

            if ($sendToPrincipal) {
                if ($principalEmail) {
                    Mail::to($principalEmail)->queue(new LoiReminderMail($letter, $kind));
                    $sentAny = true;
                }
                if ($principalUser) {
                    $this->notifyUser($principalUser, $letter, $kind);
                    $sentAny = true;
                }
            }

            if ($sentAny) {
                $letter->forceFill(['last_reminder_at' => Carbon::now()])->saveQuietly();
            }

            return $sentAny;
        } catch (Throwable $e) {
            Log::warning('loi:send-reminders failure', [
                'letter_id' => $letter->id,
                'kind' => $kind,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected function notifyUser(User $user, LetterOfIntent $letter, string $kind): void
    {
        $title = match ($kind) {
            'pending' => 'Letter of Intent — please sign',
            'overdue' => 'Letter of Intent — overdue',
            'follow_up_pending' => 'Letter of Intent — follow-up needed',
            default => 'Letter of Intent reminder',
        };

        $body = match ($kind) {
            'pending' => 'Please sign your Letter of Intent before the deadline.',
            'overdue' => 'Your Letter of Intent is past its deadline. Please respond as soon as possible.',
            'follow_up_pending' => 'A declined Letter of Intent requires your follow-up.',
            default => 'A Letter of Intent requires your attention.',
        };

        Notification::make()
            ->title($title)
            ->body($body . ' (AY ' . ($letter->academic_year ?? '—') . ')')
            ->icon('heroicon-o-envelope')
            ->color($kind === 'overdue' ? 'danger' : ($kind === 'follow_up_pending' ? 'warning' : 'info'))
            ->sendToDatabase($user);
    }
}
