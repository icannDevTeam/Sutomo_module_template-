<x-filament-panels::page>
<form wire:submit="send">
    <div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
        {{ $this->form }}
        <div style="margin-top:1rem;display:flex;gap:.5rem;">
            <button type="submit" style="background:#4f46e5;color:white;padding:.55rem 1rem;border-radius:.5rem;border:none;font-weight:600;cursor:pointer;">Send Announcement</button>
        </div>
    </div>
</form>
</x-filament-panels::page>
