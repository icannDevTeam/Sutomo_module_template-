<?php

namespace App\Mail;

use App\Models\SubstituteOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class SubstituteOfferMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SubstituteOffer $offer)
    {
    }

    public function envelope(): Envelope
    {
        $leave = $this->offer->leave;
        $teacherName = $leave?->teacher?->name ?? 'a teacher';
        $when = optional($leave?->starts_at)->format('D, d M Y');

        return new Envelope(
            subject: "Cover request — {$teacherName} · {$when}",
        );
    }

    public function content(): Content
    {
        $expires = $this->offer->expires_at ?? now()->addHours(4);

        $acceptUrl = URL::temporarySignedRoute(
            'substitute-offer.accept',
            $expires,
            ['offer' => $this->offer->id, 'token' => $this->offer->token],
        );

        $declineUrl = URL::temporarySignedRoute(
            'substitute-offer.decline',
            $expires,
            ['offer' => $this->offer->id, 'token' => $this->offer->token],
        );

        return new Content(
            markdown: 'mail.substitute-offer',
            with: [
                'offer'      => $this->offer,
                'leave'      => $this->offer->leave,
                'teacher'    => $this->offer->teacher,
                'acceptUrl'  => $acceptUrl,
                'declineUrl' => $declineUrl,
                'expiresAt'  => $expires,
            ],
        );
    }
}
