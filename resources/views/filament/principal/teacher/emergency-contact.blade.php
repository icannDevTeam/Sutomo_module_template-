@php $r = $record; @endphp
<div class="sp-emerg">
    @if(! $r->emergency_contact_name && ! $r->emergency_contact_phone)
        <div class="sp-emerg__empty">No emergency contact on file.</div>
    @else
        <div class="sp-emerg__grid">
            <div>
                <div class="sp-emerg__label">Name</div>
                <div class="sp-emerg__value">{{ $r->emergency_contact_name ?: '—' }}</div>
            </div>
            <div>
                <div class="sp-emerg__label">Relation</div>
                <div class="sp-emerg__value">{{ $r->emergency_contact_relation ?: '—' }}</div>
            </div>
            <div>
                <div class="sp-emerg__label">Phone</div>
                <div class="sp-emerg__value">
                    @if($r->emergency_contact_phone)
                        <a href="tel:{{ $r->emergency_contact_phone }}">{{ $r->emergency_contact_phone }}</a>
                        ·
                        <a href="https://wa.me/{{ ltrim($r->emergency_contact_phone, '+') }}" target="_blank" rel="noopener" class="sp-emerg__wa">WhatsApp</a>
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
