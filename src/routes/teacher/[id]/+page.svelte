<script lang="ts">
  import { page } from '$app/state';
  import { findTeacher, fmtDate } from '$lib/data';
  import Avatar from '$lib/components/Avatar.svelte';
  import Pill from '$lib/components/Pill.svelte';
  import Icon from '$lib/components/Icon.svelte';

  const t = $derived(findTeacher(page.params.id));
</script>

{#if !t}
  <div class="card p-8 text-center">
    <h2 class="font-display font-bold text-lg">Teacher not found</h2>
    <a class="btn btn-primary btn-sm mt-3" href="/master">Back to Master DB</a>
  </div>
{:else}
  <a class="btn btn-ghost btn-sm mb-3" href="/master">
    <Icon name="chevron-left" class="w-3.5 h-3.5" /> Master Database
  </a>

  <div class="card p-6 mb-4 flex items-start gap-4 flex-wrap">
    <Avatar name={t.name} size="xl" />
    <div class="flex-1 min-w-0">
      <div class="flex items-center gap-2 flex-wrap">
        <h1 class="font-display font-bold text-2xl">{t.name}</h1>
        <Pill tone="blue" dot>{t.status}</Pill>
      </div>
      <p class="text-sm text-[color:var(--ink-mute)]">{t.subject} · {t.dept} · {t.campus.toUpperCase()}</p>
      <p class="text-xs text-[color:var(--ink-faint)] mt-1 font-mono">{t.id} · {t.employeeNo}</p>
    </div>
    <div class="flex gap-2">
      <button class="btn btn-ghost btn-sm"><Icon name="mail" class="w-3.5 h-3.5" /> Email</button>
      <button class="btn btn-primary btn-sm"><Icon name="edit-3" class="w-3.5 h-3.5" /> Edit</button>
    </div>
  </div>

  <div class="grid md:grid-cols-3 gap-3">
    <div class="card p-5">
      <h3 class="font-display font-semibold mb-2">Contact</h3>
      <dl class="text-sm space-y-1.5">
        <div><dt class="text-[color:var(--ink-faint)] text-xs">Email</dt><dd>{t.email}</dd></div>
        <div><dt class="text-[color:var(--ink-faint)] text-xs">Phone</dt><dd>{t.phone}</dd></div>
        <div><dt class="text-[color:var(--ink-faint)] text-xs">City</dt><dd>{t.city}</dd></div>
      </dl>
    </div>
    <div class="card p-5">
      <h3 class="font-display font-semibold mb-2">Employment</h3>
      <dl class="text-sm space-y-1.5">
        <div><dt class="text-[color:var(--ink-faint)] text-xs">Contract</dt><dd>{t.contract}</dd></div>
        <div><dt class="text-[color:var(--ink-faint)] text-xs">Joined</dt><dd>{fmtDate(t.joined)} ({t.tenure})</dd></div>
        <div><dt class="text-[color:var(--ink-faint)] text-xs">Ends</dt><dd>{t.contractEnd ? fmtDate(t.contractEnd) : '—'}</dd></div>
        <div><dt class="text-[color:var(--ink-faint)] text-xs">Last review</dt><dd>{fmtDate(t.lastReview)} · ★ {t.rating ?? '—'}</dd></div>
      </dl>
    </div>
    <div class="card p-5">
      <h3 class="font-display font-semibold mb-2">Qualifications</h3>
      <div class="text-sm">{t.education}</div>
      <div class="mt-2 flex flex-wrap gap-1">
        {#each t.certifications as c}<Pill tone="gray">{c}</Pill>{/each}
      </div>
      <div class="mt-2 flex flex-wrap gap-1">
        {#each t.languages as l}<Pill tone="blue">{l}</Pill>{/each}
      </div>
    </div>
  </div>
{/if}
