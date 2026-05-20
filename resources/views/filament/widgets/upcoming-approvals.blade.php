<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span style="display:inline-flex;align-items:center;gap:.5rem;">
                <span style="display:inline-flex;width:1.75rem;height:1.75rem;align-items:center;justify-content:center;border-radius:.5rem;background:rgba(245,158,11,.15);color:#d97706;font-weight:700;">⏰</span>
                Upcoming &amp; pending
            </span>
        </x-slot>
        <x-slot name="description">{{ count($items) }} items need attention</x-slot>

        @php
            $palette = [
                'rose'    => ['bg' => 'rgba(244,63,94,.15)',  'fg' => '#e11d48'],
                'emerald' => ['bg' => 'rgba(16,185,129,.15)', 'fg' => '#059669'],
                'amber'   => ['bg' => 'rgba(245,158,11,.15)', 'fg' => '#d97706'],
                'sky'     => ['bg' => 'rgba(14,165,233,.15)', 'fg' => '#0284c7'],
            ];
        @endphp

        <div style="display:flex;flex-direction:column;gap:.25rem;">
            @forelse ($items as $it)
                @php $c = $palette[$it['color']] ?? $palette['emerald']; @endphp
                <a href="{{ $it['url'] }}"
                   style="display:block;padding:.65rem;border-radius:.5rem;text-decoration:none;color:inherit;transition:background .15s;"
                   onmouseover="this.style.background='rgba(148,163,184,.08)'" onmouseout="this.style.background='transparent'">
                    <div style="display:flex;align-items:flex-start;gap:.75rem;">
                        <div style="display:flex;width:2.5rem;height:2.5rem;flex:0 0 auto;align-items:center;justify-content:center;border-radius:.6rem;background:{{ $c['bg'] }};color:{{ $c['fg'] }};font-size:1.1rem;">
                            {{ $it['icon'] }}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                                <p style="margin:0;font-weight:600;font-size:.825rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $it['title'] }}</p>
                                <span style="flex:0 0 auto;font-size:11px;font-weight:500;color:{{ $c['fg'] }};">{{ $it['when'] }}</span>
                            </div>
                            <p style="margin:.15rem 0 0;font-size:.75rem;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $it['sub'] }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <div style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">
                    Nothing pending. You are all caught up.
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
