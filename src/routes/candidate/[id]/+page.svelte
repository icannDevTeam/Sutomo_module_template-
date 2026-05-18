<script lang="ts">
  import { page } from '$app/state';
  import { findCandidate, findVacancy, fmtDate, stageLabel } from '$lib/data';
  import Avatar from '$lib/components/Avatar.svelte';
  import Pill from '$lib/components/Pill.svelte';
  import Icon from '$lib/components/Icon.svelte';

  const c = $derived(findCandidate(page.params.id) as any);
  const v = $derived(c ? findVacancy(c.vacancyId) : null);
</script>

{#if !c}
  <div class="card p-8 text-center">
    <h2 class="font-display font-bold">Candidate not found</h2>
    <a class="btn btn-primary btn-sm mt-3" href="/recruitment">Back to pipeline</a>
  </div>
{:else}
  <a class="btn btn-ghost btn-sm mb-3" href="/recruitment">
    <Icon name="chevron-left" class="w-3.5 h-3.5" /> Pipeline
  </a>
  <div class="card p-6 mb-4 flex items-start gap-4 flex-wrap">
    <Avatar name={c.name} size="xl" />
    <div class="flex-1 min-w-0">
      <h1 class="font-display font-bold text-2xl">{c.name}</h1>
      <div class="text-sm text-[color:var(--ink-mute)]">{v?.title ?? '—'} · {c.education}</div>
      <div class="flex flex-wrap gap-1 mt-2">
        <Pill tone="blue" dot>{stageLabel(c.stage)}</Pill>
        {#if c.priority === 'high'}<Pill tone="red">High priority</Pill>{/if}
      </div>
    </div>
  </div>
  <div class="grid md:grid-cols-3 gap-3">
    <div class="card p-5">
      <h3 class="font-display font-semibold mb-2">Contact</h3>
      <div class="text-sm">{c.email ?? '—'}<br>{c.phone ?? '—'}<br>{c.city ?? '—'}</div>
    </div>
    <div class="card p-5">
      <h3 class="font-display font-semibold mb-2">Application</h3>
      <div class="text-sm">
        Applied {fmtDate(c.appliedAt)}<br>
        Years exp: {c.years}<br>
        Subjects: {(c.subjects ?? []).join(', ')}
      </div>
    </div>
    <div class="card p-5">
      <h3 class="font-display font-semibold mb-2">Scores</h3>
      <div class="text-sm space-y-1">
        <div>Written: <b>{c.score?.written ?? '—'}</b></div>
        <div>Interview: <b>{c.score?.interview ?? '—'}</b></div>
        <div>Micro-teach: <b>{c.score?.micro ?? '—'}</b></div>
      </div>
    </div>
  </div>
{/if}
