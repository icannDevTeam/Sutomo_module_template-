<script lang="ts">
  import { CANDIDATES, STAGES, findVacancy, stageLabel } from '$lib/data';
  import Pill from '$lib/components/Pill.svelte';
  import Avatar from '$lib/components/Avatar.svelte';
  import Icon from '$lib/components/Icon.svelte';

  const cols = STAGES.filter(s => s.id !== 'rejected' && s.id !== 'active');
  const tonePerStage: Record<string, any> = {
    applied: 'gray', screening: 'blue', written: 'blue', interview: 'gold',
    psycho: 'violet', medical: 'green', yayasan: 'maroon', opl: 'brown',
  };

  function byStage(id: string) {
    return CANDIDATES.filter((c: any) => c.stage === id);
  }
</script>

<div class="flex items-end justify-between flex-wrap gap-3 mb-5">
  <div>
    <h1 class="font-display font-bold text-2xl">Recruitment Pipeline</h1>
    <p class="text-sm text-[color:var(--ink-mute)]">Drag-to-move (UI only). All stages across all vacancies.</p>
  </div>
  <div class="flex gap-2">
    <button class="btn btn-ghost btn-sm"><Icon name="filter" class="w-3.5 h-3.5" /> Filter</button>
    <button class="btn btn-primary btn-sm"><Icon name="plus" class="w-3.5 h-3.5" /> Add candidate</button>
  </div>
</div>

<div class="kanban">
  {#each cols as col}
    {@const items = byStage(col.id)}
    <div class="kcol">
      <header>
        <span class="lbl">{col.label}</span>
        <span class="cnt">{items.length}</span>
      </header>
      <div class="kbody">
        {#each items as c}
          {@const v = findVacancy(c.vacancyId)}
          <a class="kcard" href={`/candidate/${c.id}`}>
            <div class="flex items-center gap-2 mb-2">
              <Avatar name={c.name} size="sm" />
              <div class="flex-1 min-w-0">
                <div class="font-semibold text-sm truncate">{c.name}</div>
                <div class="text-[11px] text-[color:var(--ink-mute)] truncate">{v?.title ?? '—'}</div>
              </div>
            </div>
            <div class="flex items-center gap-1 flex-wrap">
              <Pill tone={tonePerStage[col.id] ?? 'gray'}>{stageLabel(col.id)}</Pill>
              {#if c.priority === 'high'}<Pill tone="red">High</Pill>{/if}
              {#if c.score?.written}<Pill tone="green">W {c.score.written}</Pill>{/if}
            </div>
          </a>
        {:else}
          <div class="text-xs text-[color:var(--ink-faint)] p-2">Empty</div>
        {/each}
      </div>
    </div>
  {/each}
</div>
