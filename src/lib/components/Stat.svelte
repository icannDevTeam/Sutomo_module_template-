<script lang="ts">
  import Icon from './Icon.svelte';

  type Tone = 'gray'|'brown'|'gold'|'green'|'amber'|'red'|'blue'|'maroon';
  type Props = {
    label: string;
    value: string | number;
    sub?: string;
    icon?: string;
    tone?: Tone;
    delta?: number;
  };
  let { label, value, sub, icon = 'circle', tone = 'gray', delta }: Props = $props();

  const tones: Record<Tone, string> = {
    gray:  'bg-slate-100 text-slate-600',
    brown: 'bg-[color:var(--cream)] text-[color:var(--brand-dark)]',
    gold:  'bg-[#FEF6DC] text-[#92750E]',
    green: 'bg-emerald-50 text-emerald-700',
    amber: 'bg-amber-50 text-amber-700',
    red:   'bg-red-50 text-red-700',
    blue:  'bg-blue-50 text-blue-700',
    maroon:'bg-pink-50 text-[color:var(--maroon)]',
  };
</script>

<div class="card p-4">
  <div class="flex items-start gap-3">
    <div class="w-9 h-9 rounded-lg flex items-center justify-center {tones[tone]}">
      <Icon name={icon} class="w-4 h-4" />
    </div>
    <div class="flex-1 min-w-0">
      <div class="text-[11px] uppercase tracking-wider text-[color:var(--ink-faint)] font-semibold">{label}</div>
      <div class="text-2xl font-display font-bold text-[color:var(--ink)] mt-0.5 leading-none">{value}</div>
      {#if sub}<div class="text-xs text-[color:var(--ink-mute)] mt-1">{sub}</div>{/if}
    </div>
    {#if delta != null}
      <div class="text-[11px] font-semibold {delta >= 0 ? 'text-emerald-600' : 'text-red-600'}">
        {delta >= 0 ? '+' : ''}{delta}%
      </div>
    {/if}
  </div>
</div>
