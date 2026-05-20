<x-filament-panels::page>
    <form wire:submit.prevent="save" class="sp-info-form">
        {{ $this->form }}
        <div style="display:flex;justify-content:flex-end;gap:.5rem;margin-top:1rem;">
            <x-filament::button type="submit" icon="heroicon-m-check">Save changes</x-filament::button>
        </div>
    </form>

    {{-- Live preview --}}
    @php $d = $data; @endphp
    <div class="sp-card sp-info-preview" style="margin-top:var(--sp-gap);">
        <div class="sp-info-eyebrow">Live preview — public-facing copy</div>
        <h2 class="sp-info-h1">{{ $d['headline'] ?? '' }}</h2>
        <p class="sp-info-intro">{{ $d['intro'] ?? '' }}</p>

        <div class="sp-info-grid">
            <div>
                <h3 class="sp-info-h3">A. Syarat Pendaftaran</h3>
                <div class="sp-info-prose">{!! nl2br(e($d['requirements'] ?? '')) !!}</div>
            </div>
            <div>
                <h3 class="sp-info-h3">B. Dokumen Persyaratan</h3>
                <div class="sp-info-prose">{!! nl2br(e($d['documents'] ?? '')) !!}</div>
                @if (!empty($d['documents_note']))
                    <div class="sp-info-note">NB: {{ $d['documents_note'] }}</div>
                @endif
            </div>
        </div>

        <h3 class="sp-info-h3" style="margin-top:1.25rem;">Panduan Pendaftaran Online</h3>
        <div class="sp-info-prose">{!! nl2br(e($d['guide'] ?? '')) !!}</div>

        <div class="sp-info-contact">
            <div><b>Email</b><br>{{ $d['contact_email'] ?? '—' }}</div>
            <div><b>WhatsApp</b><br>{{ $d['contact_wa'] ?? '—' }}</div>
            <div><b>Phone</b><br>{{ $d['contact_phone'] ?? '—' }}</div>
            <div><b>Instagram</b><br>{{ $d['contact_ig'] ?? '—' }}</div>
            <div style="grid-column:1 / -1;"><b>Alamat</b><br>{{ $d['address'] ?? '—' }}</div>
        </div>
    </div>
</x-filament-panels::page>
