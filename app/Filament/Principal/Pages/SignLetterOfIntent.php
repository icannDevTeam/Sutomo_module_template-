<?php

namespace App\Filament\Principal\Pages;

use App\Filament\Principal\Resources\LetterOfIntentResource;
use App\Models\LetterOfIntent;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SignLetterOfIntent extends Page
{
    protected static string $view = 'filament.principal.pages.sign-letter-of-intent';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'letters-of-intent/{record}/sign';
    protected static ?string $title = 'Sign Letter of Intent';

    public ?LetterOfIntent $letter = null;
    public string $signatureText = '';
    public string $declineReason = '';

    public function mount(int|string $record): void
    {
        $this->letter = LetterOfIntent::findOrFail($record);
    }

    public function sign(): void
    {
        $text = trim($this->signatureText);
        if ($text === '' || mb_strlen($text) > 200) {
            Notification::make()
                ->title('Please type your full name (max 200 chars) to sign.')
                ->danger()
                ->send();
            return;
        }

        if (! $this->letter || $this->letter->status !== 'sent') {
            Notification::make()->title('This letter cannot be signed.')->danger()->send();
            return;
        }

        // Only the bound teacher's user account may sign. Principals/HR cannot impersonate.
        $teacherUserId = $this->letter->teacher?->user_id ?? null;
        if ($teacherUserId === null || auth()->id() !== $teacherUserId) {
            abort(403, 'Only the named teacher may sign this letter.');
        }

        $this->letter->forceFill([
            'status'         => 'signed',
            'signed_at'      => now(),
            'signature_text' => $text,
            'signature_ip'   => request()->ip(),
        ])->save();

        Notification::make()
            ->title('Letter signed. Thank you!')
            ->success()
            ->send();

        $this->redirect(LetterOfIntentResource::getUrl('view', ['record' => $this->letter->id], panel: 'principal'));
    }

    public function decline(): void
    {
        if (! $this->letter || $this->letter->status !== 'sent') {
            Notification::make()->title('This letter cannot be declined.')->danger()->send();
            return;
        }

        $teacherUserId = $this->letter->teacher?->user_id ?? null;
        if ($teacherUserId === null || auth()->id() !== $teacherUserId) {
            abort(403, 'Only the named teacher may decline this letter.');
        }

        $reason = trim($this->declineReason);
        if (mb_strlen($reason) > 1000) {
            $reason = mb_substr($reason, 0, 1000);
        }

        $this->letter->forceFill([
            'status'         => 'declined',
            'decline_reason' => $reason ?: null,
        ])->save();

        Notification::make()->title('Letter declined.')->success()->send();

        $this->redirect(LetterOfIntentResource::getUrl('view', ['record' => $this->letter->id], panel: 'principal'));
    }
}
