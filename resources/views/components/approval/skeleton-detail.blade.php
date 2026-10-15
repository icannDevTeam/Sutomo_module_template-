<div class="ap-skel-detail" aria-hidden="true">
    <div style="padding:1.1rem 1.25rem .9rem;border-bottom:1px solid var(--ap-border);display:flex;flex-direction:column;gap:.6rem">
        <div style="display:flex;gap:.5rem"><span class="ap-skel ap-skel-line" style="width:5rem"></span><span class="ap-skel" style="width:14rem;height:1.2rem"></span></div>
        <span class="ap-skel ap-skel-line" style="width:18rem"></span>
        <div style="display:flex;gap:.75rem"><span class="ap-skel" style="width:10rem;height:1.6rem;border-radius:9999px"></span><span class="ap-skel ap-skel-line" style="width:9rem"></span></div>
    </div>
    <div style="display:grid;grid-auto-flow:column;gap:.5rem;padding:.9rem 1.25rem;background:var(--ap-surface-2);border-bottom:1px solid var(--ap-border)">
        @for($i = 0; $i < 8; $i++)<div style="display:flex;flex-direction:column;align-items:center;gap:.35rem"><span class="ap-skel" style="width:1.6rem;height:1.6rem;border-radius:50%"></span><span class="ap-skel ap-skel-line" style="width:3rem"></span></div>@endfor
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr">
        <div style="padding:1rem 1.25rem;display:flex;flex-direction:column;gap:.75rem">
            <span class="ap-skel ap-skel-line" style="width:6rem"></span>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">@for($i = 0; $i < 6; $i++)<div><span class="ap-skel ap-skel-line" style="width:4rem;display:block;margin-bottom:.3rem"></span><span class="ap-skel" style="width:6rem;height:.9rem;display:block"></span></div>@endfor</div>
            <span class="ap-skel" style="width:100%;height:3.5rem"></span>
        </div>
        <div style="padding:1rem 1.25rem;border-left:1px solid var(--ap-border);display:flex;flex-direction:column;gap:.75rem">
            <span class="ap-skel ap-skel-line" style="width:6rem"></span>
            @for($i = 0; $i < 4; $i++)<div style="display:flex;gap:.6rem"><span class="ap-skel" style="width:.6rem;height:.6rem;border-radius:50%;margin-top:.2rem"></span><div style="flex:1;display:flex;flex-direction:column;gap:.3rem"><span class="ap-skel ap-skel-line" style="width:{{ 55 + $i * 9 }}%"></span><span class="ap-skel ap-skel-line" style="width:40%"></span></div></div>@endfor
        </div>
    </div>
</div>
