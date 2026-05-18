<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span style="display:inline-flex;align-items:center;gap:.5rem;">
                <span style="display:inline-flex;width:1.75rem;height:1.75rem;align-items:center;justify-content:center;border-radius:.5rem;background:rgba(16,185,129,.15);color:#059669;font-weight:700;">✓</span>
                Recently approved
            </span>
        </x-slot>
        <x-slot name="description">Latest decisions across the pipeline</x-slot>

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
                <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.65rem;border-radius:.5rem;transition:background .15s;"
                     onmouseover="this.style.background='rgba(148,163,184,.08)'" onmouseout="this.style.background='transparent'">
                    <div style="display:flex;width:2.25rem;height:2.25rem;flex:0 0 auto;align-items:center;justify-content:center;border-radius:9999px;background:{{ $c['bg'] }};color:{{ $c['fg'] }};font-weight:700;">
                        {{ $it['icon'] }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                            <p style="margin:0;font-weight:600;font-size:.825rem;text-transform:capitalize;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $it['title'] }}</p>
                            <span style="flex:0 0 auto;font-size:11px;color:#94a3b8;">{{ $it['when'] }}</span>
                        </div>
                        <p style="margin:.1rem 0 0;font-size:.75rem;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $it['sub'] }}</p>
                        <p style="margin:.15rem 0 0;font-size:11px;color:#94a3b8;">by {{ $it['who'] }}</p>
                    </div>
                </div>
            @empty
                <div style="padding:2rem;text-align:center;font-size:.875rem;color:#94a3b8;">No approvals yet.</div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
