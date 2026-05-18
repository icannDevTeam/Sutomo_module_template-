<script lang="ts">
  type Props = { pct: number; size?: number; tone?: string };
  let { pct, size = 64, tone = 'var(--blue)' }: Props = $props();

  const r = $derived(size / 2 - 6);
  const c = $derived(2 * Math.PI * r);
  const off = $derived(c * (1 - pct / 100));
</script>

<div class="ring" style="width:{size}px;height:{size}px">
  <svg width={size} height={size}>
    <circle cx={size/2} cy={size/2} r={r} stroke="#E2E8F0" stroke-width="6" fill="none" />
    <circle cx={size/2} cy={size/2} r={r} stroke={tone} stroke-width="6" fill="none"
      stroke-linecap="round" stroke-dasharray={c} stroke-dashoffset={off} />
  </svg>
  <div class="pct">{pct}%</div>
</div>
