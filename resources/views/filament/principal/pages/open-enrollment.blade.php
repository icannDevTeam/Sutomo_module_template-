<x-filament-panels::page>
    {{-- Previous Enrollment Periods --}}
    @if($periods->count() > 0)
        <x-filament::section class="mt-6" icon="heroicon-o-calendar-days" heading="Enrollment Periods">
            <div class="sp-enrollment-periods-list">
                @foreach($periods as $period)
                    <div class="sp-period-card">
                        <div class="sp-period-header">
                            <div class="sp-period-info">
                                <h3 class="sp-period-name">{{ $period->name }}</h3>
                                <div class="sp-period-meta">
                                    <span class="sp-period-campus">{{ $period->campus }}</span>
                                    <span class="sp-period-separator">•</span>
                                    <span class="sp-period-unit">{{ $period->unit }}</span>
                                    <span class="sp-period-separator">•</span>
                                    <span class="sp-period-level">{{ ucfirst($period->school_level) }}</span>
                                </div>
                            </div>
                            <div class="sp-period-status">
                                @php
                                    $statusClass = [
                                        'draft' => 'sp-status--draft',
                                        'open' => 'sp-status--open',
                                        'closed' => 'sp-status--closed',
                                    ][$period->status] ?? 'sp-status--draft';
                                    $statusLabel = \App\Models\EnrollmentPeriod::STATUSES[$period->status] ?? 'Draft';
                                @endphp
                                <span class="sp-period-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                        </div>
                        
                        <div class="sp-period-dates">
                            <div class="sp-period-date-item">
                                <x-heroicon-o-calendar class="w-4 h-4 text-gray-400" />
                                <span>Opens: {{ $period->opens_at?->format('M d, Y') ?? '—' }}</span>
                            </div>
                            <div class="sp-period-date-item">
                                <x-heroicon-o-calendar class="w-4 h-4 text-gray-400" />
                                <span>Closes: {{ $period->closes_at?->format('M d, Y') ?? '—' }}</span>
                            </div>
                            @if($period->exam_starts_at)
                                <div class="sp-period-date-item">
                                    <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                    <span>Exam: {{ $period->exam_starts_at->format('M d, Y H:i') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="sp-period-stats">
                            <div class="sp-period-stat">
                                <span class="sp-period-stat-label">Applications</span>
                                <span class="sp-period-stat-value">{{ $period->applications()->count() }}</span>
                            </div>
                            <div class="sp-period-stat">
                                <span class="sp-period-stat-label">Quota</span>
                                <span class="sp-period-stat-value">{{ $period->quota }}</span>
                            </div>
                            @if($period->pass_threshold)
                                <div class="sp-period-stat">
                                    <span class="sp-period-stat-label">Pass Score</span>
                                    <span class="sp-period-stat-value">{{ $period->pass_threshold }}</span>
                                </div>
                            @endif
                            <div class="sp-period-stat">
                                <span class="sp-period-stat-label">App Fee</span>
                                <span class="sp-period-stat-value">Rp {{ number_format($period->application_fee ?? 300000, 0, ',', '.') }}</span>
                            </div>
                            <div class="sp-period-stat">
                                <span class="sp-period-stat-label">VA Expiry</span>
                                <span class="sp-period-stat-value">{{ $period->payment_expiry_hours ?? 24 }}h</span>
                            </div>
                        </div>

                        <div class="sp-period-actions">
                            @if($period->status !== 'open')
                                {{ ($this->openPeriodAction)(['period' => $period->id]) }}
                            @endif
                            
                            @if($period->status === 'open')
                                {{ ($this->closePeriodAction)(['period' => $period->id]) }}
                            @endif
                            
                            {{ ($this->editPeriodAction)(['period' => $period->id]) }}
                            {{ ($this->deletePeriodAction)(['period' => $period->id]) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @else
        <x-filament::section class="mt-6">
            <div class="text-center py-12">
                <x-heroicon-o-calendar-days class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                <h3 class="text-lg font-semibold text-gray-700 mb-2">No enrollment periods yet</h3>
                <p class="text-sm text-gray-500 mb-4">Create your first enrollment period to get started.</p>
            </div>
        </x-filament::section>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
