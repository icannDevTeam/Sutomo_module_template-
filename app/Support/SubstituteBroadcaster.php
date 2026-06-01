<?php

namespace App\Support;

use App\Mail\SubstituteOfferMail;
use App\Models\SubstituteOffer;
use App\Models\TeacherLeave;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SubstituteBroadcaster
{
    public const DEFAULT_TTL_HOURS    = 24;
    public const DEFAULT_CAP          = 5;
    public const DEFAULT_INVITE_LIMIT = 15;

    /**
     * Opens auto substitute search for a leave: emails up to N eligible
     * candidates inviting them to express interest. Principal still picks
     * the final substitute manually from those who express interest.
     */
    public static function startAutoSearch(
        TeacherLeave $leave,
        int $ttlHours = self::DEFAULT_TTL_HOURS,
        int $cap = self::DEFAULT_CAP,
        int $inviteLimit = self::DEFAULT_INVITE_LIMIT,
    ): array {
        if ($leave->substitute_teacher_id) {
            return [];
        }

        $existingTeacherIds = $leave->offers()->pluck('teacher_id')->all();

        $candidates = SubstituteSuggester::for($leave, 50)
            ->filter(fn ($c) => $c['is_assignable'])
            ->reject(fn ($c) => in_array($c['teacher']->id, $existingTeacherIds, true))
            ->take($inviteLimit);

        $expiresAt = Carbon::now()->addHours($ttlHours);
        $round     = ((int) $leave->offers()->max('round') ?: 0) + 1;

        $created = [];
        foreach ($candidates as $candidate) {
            $teacher = $candidate['teacher'];

            $offer = SubstituteOffer::create([
                'teacher_leave_id' => $leave->id,
                'teacher_id'       => $teacher->id,
                'token'            => SubstituteOffer::generateToken(),
                'status'           => 'pending',
                'sent_at'          => now(),
                'expires_at'       => $expiresAt,
                'round'            => $round,
            ]);

            if ($teacher->email) {
                try {
                    $offer->load('teacher', 'leave.teacher');
                    Mail::to($teacher->email)->send(new SubstituteOfferMail($offer));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
            $created[] = $offer;
        }

        $leave->update([
            'auto_search_enabled'   => true,
            'auto_search_status'    => 'open',
            'auto_search_opened_at' => now(),
            'auto_search_closes_at' => $expiresAt,
            'auto_search_closed_at' => null,
            'auto_search_cap'       => $cap,
        ]);

        return $created;
    }

    /**
     * Called after a teacher expresses interest. If cap reached, auto-close search.
     */
    public static function checkAndCloseIfFull(TeacherLeave $leave): bool
    {
        if ($leave->auto_search_status !== 'open') {
            return false;
        }
        $cap = (int) ($leave->auto_search_cap ?: self::DEFAULT_CAP);
        $interested = $leave->offers()->where('status', 'interested')->count();
        if ($interested >= $cap) {
            self::closeAutoSearch($leave, 'closed_full');
            return true;
        }
        return false;
    }

    /**
     * Close the auto-search (manual / full / expired / assigned).
     * Cancels still-pending invitations; leaves 'interested' offers alone so
     * the principal can still assign one of them.
     */
    public static function closeAutoSearch(TeacherLeave $leave, string $reason = 'closed_manual'): void
    {
        $leave->offers()
            ->where('status', 'pending')
            ->update(['status' => 'cancelled', 'responded_at' => now()]);

        $leave->update([
            'auto_search_status'    => $reason,
            'auto_search_closed_at' => now(),
        ]);
    }

    /**
     * Principal picks one interested teacher as the substitute.
     * Marks them 'assigned', sets leave.substitute_teacher_id, closes search,
     * notifies the rest.
     */
    public static function assignFromInterested(TeacherLeave $leave, int $offerId): ?SubstituteOffer
    {
        $offer = $leave->offers()->where('id', $offerId)->first();
        if (! $offer || $offer->status !== 'interested') {
            return null;
        }

        $offer->update([
            'status'       => 'assigned',
            'responded_at' => $offer->responded_at ?? now(),
        ]);

        $leave->update([
            'substitute_teacher_id' => $offer->teacher_id,
            'auto_search_status'    => 'closed_assigned',
            'auto_search_closed_at' => now(),
        ]);

        // Mark other open invitations / interests as cancelled.
        $leave->offers()
            ->where('id', '!=', $offer->id)
            ->whereIn('status', ['pending', 'interested'])
            ->update(['status' => 'cancelled', 'responded_at' => now()]);

        return $offer;
    }
}
