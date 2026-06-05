<?php

namespace App\Filament\Principal\Pages;

use App\Models\ObservationCriterion;
use App\Models\ObservationSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ObservationConfig extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Supervision';
    protected static ?string $navigationLabel = 'Observation Criteria';
    protected static ?string $title = 'Observation Configuration';
    protected static ?int $navigationSort = 90;
    protected static string $view = 'filament.principal.pages.observation-config';

    public string $activeTab = 'teacher-observation';

    public array $criteria = [];

    // Probation Watch settings (Phase 3)
    public bool $probationWatchEnabled = true;
    public int $probationMinSupervisions = 4;
    public int $probationMinPeerObservations = 2;
    public int $probationDecisionDueDays = 14;

    public function mount(): void
    {
        try {
            $this->criteria = ObservationCriterion::query()
                ->orderBy('order')
                ->get()
                ->map(fn ($row) => [
                    'id'          => $row->id,
                    'key'         => $row->key,
                    'label'       => $row->label,
                    'description' => $row->description,
                    'weight'      => (int) $row->weight,
                    'order'       => (int) $row->order,
                    'active'      => (bool) $row->active,
                ])->all();
        } catch (\Throwable $e) {
            $this->criteria = [];
        }

        $settings = ObservationSetting::current();
        $this->probationWatchEnabled         = (bool) $settings->probation_watch_enabled;
        $this->probationMinSupervisions      = (int) $settings->probation_min_supervisions;
        $this->probationMinPeerObservations  = (int) $settings->probation_min_peer_observations;
        $this->probationDecisionDueDays      = (int) $settings->probation_decision_due_days;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['teacher-observation', 'probation-watch'], true)
            ? $tab
            : 'teacher-observation';
    }

    public function addRow(): void
    {
        $this->criteria[] = [
            'id'          => null,
            'key'         => 'criterion_' . (count($this->criteria) + 1),
            'label'       => 'New Criterion',
            'description' => null,
            'weight'      => 5,
            'order'       => count($this->criteria) + 1,
            'active'      => true,
        ];
    }

    public function removeRow(int $idx): void
    {
        if (isset($this->criteria[$idx])) {
            array_splice($this->criteria, $idx, 1);
            $this->reindexOrder();
        }
    }

    public function moveUp(int $idx): void
    {
        if ($idx <= 0 || ! isset($this->criteria[$idx])) {
            return;
        }
        [$this->criteria[$idx - 1], $this->criteria[$idx]] = [$this->criteria[$idx], $this->criteria[$idx - 1]];
        $this->reindexOrder();
    }

    public function moveDown(int $idx): void
    {
        if (! isset($this->criteria[$idx + 1])) {
            return;
        }
        [$this->criteria[$idx], $this->criteria[$idx + 1]] = [$this->criteria[$idx + 1], $this->criteria[$idx]];
        $this->reindexOrder();
    }

    protected function reindexOrder(): void
    {
        $this->criteria = array_values($this->criteria);
        foreach ($this->criteria as $i => $row) {
            $this->criteria[$i]['order'] = $i + 1;
        }
    }

    public function save(): void
    {
        $this->reindexOrder();
        $keptIds = [];

        foreach ($this->criteria as $row) {
            $key = trim((string) ($row['key'] ?? ''));
            $label = trim((string) ($row['label'] ?? ''));
            if ($key === '' || $label === '') {
                continue;
            }

            $payload = [
                'key'         => $key,
                'label'       => $label,
                'description' => $row['description'] ?? null,
                'weight'      => (int) ($row['weight'] ?? 5),
                'order'       => (int) ($row['order'] ?? 0),
                'active'      => (bool) ($row['active'] ?? true),
            ];

            $model = ! empty($row['id'])
                ? ObservationCriterion::find($row['id'])
                : null;

            if ($model) {
                $model->fill($payload)->save();
            } else {
                $model = ObservationCriterion::updateOrCreate(['key' => $key], $payload);
            }
            $keptIds[] = $model->id;
        }

        // Orphan guard: never hard-delete a criterion key that's referenced
        // by an existing TeacherObservation.dimensions JSON. Deactivate instead.
        $usedKeys = [];
        try {
            $usedKeys = \App\Models\TeacherObservation::query()
                ->whereNotNull('dimensions')
                ->pluck('dimensions')
                ->flatMap(fn ($d) => is_array($d) ? array_keys($d) : [])
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $e) {
            $usedKeys = [];
        }

        $toRemove = ObservationCriterion::query()->whereNotIn('id', $keptIds)->get();
        foreach ($toRemove as $c) {
            if (in_array($c->key, $usedKeys, true)) {
                $c->forceFill(['active' => false])->save();
            } else {
                $c->delete();
            }
        }

        // Save probation settings (Phase 3) BEFORE re-mounting (mount reloads from DB).
        $settings = ObservationSetting::current();
        if (! $settings->exists) {
            $settings = new ObservationSetting(['id' => 1]);
        }
        $settings->fill([
            'probation_watch_enabled'         => (bool) $this->probationWatchEnabled,
            'probation_min_supervisions'      => max(0, min(50, (int) $this->probationMinSupervisions)),
            'probation_min_peer_observations' => max(0, min(50, (int) $this->probationMinPeerObservations)),
            'probation_decision_due_days'     => max(1, min(90, (int) $this->probationDecisionDueDays)),
        ])->save();

        $this->mount();

        Notification::make()->title('Observation configuration saved')->success()->send();
    }
}
