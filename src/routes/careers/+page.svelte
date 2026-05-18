<script lang="ts">
  import { VACANCIES, fmtDate } from '$lib/data';
  import Pill from '$lib/components/Pill.svelte';
  import Icon from '$lib/components/Icon.svelte';

  const open = VACANCIES.filter(v => v.status !== 'closed');
</script>

<div class="public-nav">
  <div class="inner">
    <a href="/careers" class="brand">
      <div class="logo"><Icon name="graduation-cap" class="w-4 h-4" /></div>
      Sutomo Careers
    </a>
    <nav class="links">
      <a href="/careers">Vacancies</a>
      <a href="/apply">Apply</a>
      <a href="/tracker">Track</a>
    </nav>
  </div>
</div>

<section class="hero">
  <div class="inner">
    <h1 class="font-display font-bold text-3xl md:text-4xl">Teach where every story matters.</h1>
    <p class="text-base md:text-lg opacity-90 mt-3 max-w-2xl">
      Join Sutomo School — a 70-year-old Medan institution shaping the next generation of leaders.
    </p>
    <div class="mt-5 flex gap-2">
      <a class="btn btn-primary" href="/apply">Apply now <Icon name="arrow-right" class="w-3.5 h-3.5" /></a>
      <a class="btn btn-ghost" href="/tracker">Track application</a>
    </div>
  </div>
</section>

<section class="max-w-6xl mx-auto px-6 py-8">
  <div class="flex items-end justify-between mb-4">
    <h2 class="font-display font-bold text-xl">Open positions</h2>
    <span class="text-sm text-[color:var(--ink-mute)]">{open.length} vacancies</span>
  </div>
  <div class="grid md:grid-cols-2 gap-3">
    {#each open as v}
      <a class="vac-card" href={`/vacancy/${v.id}`}>
        <div class="flex items-start justify-between gap-3">
          <div>
            <h3 class="font-display font-semibold text-base">{v.title}</h3>
            <div class="text-xs text-[color:var(--ink-mute)] mt-0.5">{v.dept} · {v.campus.toUpperCase()} · {v.type}</div>
          </div>
          {#if v.featured}<Pill tone="gold">Featured</Pill>{/if}
          {#if v.status === 'closing'}<Pill tone="red">Closing soon</Pill>{/if}
        </div>
        <p class="text-sm mt-2 text-[color:var(--ink-mute)] line-clamp-2">{v.summary}</p>
        <div class="text-xs text-[color:var(--ink-faint)] mt-3">
          Closes {fmtDate(v.closes)} · {v.applicants} applicants
        </div>
      </a>
    {/each}
  </div>
</section>
