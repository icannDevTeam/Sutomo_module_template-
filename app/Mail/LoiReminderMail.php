<?php

namespace App\Mail;

use App\Models\LetterOfIntent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoiReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public LetterOfIntent $letter,
        public string $kind,
    ) {}

    public function envelope(): Envelope
    {
        $prefix = match ($this->kind) {
            'overdue' => 'Overdue · ',
            'follow_up_pending' => 'Follow-up needed · ',
            default => 'Reminder · ',
        };

        return new Envelope(
            subject: $prefix . 'Letter of Intent · ' . ($this->letter->academic_year ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loi-reminder',
            with: [
                'letter' => $this->letter,
                'kind' => $this->kind,
            ],
        );
    }
}
