@php
    $modTabs = [
        [
            'id'    => 'units',
            'label' => 'Units & Topics',
            'desc'  => 'Subject planning, lessons, objectives',
            'icon'  => 'heroicon-o-puzzle-piece',
            'url'   => \App\Filament\Principal\Pages\UnitPlanning::getUrl(),
            'class' => \App\Filament\Principal\Pages\UnitPlanning::class,
        ],
        [
            'id'    => 'timetable',
            'label' => 'Timetable',
            'desc'  => 'Class distribution, periods, rooms',
            'icon'  => 'heroicon-o-clock',
            'url'   => \App\Filament\Principal\Pages\TimetableBuilder::getUrl(),
            'class' => \App\Filament\Principal\Pages\TimetableBuilder::class,
        ],
        [
            'id'    => 'health',
            'label' => 'Health',
            'desc'  => 'Conflicts, deficits, load balance',
            'icon'  => 'heroicon-o-shield-check',
            'url'   => \App\Filament\Principal\Pages\TimetableHealth::getUrl(),
            'class' => \App\Filament\Principal\Pages\TimetableHealth::class,
        ],
    ];
    $current = $module ?? 'units';
@endphp

<div class="sp-planmod">
    <div class="sp-planmod__crumbs">
        <span class="sp-planmod__crumbs-pill">
            <x-filament::icon icon="heroicon-m-rectangle-group" class="w-3.5 h-3.5" />
            Planning module
        </span>
        <span class="sp-planmod__crumbs-sep">/</span>
        <span class="sp-planmod__crumbs-now">{{ collect($modTabs)->firstWhere('id', $current)['label'] ?? '' }}</span>
    </div>
    <nav class="sp-planmod__tabs" role="tablist" aria-label="Planning module">
        @foreach ($modTabs as $t)
            <a href="{{ $t['url'] }}"
               class="sp-planmod__tab {{ $current === $t['id'] ? 'is-active' : '' }}"
               role="tab"
               aria-selected="{{ $current === $t['id'] ? 'true' : 'false' }}">
                <span class="sp-planmod__tab-icon">
                    <x-filament::icon :icon="$t['icon']" class="w-4 h-4" />
                </span>
                <span class="sp-planmod__tab-body">
                    <strong>{{ $t['label'] }}</strong>
                    <em>{{ $t['desc'] }}</em>
                </span>
            </a>
        @endforeach
    </nav>
</div>
