<?php

namespace App\Http\Controllers;

use App\Models\SubstituteOffer;
use App\Support\SubstituteBroadcaster;
use Illuminate\Http\Request;

class SubstituteOfferController extends Controller
{
    public function accept(Request $request, SubstituteOffer $offer)
    {
        return $this->respond($request, $offer, 'accepted');
    }

    public function decline(Request $request, SubstituteOffer $offer)
    {
        return $this->respond($request, $offer, 'declined');
    }

    protected function respond(Request $request, SubstituteOffer $offer, string $decision)
    {
        // Signed middleware validates the URL signature; also check token matches.
        if (! hash_equals($offer->token, (string) $request->query('token'))) {
            return $this->page('error', 'Invalid link.', $offer);
        }

        $leave = $offer->leave()->with('teacher')->first();
        if (! $leave) {
            return $this->page('error', 'Leave request no longer exists.', $offer);
        }

        if ($decision === 'accepted') {
            // Is anyone already accepted? Slot filled.
            $accepted = $leave->offers()->where('status', 'accepted')->first();
            if ($accepted && $accepted->id !== $offer->id) {
                return $this->page('filled', 'This cover slot has already been filled by another teacher. Thank you for being available!', $offer);
            }

            if ($offer->status === 'accepted') {
                return $this->page('already', 'You already accepted this cover. Thank you!', $offer);
            }

            if ($offer->status !== 'pending') {
                return $this->page('error', 'This offer is no longer active.', $offer);
            }

            if ($offer->expires_at && $offer->expires_at->isPast()) {
                $offer->update(['status' => 'expired']);
                return $this->page('expired', 'This cover offer has expired.', $offer);
            }

            $offer->update([
                'status'       => 'accepted',
                'responded_at' => now(),
            ]);

            // Auto-assign the substitute on the leave.
            $leave->update([
                'substitute_teacher_id' => $offer->teacher_id,
            ]);

            // Cancel other pending offers.
            SubstituteBroadcaster::cancelPendingExcept($leave, $offer->id);

            return $this->page('accepted', 'Thank you! You have been assigned as the substitute. The principal\'s office has been notified.', $offer);
        }

        // Decline
        if (! in_array($offer->status, ['pending', 'accepted'], true)) {
            return $this->page('already', 'You already responded to this offer.', $offer);
        }

        $offer->update([
            'status'       => 'declined',
            'responded_at' => now(),
        ]);

        // If they had accepted earlier and now decline, clear the leave's assignment.
        if ($leave->substitute_teacher_id === $offer->teacher_id) {
            $leave->update(['substitute_teacher_id' => null]);
        }

        return $this->page('declined', 'Thanks for letting us know. We\'ll find someone else.', $offer);
    }

    protected function page(string $state, string $message, SubstituteOffer $offer)
    {
        return response()->view('substitute-offer.response', [
            'state'   => $state,
            'message' => $message,
            'offer'   => $offer->load('teacher', 'leave.teacher'),
        ]);
    }
}
