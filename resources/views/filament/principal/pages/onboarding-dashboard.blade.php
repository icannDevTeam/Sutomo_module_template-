<x-filament-panels::page>
<div style="background:white;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.25rem;">
    <div style="font-weight:600;margin-bottom:1rem;">Onboarding funnel — accepted → activated</div>
    @php $max = max(1, max(array_column($steps, 'count'))); @endphp
    @foreach ($steps as $k => $s)
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.55rem;">
            <div style="width:160px;font-size:.85rem;color:#334155;">{{ $s['label'] }}</div>
            <div style="flex:1;background:#f1f5f9;border-radius:.5rem;height:1.2rem;overflow:hidden;">
                <div style="height:100%;background:linear-gradient(90deg,#4f46e5,#7c3aed);width:{{ ($s['count']/$max)*100 }}%;border-radius:.5rem;transition:width .4s;"></div>
            </div>
            <div style="width:50px;text-align:right;font-weight:700;color:#4f46e5;">{{ $s['count'] }}</div>
        </div>
    @endforeach
</div>
</x-filament-panels::page>
