@props(['icon' => 'heroicon-o-inbox', 'title', 'body' => null])
<div {{ $attributes->merge(['class' => 'ap-empty']) }} role="status">
    <div class="ap-empty__icon" aria-hidden="true"><x-filament::icon :icon="$icon" /></div>
    <div class="ap-empty__title">{{ $title }}</div>
    @if($body)<div class="ap-empty__body">{{ $body }}</div>@endif
    @if(trim($slot))<div class="ap-empty__cta">{{ $slot }}</div>@endif
</div>
