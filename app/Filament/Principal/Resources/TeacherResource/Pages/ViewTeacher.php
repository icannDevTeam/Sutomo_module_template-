<?php

namespace App\Filament\Principal\Resources\TeacherResource\Pages;

use App\Filament\Principal\Resources\TeacherResource;
use App\Models\QueryLetter;
use App\Models\TeacherNote;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class ViewTeacher extends Page
{
    protected static string $resource = TeacherResource::class;
    protected static string $view = 'filament.principal.teacher.profile';

    public \App\Models\Teacher $record;

    /** Active tab (URL-shareable via Livewire). */
    public string $tab = 'profile';

    public ?int $editingNoteId = null;
    public string $newNoteBody = '';

    public function mount(int|string $record): void
    {
        $this->record = \App\Models\Teacher::findOrFail($record);
        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::can('view', $this->record), 403);
    }

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, $this->tabKeys(), true) ? $tab : 'profile';
    }

    /** Tabs definition (id => label). */
    public function tabs(): array
    {
        return [
            'profile'        => 'Profile',
            'attendance'     => 'Attendance',
            'duties'         => 'Duties',
            'substitutions'  => 'Substitutions',
            'qualifications' => 'Qualifications',
            'leaves'         => 'Leaves',
            'query_letters'  => 'Query Letters',
            'observations'   => 'Observations',
            'goals'          => 'Goals',
            'notes'          => 'Principal Notes',
        ];
    }

    protected function tabKeys(): array
    {
        return array_keys($this->tabs());
    }

    /* -----------------------------------------------------------------
     |  Header actions (always available)
     | ----------------------------------------------------------------- */

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addNote')
                ->label('Add note')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->form([
                    Forms\Components\Textarea::make('body')->label('Note')->rows(4)->required(),
                    Forms\Components\Toggle::make('pinned')->label('Pin to top')->default(false),
                ])
                ->action(function (array $data): void {
                    TeacherNote::create([
                        'teacher_id' => $this->record->id,
                        'author_id'  => auth()->id(),
                        'body'       => trim($data['body']),
                        'pinned'     => (bool) ($data['pinned'] ?? false),
                    ]);
                    Notification::make()->title('Note added')->success()->send();
                    $this->tab = 'notes';
                }),

            Actions\Action::make('issueQueryLetter')
                ->label('Issue query letter')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->form([
                    Forms\Components\TextInput::make('title')->required()->maxLength(150),
                    Forms\Components\DatePicker::make('issued_at')->default(now())->required(),
                    Forms\Components\Textarea::make('body')->label('Letter body')->rows(6)->required(),
                ])
                ->action(function (array $data): void {
                    QueryLetter::create([
                        'teacher_id' => $this->record->id,
                        'issued_by'  => auth()->id(),
                        'title'      => $data['title'],
                        'body'       => $data['body'],
                        'issued_at'  => $data['issued_at'],
                        'status'     => 'sent',
                    ]);
                    Notification::make()->title('Query letter issued')->success()->send();
                    $this->tab = 'query_letters';
                }),

            Actions\Action::make('edit')
                ->label('Edit profile')
                ->icon('heroicon-o-pencil')
                ->color('gray')
                ->url(fn () => static::getResource()::getUrl('edit', ['record' => $this->record])),
        ];
    }

    /* -----------------------------------------------------------------
     |  Note actions
     | ----------------------------------------------------------------- */

    public function togglePinNote(int $id): void
    {
        $note = $this->record->notes()->whereKey($id)->first();
        if (! $note) return;
        $note->update(['pinned' => ! $note->pinned]);
    }

    public function deleteNote(int $id): void
    {
        $note = $this->record->notes()->whereKey($id)->first();
        if (! $note) return;
        if ($note->author_id && $note->author_id !== auth()->id()
            && ! in_array(auth()->user()?->role ?? '', ['admin', 'principal'], true)) {
            return;
        }
        $note->delete();
        Notification::make()->title('Note deleted')->success()->send();
    }

    /* -----------------------------------------------------------------
     |  Query letter actions
     | ----------------------------------------------------------------- */

    public ?int $respondingLetterId = null;
    public string $responseDraft = '';

    public function openResponse(int $id): void
    {
        $this->respondingLetterId = $id;
        $this->responseDraft = (string) ($this->record->queryLetters()->whereKey($id)->value('response') ?? '');
    }

    public function cancelResponse(): void
    {
        $this->respondingLetterId = null;
        $this->responseDraft = '';
    }

    public function saveResponse(): void
    {
        if (! $this->respondingLetterId) return;
        $letter = $this->record->queryLetters()->whereKey($this->respondingLetterId)->first();
        if (! $letter) return;
        $letter->update([
            'response'     => trim($this->responseDraft) ?: null,
            'responded_at' => $this->responseDraft ? now() : null,
            'status'       => $this->responseDraft ? 'responded' : 'sent',
        ]);
        Notification::make()->title('Response recorded')->success()->send();
        $this->cancelResponse();
    }

    public function closeLetter(int $id): void
    {
        $letter = $this->record->queryLetters()->whereKey($id)->first();
        if (! $letter) return;
        $letter->update(['status' => 'closed']);
        Notification::make()->title('Letter closed')->success()->send();
    }

    /* -----------------------------------------------------------------
     |  Data for tabs
     | ----------------------------------------------------------------- */

    protected function getViewData(): array
    {
        $t = $this->record;
        return [
            'tabs'              => $this->tabs(),
            'currentTab'        => $this->tab,
            'notes'             => $t->notes()->with('author')->get(),
            'queryLetters'      => $t->queryLetters()->with('issuedBy')->get(),
            'leaves'            => $t->leaves()->with('substitute')->orderByDesc('starts_at')->get(),
            'duties'            => $t->duties()->orderByDesc('starts_at')->get(),
            'attendance'        => $t->attendance()->orderByDesc('date')->limit(60)->get(),
            'observations'      => $t->observations()->with('observer')->orderByDesc('observed_at')->get(),
            'goals'             => $t->goals()->orderByDesc('target_date')->get(),
            'documents'         => $t->documents()->orderByDesc('created_at')->get(),
            'certifications'    => $t->formalCertifications()->orderByDesc('issued_at')->get(),
            'clearances'        => $t->clearances()->orderByDesc('issued_at')->get(),
            'trainings'         => $t->trainings()->orderByDesc('starts_on')->get(),
            'substitutesGiven'  => $t->substituteOffers()->with('leave.teacher')->orderByDesc('sent_at')->limit(50)->get(),
            'coveredFor'        => \App\Models\TeacherLeave::where('substitute_teacher_id', $t->id)
                                    ->with('teacher')->orderByDesc('starts_at')->limit(50)->get(),
            'respondingLetter'  => $this->respondingLetterId
                ? $t->queryLetters()->whereKey($this->respondingLetterId)->first() : null,
        ];
    }
}
