<?php

namespace App\Http\Controllers;

use App\Models\ContractDocumentVersion;
use App\Models\LetterOfIntent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves contract document versions from the private disk, after checking
 * the caller is a party to the request (submitter, assigned HR, named
 * teacher, Yayasan or admin).
 */
class ContractDocumentController extends Controller
{
    public function download(Request $request, ContractDocumentVersion $document): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();
        $approval = $document->request()->with(['teacher'])->firstOrFail();

        $role = $user->role ?? 'teacher';
        $allowed = in_array($role, User::YAYASAN_ROLES, true)
            || in_array($role, ['admin', 'superadmin'], true)
            || $approval->submitted_by === $user->id
            || $approval->assigned_hr_id === $user->id
            || ($approval->teacher?->user_id !== null && $approval->teacher->user_id === $user->id)
            || ($role === 'hr' && $approval->assigned_hr_id === null);

        abort_unless($allowed, 403, 'You are not a party to this request.');

        $disk = Storage::disk(config('approvals.document_disk', 'local'));
        abort_unless($disk->exists($document->storage_path), 404, 'Document file is missing.');

        $filename = $document->original_filename ?: "contract-v{$document->version_number}.pdf";
        $inline = $request->boolean('inline', true) && str_starts_with((string) $document->mime_type, 'application/pdf');

        return $disk->response($document->storage_path, $filename, [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], $inline ? 'inline' : 'attachment');
    }

    public function downloadLoiContract(Request $request, LetterOfIntent $letter): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();
        $approval = $letter->latestApprovalRequest;
        $allowed = in_array($user->role, ['admin', 'superadmin'], true)
            || (in_array($user->role, User::YAYASAN_ROLES, true) && $approval !== null)
            || (in_array($user->role, User::PRINCIPAL_ROLES, true) && $letter->principal_id === $user->id)
            || ($user->role === 'hr' && $approval?->assigned_hr_id === $user->id)
            || $approval?->submitted_by === $user->id
            || $letter->teacher?->user_id === $user->id;

        abort_unless($allowed, 403, 'You are not authorized to view this contract.');
        abort_if(blank($letter->yayasan_contract_path), 404, 'Contract file is missing.');

        $disks = array_unique([
            config('approvals.document_disk', 'local'),
            'public',
        ]);
        $disk = collect($disks)
            ->map(fn (string $name) => Storage::disk($name))
            ->first(fn ($disk) => $disk->exists($letter->yayasan_contract_path));

        abort_unless($disk, 404, 'Contract file is missing.');

        $inline = $request->boolean('inline', true);

        return $disk->response(
            $letter->yayasan_contract_path,
            'continuation-contract-' . $letter->id . '.pdf',
            [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ],
            $inline ? 'inline' : 'attachment'
        );
    }
}
