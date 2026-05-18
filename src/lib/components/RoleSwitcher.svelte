<script lang="ts">
  import { state, setRole } from '$lib/state.svelte';
  import { ROLES } from '$lib/data';
  import Icon from './Icon.svelte';
</script>

<div class="card p-1 inline-flex items-center gap-1 flex-wrap">
  <div class="text-[11px] uppercase tracking-wider font-bold text-[color:var(--ink-faint)] px-2">
    View as
  </div>
  {#each ROLES as r}
    <button
      type="button"
      class="btn btn-sm"
      class:btn-primary={state.role === r.id}
      class:btn-ghost={state.role !== r.id}
      onclick={() => setRole(r.id as any)}
    >
      <Icon name={r.icon} class="w-3 h-3" />
      {r.label}
    </button>
  {/each}
</div>
