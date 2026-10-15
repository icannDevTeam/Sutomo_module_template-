@props(['from' => null, 'to' => null])
@php
    $label = fn (?string $t) => match ($t) {
        'pkwt_1'  => 'PKWT-I', 'pkwt_2' => 'PKWT-II', 'pkwt_3' => 'PKWT-III', 'guru_sk' => 'Guru SK',
        null      => null,
        default   => strtoupper(str_replace('_', '-', $t)),
    };
@endphp
@if($from || $to)
<span {{ $attributes->merge(['class' => 'ap-tier']) }}>
    @if($from)<span>{{ $label($from) }}</span>@endif
    @if($from && $to)<x-filament::icon icon="heroicon-m-arrow-long-right" aria-hidden="true" /><span class="ap-visually-hidden">to</span>@endif
    @if($to)<span>{{ $label($to) }}</span>@endif
</span>
@endif
