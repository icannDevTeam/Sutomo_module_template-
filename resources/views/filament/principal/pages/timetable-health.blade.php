<x-filament-panels::page>
@include('filament.principal.partials.planning-module-tabs', ['module' => 'health'])

<div class="sp-tth">

    {{-- Summary cards --}}
    <div class="sp-tth__summary">
        <div class="sp-tth__stat">
            <strong>{{ $summary['lessons'] }}</strong>
            <em>Lessons scheduled</em>
        </div>
        <div class="sp-tth__stat sp-tth__stat--error">
            <strong>{{ $summary['errors'] }}</strong>
            <em>Errors (must fix)</em>
        </div>
        <div class="sp-tth__stat sp-tth__stat--warn">
            <strong>{{ $summary['warns'] }}</strong>
            <em>Warnings</em>
        </div>
        <div class="sp-tth__stat sp-tth__stat--info">
            <strong>{{ $summary['info'] }}</strong>
            <em>Info</em>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="sp-tth__filters">
        <div class="sp-tth__filter-group">
            <span class="sp-tth__filter-lbl">Severity</span>
            @foreach (['all' => 'All', 'error' => 'Errors', 'warning' => 'Warnings', 'info' => 'Info'] as $key => $lbl)
                <button type="button" wire:click="setSeverity('{{ $key }}')"
                    @class(['sp-tth__pill', "is-{$key}", 'is-active' => $filterSeverity === $key])>
                    {{ $lbl }}
                </button>
            @endforeach
        </div>
        <div class="sp-tth__filter-group">
            <span class="sp-tth__filter-lbl">Category</span>
            <button type="button" wire:click="setCategory('all')"
                @class(['sp-tth__pill', 'is-active' => $filterCategory === 'all'])>All</button>
            @foreach ($categoryLabels as $cat => $lbl)
                @if (($byCategory[$cat] ?? 0) > 0)
                    <button type="button" wire:click="setCategory('{{ $cat }}')"
                        @class(['sp-tth__pill', 'is-active' => $filterCategory === $cat])>
                        {{ $lbl }}
                        <span class="sp-tth__pill-count">{{ $byCategory[$cat] }}</span>
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Issue list --}}
    <div class="sp-tth__list">
        @forelse ($issues as $i)
            @php
                $sev   = $i['severity'] ?? 'warning';
                $fix   = $i['fix'] ?? [];
                $query = http_build_query(array_filter([
                    'view'    => 'teacher',
                    'teacher' => $fix['teacher'] ?? null,
                    'session' => $fix['session'] ?? null,
                    'period'  => $fix['period'] ?? null,
                    'day'     => $fix['day'] ?? null,
                ]));
                $url   = \App\Filament\Principal\Pages\TimetableBuilder::getUrl() . ($query ? '?' . $query : '');
            @endphp
            <div @class(['sp-tth__row', "is-{$sev}"])>
                <div class="sp-tth__row-sev">
                    @if ($sev === 'error')
                        <x-filament::icon icon="heroicon-m-exclamation-circle" class="w-5 h-5" />
                    @elseif ($sev === 'warning')
                        <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-5 h-5" />
                    @else
                        <x-filament::icon icon="heroicon-m-information-circle" class="w-5 h-5" />
                    @endif
                </div>
                <div class="sp-tth__row-body">
                    <strong>{{ $i['title'] }}</strong>
                    <em>{{ $i['detail'] }}</em>
                </div>
                <div class="sp-tth__row-cat">
                    {{ $categoryLabels[$i['category']] ?? $i['category'] }}
                </div>
                <a href="{{ $url }}" class="sp-tth__fix-btn">
                    Fix
                    <x-filament::icon icon="heroicon-m-arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>
        @empty
            <div class="sp-tth__empty">
                <x-filament::icon icon="heroicon-o-check-badge" class="w-10 h-10" />
                <strong>All clear in this view.</strong>
                <em>No issues match the current filters.</em>
            </div>
        @endforelse
    </div>
</div>
</x-filament-panels::page>
