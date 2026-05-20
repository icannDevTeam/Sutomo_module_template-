<x-filament-widgets::widget>
    <x-filament::section>
        <div class="sp-enrollment-status-card">
            <div class="sp-est-header">
                <div class="sp-est-icon">
                    @if($status === 'open')
                        <x-heroicon-o-megaphone class="w-6 h-6 text-success-500" />
                    @else
                        <x-heroicon-o-lock-closed class="w-6 h-6 text-gray-400" />
                    @endif
                </div>
                <div class="sp-est-content">
                    <div class="sp-est-status">
                        @if($status === 'open')
                            <span class="sp-est-badge sp-est-badge--open">Open</span>
                        @else
                            <span class="sp-est-badge sp-est-badge--closed">Closed</span>
                        @endif
                    </div>
                    <h3 class="sp-est-title">{{ $name }}</h3>
                </div>
            </div>
            <a href="{{ \App\Filament\Principal\Pages\OpenEnrollment::getUrl() }}" 
               class="sp-est-link">
                Manage Enrollment
                <x-heroicon-o-arrow-right class="w-4 h-4" />
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
