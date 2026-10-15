@props(['presenter'])
@php $steps = $presenter->stageTrack(); @endphp
<ol {{ $attributes->merge(['class' => 'ap-track']) }} aria-label="Progress">
    @foreach($steps as $i => $s)
        <li class="ap-track__step is-{{ $s['state'] }}" aria-current="{{ $s['state'] === 'current' ? 'step' : 'false' }}">
            @if($s['rounds'] > 1)
                <span class="ap-track__round" title="{{ $s['rounds'] }} correction rounds">×{{ $s['rounds'] }}</span>
            @endif
            <span class="ap-track__dot" aria-hidden="true">
                @if($s['state'] === 'done')
                    <x-filament::icon icon="heroicon-m-check" />
                @elseif($s['state'] === 'failed')
                    <x-filament::icon icon="heroicon-m-x-mark" />
                @else
                    {{ $i + 1 }}
                @endif
            </span>
            <span class="ap-track__label">
                {{ $s['label'] }}
                <span class="ap-visually-hidden">
                    @switch($s['state'])
                        @case('done') completed @break
                        @case('current') current step @break
                        @case('failed') stopped here @break
                        @default not started
                    @endswitch
                </span>
            </span>
        </li>
    @endforeach
</ol>
