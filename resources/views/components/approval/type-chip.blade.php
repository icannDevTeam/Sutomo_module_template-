@props(['type'])
@php
    [$cls, $label] = match ($type) {
        'pkwt_renewal'        => ['ap-chip--pkwt', 'PKWT renewal'],
        'annual_continuation' => ['ap-chip--loi', 'Continuation'],
        'hiring'              => ['ap-chip--hiring', 'Hiring'],
        default               => ['', ucfirst(str_replace('_', ' ', (string) $type))],
    };
@endphp
<span {{ $attributes->merge(['class' => 'ap-chip ' . $cls]) }}>{{ $label }}</span>
