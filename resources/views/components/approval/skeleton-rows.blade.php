@props(['count' => 4])
<div class="ap-list" aria-hidden="true">
    @for($i = 0; $i < $count; $i++)
        <div class="ap-skel-row">
            <div style="display:flex;flex-direction:column;gap:.5rem;min-width:0">
                <div style="display:flex;gap:.5rem;align-items:center">
                    <span class="ap-skel ap-skel-line" style="width:4.5rem"></span>
                    <span class="ap-skel ap-skel-line" style="width:{{ 9 + ($i * 2 % 5) }}rem"></span>
                    <span class="ap-skel ap-skel-line" style="width:6rem"></span>
                </div>
                <div style="display:flex;gap:.75rem;align-items:center">
                    <span class="ap-skel" style="width:8.5rem;height:1.2rem;border-radius:9999px"></span>
                    <span class="ap-skel ap-skel-line" style="width:7rem"></span>
                </div>
            </div>
            <div style="display:flex;gap:.75rem;align-items:center">
                <span class="ap-skel ap-skel-line" style="width:4rem"></span>
                <span class="ap-skel" style="width:1rem;height:1rem"></span>
            </div>
        </div>
    @endfor
</div>
