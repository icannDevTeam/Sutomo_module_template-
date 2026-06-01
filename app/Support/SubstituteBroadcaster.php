<?php

namespace App\Support;

use App\Mail\SubstituteOfferMail;
use App\Models\SubstituteOffer;
use App\Models\TeacherLeave;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SubstituteBroadcaster
{
    public const DEFAULT_BATCH_SIZE = 3;
    public const DEFAULT_TTL_HOURS  = 4;

    /**
     * Broadcast an offer to the next N candidates who haven't been offered yet.
     * Returns the offers created.
     */
    public static function broadcast(
        TeacherLeave $leave,
        ?array $teacherIds = null,
        int $size = self::DEFAULT_BATCH_SIZE,
        int $ttlHours = self::DEFAULT_TTL_HOURS,
    ): array {
        $offered = $leave->offers()->pluck('teacher_id')->all();

        if ($teacherIds === null) {
            $candidates = SubstituteSuggester::for($leave, 20)
                ->filter(fn ($c) => $c['is_assignable'])
                ->reject(fn ($c) => in_array($c['teacher']->id, $offered, true))
                ->take($size);
            $teacherIds = $candidates->map(fn ($c) => $c['teacher']->id)->all();
        } else {
            $teacherIds = array_values(array_diff($teacherIds, $offered));
        }

        if (empty($teacherIds)) {
            return [];
        }

        $round = ((int) $leave->offers()->max('round') ?: 0) + 1;
        $expiresAt = Carbon::now()->addHours($ttlHours);

        $created = [];
        foreach ($teacherIds as $teacherId) {
            $offer = SubstituteOffer::create([
                'teacher_leave_id' => $leave->id,
                'teacher_id'       => $teacherId,
                'token'            => SubstituteOffer::generateToken(),
                'status'           => 'pending',
                'sent_at'          => now(),
                'expires_at'       => $expiresAt,
                'round'            => $round,
            ]);

            $offer->load('teacher', 'leave.teacher');

            if ($offer->teacher?->email) {
                try {
                    Mail::to($offer->teacher->email)->send(new SubstituteOfferMail($offer));
                } catch (\Throwable $e) {
                    // Swallow mail errors in dev (log mailer or missing SMTP); offer still created.
                    report($e);
                }
            }

            $created[] = $offer;
        }

        return $created;
    }

    /**
     * Mark all other pending offers for the leave as cancelled (after one is accepted).
     */
    public static function cancelPendingExcept(TeacherLeave $leave, int $exceptOfferId): int
    {
        return $leave->offers()
            ->where('id', '!=', $exceptOfferId)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled', 'responded_at' => now()]);
    }
}
