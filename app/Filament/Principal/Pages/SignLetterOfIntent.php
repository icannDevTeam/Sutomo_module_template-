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

    /** 'loi' (default) | 'agreement' — agreement mode signs the post-Yayasan agreement letter (= contract handover). */
    public string $mode = 'loi';

    public function mount(int|string $record): void
    {
        $this->letter = LetterOfIntent::findOrFail($record);
        $requested = (string) request()->query('mode', 'loi');
        $this->mode = in_array($requested, ['loi', 'agreement'], true) ? $requested : 'loi';
    }

    public function isAgreementMode(): bool
    {
        return $this->mode === 'agreement';
    }

    public function signAgreement(): void
    {
        $text = trim($this->signatureText);
        if ($text === '' || mb_strlen($text) > 200) {
            Notification::make()->title('Please type your full name (max 200 chars) to sign.')->danger()->send();
            return;
        }
        if (! $this->letter) {
            Notification::make()->title('Letter not found.')->danger()->send();
            return;
        }
        if (is_null($this->letter->yayasan_contract_uploaded_at)) {
            Notification::make()->title('Yayasan contract has not been uploaded yet.')->danger()->send();
            return;
        }
        if ($this->letter->yayasan_review_status !== 'accepted') {
            Notification::make()->title('Principal must accept the contract before agreement signing.')->danger()->send();
            return;
        }
        if (! is_null($this->letter->agreement_signed_at)) {
            Notification::make()->title('Agreement already signed.')->warning()->send();
            return;
        }

        $teacherUserId = $this->letter->teacher?->user_id ?? null;
        if ($teacherUserId === null || auth()->id() !== $teacherUserId) {
            abort(403, 'Only the named teacher may e-sign the agreement letter.');
        }

        $this->letter->forceFill([
            'agreement_signed_at'       => now(),
            'agreement_signature_text'  => $text,
            'agreement_signature_ip'    => request()->ip(),
            'buku_induk_recorded_at'    => $this->letter->buku_induk_recorded_at ?? now(),
            'hr_handoff_status'         => 'uploaded',
            'hr_uploaded_at'            => $this->letter->hr_uploaded_at ?? now(),
        ])->save();

        Notification::make()->title('Agreement letter signed. Buku Induk was logged automatically.')->success()->send();

        $this->redirect(LetterOfIntentResource::getUrl('view', ['record' => $this->letter->id], panel: 'principal'));
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
