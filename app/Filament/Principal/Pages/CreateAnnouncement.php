<?php

namespace App\Filament\Principal\Pages;

use App\Models\Announcement;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class CreateAnnouncement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Create announcement';
    protected static string $view = 'filament.principal.pages.create-announcement';
    protected static ?string $slug = 'announcements/create';

    public ?string $bannerEmoji = null;
    public string $title_ = '';
    public string $body = '';

    public string $category = 'general';
    public bool   $pinned = false;
    public ?string $scheduledAt = null;

    /** recipient type checkboxes */
    public array $recipientTypes = ['all_members' => false, 'staff' => false, 'students' => false, 'family' => true];
    /** recipient group radio */
    public string $recipientGroup = 'classes'; // programmes | grades | classes | custom
    /** selected items within the chosen group */
    public array $selectedGroups = [];

    /** delivery channels */
    public array $channels = ['app' => true, 'email' => false, 'whatsapp' => false];

    /** recently used recipient sets shown as chips */
    public array $recents = [
        'Last week’s SD parents',
        'All teachers — in-app',
        'Grade 10 — SMA',
    ];

    public array $bannerEmojiPalette = ['🎉','📣','📚','🎓','🏆','🗓️','🚌','🏫','💡','🤝','🌱','⚽'];

    /* ---------------- Custom audience modal ---------------- */
    public bool $customOpen = false;
    public string $customTab = 'classes';            // classes | teachers | students | school | campus | unit | stream
    public string $customSearch = '';
    /** @var array<string, array<int|string>> map of bucket => array of ids/keys */
    public array $customPicked = [
        'classes'  => [],
        'teachers' => [],
        'students' => [],
        'school'   => [],
        'campus'   => [],
        'unit'     => [],
        'stream'   => [],
    ];
    /** label cache keyed by "bucket:id" so chips don't require re-querying */
    public array $customLabels = [];

    /** When editing, this carries the announcement id via ?record=… */
    #[Url] public ?int $record = null;

    public function mount(): void
    {
        if ($this->record) {
            $a = Announcement::find($this->record);
            if ($a) {
                $this->title_       = $a->title;
                $this->body         = $a->body;
                $this->category     = $a->category ?: 'general';
                $this->pinned       = (bool) $a->pinned;
                $this->scheduledAt  = optional($a->scheduled_at)->format('Y-m-d\\TH:i');
                $this->channels     = array_merge(
                    ['app' => false, 'email' => false, 'whatsapp' => false],
                    collect((array) $a->channels)->mapWithKeys(fn ($c) => [$c => true])->all()
                );
            } else {
                $this->record = null;
            }
        }
    }

    public function getTitle(): string
    {
        return $this->record ? 'Edit announcement' : 'Create announcement';
    }

    public function getHeading(): string
    {
        return $this->getTitle();
    }

    public function toggleType(string $key): void
    {
        $this->recipientTypes[$key] = ! ($this->recipientTypes[$key] ?? false);
        if ($key === 'all_members' && $this->recipientTypes[$key]) {
            $this->recipientTypes = ['all_members' => true, 'staff' => true, 'students' => true, 'family' => true];
        }
    }

    public function toggleChannel(string $key): void
    {
        $this->channels[$key] = ! ($this->channels[$key] ?? false);
    }

    public function setGroup(string $group): void
    {
        $this->recipientGroup = $group;
        $this->selectedGroups = [];
        if ($group === 'custom') {
            $this->openCustom();
        }
    }

    /* ---------------- Custom modal handlers ---------------- */
    public function openCustom(string $tab = 'classes'): void
    {
        $this->customTab = $tab;
        $this->customSearch = '';
        $this->customOpen = true;
    }

    public function closeCustom(): void
    {
        $this->customOpen = false;
        $this->customSearch = '';
    }

    public function setCustomTab(string $tab): void
    {
        $this->customTab = $tab;
        $this->customSearch = '';
    }

    public function toggleCustomItem(string $bucket, $id, string $label): void
    {
        $arr = $this->customPicked[$bucket] ?? [];
        $key = (string) $id;
        if (in_array($key, array_map('strval', $arr), true)) {
            $this->customPicked[$bucket] = array_values(array_filter($arr, fn ($x) => (string) $x !== $key));
            unset($this->customLabels["{$bucket}:{$key}"]);
        } else {
            $this->customPicked[$bucket][] = $key;
            $this->customLabels["{$bucket}:{$key}"] = $label;
        }
    }

    public function clearCustomBucket(string $bucket): void
    {
        foreach ($this->customPicked[$bucket] ?? [] as $id) {
            unset($this->customLabels["{$bucket}:{$id}"]);
        }
        $this->customPicked[$bucket] = [];
    }

    public function clearAllCustom(): void
    {
        foreach (array_keys($this->customPicked) as $b) {
            $this->customPicked[$b] = [];
        }
        $this->customLabels = [];
    }

    public function applyCustom(): void
    {
        $total = collect($this->customPicked)->sum(fn ($a) => count($a));
        $this->customOpen = false;
        Notification::make()->title("Custom audience updated")->body("{$total} recipient " . \Illuminate\Support\Str::plural('reference', $total) . ' selected.')->success()->send();
    }

    public function customTotal(): int
    {
        return collect($this->customPicked)->sum(fn ($a) => count($a));
    }

    /** Options for the active custom tab, filtered by search. */
    public function customOptions(): \Illuminate\Support\Collection
    {
        $q = trim($this->customSearch);
        switch ($this->customTab) {
            case 'teachers':
                return Teacher::query()
                    ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('subject', 'like', "%{$q}%")))
                    ->orderBy('name')->limit(80)
                    ->get(['id','name','subject','campus'])
                    ->map(fn ($t) => ['id' => $t->id, 'label' => $t->name, 'meta' => trim(($t->subject ?: '') . ($t->campus ? ' · ' . strtoupper($t->campus) : ''))]);
            case 'students':
                return Student::query()
                    ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%")))
                    ->orderBy('name')->limit(80)
                    ->get(['id','name','nis','grade','campus'])
                    ->map(fn ($s) => ['id' => $s->id, 'label' => $s->name, 'meta' => trim(($s->nis ?: '') . ($s->grade ? ' · ' . $s->grade : '') . ($s->campus ? ' · ' . strtoupper($s->campus) : ''))]);
            case 'classes':
                return SchoolClass::query()->with('homeroomTeacher')
                    ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%")))
                    ->orderBy('campus')->orderBy('grade')->orderBy('name')->limit(80)
                    ->get()
                    ->map(fn ($c) => ['id' => $c->id, 'label' => $c->name, 'meta' => trim(($c->code ?: '') . ' · ' . strtoupper($c->campus ?: '') . ($c->homeroomTeacher ? ' · ' . $c->homeroomTeacher->name : ''))]);
            case 'school':
                return collect(\App\Support\SchoolDirectory::SCHOOLS)
                    ->map(fn ($name, $code) => ['id' => $code, 'label' => $name, 'meta' => 'Yayasan-managed'])
                    ->values()
                    ->when($q !== '', fn ($c) => $c->filter(fn ($r) => stripos($r['label'], $q) !== false || stripos($r['id'], $q) !== false))->values();
            case 'campus':
                return collect(\App\Support\SchoolDirectory::CAMPUSES)
                    ->map(fn ($row, $code) => [
                        'id'    => $code,
                        'label' => $row['name'],
                        'meta'  => (\App\Support\SchoolDirectory::SCHOOLS[$row['school']] ?? $row['school']) . ' · ' . $code,
                    ])
                    ->values()
                    ->when($q !== '', fn ($c) => $c->filter(fn ($r) => stripos($r['label'], $q) !== false || stripos($r['meta'], $q) !== false))->values();
            case 'unit':
                return collect(\App\Support\SchoolDirectory::UNITS)
                    ->map(fn ($row, $slug) => [
                        'id'    => $slug,
                        'label' => $row['label'],
                        'meta'  => $row['code'],
                    ])
                    ->values()
                    ->when($q !== '', fn ($c) => $c->filter(fn ($r) => stripos($r['label'], $q) !== false || stripos($r['meta'], $q) !== false))->values();
            case 'stream':
                $dynamic = \App\Models\Student::query()
                    ->whereNotNull('stream')->where('stream', '!=', '')
                    ->distinct()->orderBy('stream')->pluck('stream')->all();
                $streams = collect(array_unique(array_merge(['IPA', 'IPS', 'Bahasa'], $dynamic)))
                    ->map(fn ($s) => [
                        'id'    => \Illuminate\Support\Str::slug($s),
                        'label' => $s,
                        'meta'  => match (strtolower($s)) {
                            'ipa'    => 'Sciences',
                            'ips'    => 'Social Sciences',
                            'bahasa' => 'Language',
                            default  => 'Stream',
                        },
                    ])
                    ->values();
                return $streams->when($q !== '', fn ($c) => $c->filter(fn ($r) => stripos($r['label'], $q) !== false || stripos($r['meta'], $q) !== false))->values();
        }
        return collect();
    }

    public function customTabs(): array
    {
        return [
            'classes'  => ['Classes',  'heroicon-m-rectangle-group'],
            'teachers' => ['Teachers', 'heroicon-m-user-circle'],
            'students' => ['Students', 'heroicon-m-academic-cap'],
            'school'   => ['Schools',  'heroicon-m-building-office-2'],
            'campus'   => ['Campus',   'heroicon-m-building-library'],
            'unit'     => ['Unit',     'heroicon-m-squares-2x2'],
            'stream'   => ['Stream',   'heroicon-m-beaker'],
        ];
    }

    public function pickGroup(string $value): void
    {
        if (in_array($value, $this->selectedGroups, true)) {
            $this->selectedGroups = array_values(array_diff($this->selectedGroups, [$value]));
        } else {
            $this->selectedGroups[] = $value;
        }
    }

    public function pickRecent(string $name): void
    {
        // Prefill audience based on the recent label.
        $this->recipientTypes = ['all_members' => false, 'staff' => false, 'students' => false, 'family' => false];
        $this->selectedGroups = [];
        if (str_contains($name, 'parents')) {
            $this->recipientTypes['family'] = true;
            $this->recipientGroup = 'grades';
            $this->selectedGroups = ['Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6'];
        } elseif (str_contains($name, 'teachers')) {
            $this->recipientTypes['staff'] = true;
            $this->recipientGroup = 'programmes';
            $this->selectedGroups = ['PYP (SD)', 'MYP (SMP)', 'DP (SMA)'];
        } else {
            $this->recipientTypes['students'] = true;
            $this->recipientTypes['family'] = true;
            $this->recipientGroup = 'classes';
            $this->selectedGroups = ['Grade 10 IPA'];
        }
        Notification::make()->title("Loaded recipients from “{$name}”")->success()->send();
    }

    public function getGroupOptions(): array
    {
        return match ($this->recipientGroup) {
            'programmes' => ['PYP (SD)', 'MYP (SMP)', 'DP (SMA)'],
            'grades'     => ['Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12'],
            'classes'    => ['Grade 1A','Grade 2A','Grade 3A','Grade 4A','Grade 4B','Grade 5A','Grade 6A','Grade 7A','Grade 8A','Grade 9A','Grade 10 IPA','Grade 11 IPA','Grade 12 IPA'],
            'custom'     => ['Math Olympiad Team', 'Choir', 'Student Council', 'Robotics Club'],
            default      => [],
        };
    }

    public function getAudiencesPayload(): array
    {
        // Translate UI choices into Announcement::AUDIENCES tokens for downstream consumers.
        $out = [];
        if ($this->recipientTypes['family'] ?? false)      $out[] = 'all_parents';
        if ($this->recipientTypes['staff'] ?? false)       $out[] = 'staff';
        if ($this->recipientTypes['students'] ?? false)    $out[] = 'teachers'; // closest existing token (no "students" audience defined)
        if ($this->recipientTypes['all_members'] ?? false) $out[] = 'staff';
        // Add grade tokens that map to predefined audience keys
        foreach ($this->selectedGroups as $g) {
            $token = strtolower(str_replace([' ', '-'], '_', $g));
            if (array_key_exists($token, Announcement::AUDIENCES)) {
                $out[] = $token;
            }
        }
        return array_values(array_unique($out)) ?: ['staff'];
    }

    public function getChannelsPayload(): array
    {
        $out = [];
        foreach ($this->channels as $k => $v) {
            if ($v) $out[] = $k;
        }
        return $out ?: ['app'];
    }

    public function recipientCount(): int
    {
        // Rough demo estimate so the user sees a count update live.
        $base = 0;
        if ($this->recipientTypes['family'] ?? false)   $base += 480;
        if ($this->recipientTypes['students'] ?? false) $base += 1024;
        if ($this->recipientTypes['staff'] ?? false)    $base += 142;
        $base += count($this->selectedGroups) * 28;
        // Custom picks add weight per bucket
        $weights = ['classes' => 28, 'teachers' => 1, 'students' => 1, 'campus' => 320, 'unit' => 260, 'stream' => 90];
        foreach ($this->customPicked as $b => $arr) {
            $base += count($arr) * ($weights[$b] ?? 1);
        }
        return $base;
    }

    protected function persist(string $status): Announcement
    {
        $this->validate([
            'title_' => 'required|string|max:160',
            'body'   => 'required|string|min:2',
        ], [
            'title_.required' => 'Please add an announcement title.',
            'body.required'   => 'Please write your announcement.',
        ]);

        $payload = [
            'title'        => $this->title_,
            'body'         => $this->body,
            'author_name'  => auth()->user()?->name ?? 'Pak Dwi',
            'author_role'  => 'Principal',
            'audiences'    => $this->getAudiencesPayload(),
            'channels'     => $this->getChannelsPayload(),
            'category'     => $this->category,
            'pinned'       => $this->pinned,
            'status'       => $status,
            'scheduled_at' => $this->scheduledAt,
            'sent_at'      => $status === 'sent' ? now() : null,
            'recipient_count' => $this->recipientCount(),
        ];

        if ($this->record && $existing = Announcement::find($this->record)) {
            // Preserve original sent_at when re-saving a sent announcement as a draft edit.
            if ($status !== 'sent' && $existing->sent_at) {
                $payload['sent_at'] = $existing->sent_at;
            }
            $existing->update($payload);
            return $existing;
        }

        $created = Announcement::create($payload);
        $this->record = $created->id;
        return $created;
    }

    public function saveDraft()
    {
        $this->persist('draft');
        Notification::make()->title('Saved to drafts')->success()->send();
        return redirect(Announcements::getUrl(['folder' => 'drafts']));
    }

    public function publish()
    {
        $status = $this->scheduledAt ? 'scheduled' : 'sent';
        $this->persist($status);
        Notification::make()->title($status === 'scheduled' ? 'Scheduled' : 'Published')->success()->send();
        return redirect(Announcements::getUrl(['folder' => $status === 'scheduled' ? 'scheduled' : 'sent']));
    }
}
