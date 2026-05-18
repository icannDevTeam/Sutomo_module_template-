<script lang="ts">
  import { page } from '$app/state';
  import { findVacancy, fmtDate } from '$lib/data';
  import Pill from '$lib/components/Pill.svelte';
  import Icon from '$lib/components/Icon.svelte';

  const v = $derived(findVacancy(page.params.id));
</script>

{#if !v}
  <div class="card p-8 text-center">
    <h2 class="font-display font-bold">Vacancy not found</h2>
    <a class="btn btn-primary btn-sm mt-3" href="/careers">Browse vacancies</a>
  </div>
{:else}
  <a class="btn btn-ghost btn-sm mb-3" href="/careers">
    <Icon name="chevron-left" class="w-3.5 h-3.5" /> Vacancies
  </a>
  <div class="card p-6 mb-4">
    <div class="flex items-start justify-between gap-3 flex-wrap">
      <div>
        <h1 class="font-display font-bold text-2xl">{v.title}</h1>
        <div class="text-sm text-[color:var(--ink-mute)] mt-1">
          {v.dept} · {v.campus.toUpperCase()} · {v.type} · {v.level}
        </div>
      </div>
      <a class="btn btn-primary" href="/apply">Apply now <Icon name="arrow-right" class="w-3.5 h-3.5" /></a>
    </div>
    <p class="mt-4">{v.summary}</p>
    <div class="text-xs text-[color:var(--ink-faint)] mt-4">
      Posted {fmtDate(v.posted)} · Closes {fmtDate(v.closes)} · {v.applicants} applicants
    </div>
  </div>
{/if}
