<script lang="ts">
  type Props = { pct: number; tone?: '' | 'green' | 'amber' | 'red' };
  let { pct, tone = '' }: Props = $props();
  const w = $derived(Math.max(0, Math.min(100, pct)));
</script>

<div class="bar {tone}"><i style="width:{w}%"></i></div>
