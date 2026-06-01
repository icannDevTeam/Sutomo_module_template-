<?php

namespace App\Http\Controllers;

use App\Models\SubstituteOffer;
use App\Support\SubstituteBroadcaster;
use Illuminate\Http\Request;

class SubstituteOfferController extends Controller
{
    public function accept(Request $request, SubstituteOffer $offer)
    {
        return $this->respond($request, $offer, 'interested');
    }

    public function decline(Request $request, SubstituteOffer $offer)
    {
        return $this->respond($request, $offer, 'declined');
    }

    protected function respond(Request $request, SubstituteOffer $offer, string $decision)
    {
        if (! hash_equals($offer->token, (string) $request->query('token'))) {
            return $this->page('error', 'Invalid link.', $offer);
        }

        $leave = $offer->leave()->with('teacher')->first();
        if (! $leave) {
            return $this->page('error', 'Leave request no longer exists.', $offer);
        }

        // Final state — nothing the teacher can do.
        if ($offer->status === 'assigned') {
            return $this->page('assigned', 'The principal selected you as the substitute. Thank you!', $offer);
        }
        if (in_array($offer->status, ['cancelled', 'expired'], true)) {
            return $this->page('closed', 'This cover search has been closed.', $offer);
        }
        if ($offer->expires_at && $offer->expires_at->isPast()) {
            $offer->update(['status' => 'expired']);
            return $this->page('expired', 'This invitation has expired.', $offer);
        }
        if ($leave->substitute_teacher_id && $leave->substitute_teacher_id !== $offer->teacher_id) {
            return $this->page('filled', 'A substitute has already been assigned. Thank you for being available!', $offer);
        }

        if ($decision === 'interested') {
            if ($leave->auto_search_status === 'closed_full') {
                return $this->page('closed', 'The cover search has reached its limit. Thanks for being available!', $offer);
            }

            $offer->update([
                'status'       => 'interested',
                'responded_at' => now(),
            ]);

            // If this fills the cap, close the search automatically.
            SubstituteBroadcaster::checkAndCloseIfFull($leave->refresh());

            return $this->page('interested', 'Thanks for expressing interest! The principal will review and confirm the substitute selection.', $offer);
        }

        // Decline (or change of mind from interested → declined)
        $offer->update([
            'status'       => 'declined',
            'responded_at' => now(),
        ]);

        return $this->page('declined', 'Thanks for letting us know. The principal will keep looking.', $offer);
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
