<?php

namespace App\Filament\Principal\Pages;

use App\Filament\Principal\Resources\LetterOfIntentResource;
use App\Models\ContractApprovalRequest;
use App\Models\LetterOfIntent;
use App\Models\User;
use App\Services\ContractApprovalWorkflow;
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
    public bool $simulation = false;

    public function mount(int|string $record): void
    {
        $this->letter = LetterOfIntent::findOrFail($record);
        $requested = (string) request()->query('mode', 'loi');
        $this->mode = in_array($requested, ['loi', 'agreement'], true) ? $requested : 'loi';
        $this->simulation = $this->mode === 'agreement'
            && request()->boolean('simulation')
            && in_array(auth()->user()?->role, [...User::HR_SIMULATION_ROLES, 'admin', 'superadmin'], true);
    }

    public function isAgreementMode(): bool
    {
        return $this->mode === 'agreement';
    }

    public function canSimulateAgreement(): bool
    {
        return $this->simulation
            && $this->isAgreementMode()
            && in_array(auth()->user()?->role, [...User::HR_SIMULATION_ROLES, 'admin', 'superadmin'], true);
    }

    public function signAgreement(): void
    {
        if ($this->canSimulateAgreement()) {
            if (! $this->letter) {
                Notification::make()->title('Letter not found.')->danger()->send();
                return;
            }

            $approval = ContractApprovalRequest::where('source_loi_id', $this->letter->id)
                ->where('type', 'annual_continuation')
                ->latest('id')
                ->first();

            if (! $approval || $approval->stage !== 'document_accepted') {
                Notification::make()
                    ->title('The accepted contract is not ready for demo signing.')
                    ->body('The approval request must be accepted before the Agreement signing simulation.')
                    ->danger()
                    ->send();
                return;
            }

            try {
                app(ContractApprovalWorkflow::class)->simulateTeacherSigning($approval, auth()->user(), (string) request()->ip());
            } catch (\RuntimeException $e) {
                Notification::make()->title($e->getMessage())->danger()->send();
                return;
            }

            Notification::make()
                ->title('Demo Agreement signing recorded')
                ->body('The request is now awaiting HR registration. No teacher account or legal signature was used.')
                ->success()
                ->send();

            $this->redirect(\App\Filament\Principal\Pages\MySubmissions::getUrl(
                ['tab' => 'progress', 'type' => 'annual_continuation'],
                panel: 'principal'
            ));
            return;
        }

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
            Notification::make()->title('Only the named teacher account can e-sign this agreement letter.')->danger()->send();
            return;
        }

        $approval = ContractApprovalRequest::where('source_loi_id', $this->letter->id)
            ->where('type', 'annual_continuation')
            ->latest('id')
            ->first();
        if ($approval) {
            try {
                app(ContractApprovalWorkflow::class)->signDocument($approval, auth()->user(), $text, (string) request()->ip());
            } catch (\RuntimeException $e) {
                Notification::make()->title($e->getMessage())->danger()->send();
                return;
            }

            Notification::make()->title('Agreement signed. HR registration is next.')->success()->send();
            $this->redirect(\App\Filament\Principal\Pages\MySubmissions::getUrl(
                ['tab' => 'progress', 'type' => 'annual_continuation'],
                panel: 'principal'
            ));
            return;
        }

        $this->letter->forceFill([
            'yayasan_review_status'      => $this->letter->yayasan_review_status ?: 'accepted',
            'yayasan_review_notes'       => $this->letter->yayasan_review_notes ?: 'Auto-accepted during Agreement Letter signing (legacy compatibility).',
            'yayasan_reviewed_by'        => $this->letter->yayasan_reviewed_by ?: auth()->id(),
            'yayasan_reviewed_at'        => $this->letter->yayasan_reviewed_at ?: now(),
            'agreement_signed_at'       => now(),
            'agreement_signature_text'  => $text,
            'agreement_signature_ip'    => request()->ip(),
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
