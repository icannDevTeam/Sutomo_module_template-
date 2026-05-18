<script lang="ts">
  import '../app.css';
  import Sidebar from '$lib/components/Sidebar.svelte';
  import Topbar from '$lib/components/Topbar.svelte';
  import { state } from '$lib/state.svelte';
  import { ROLES, USERS } from '$lib/data';
  import Icon from '$lib/components/Icon.svelte';

  let { children } = $props();

  const isPublic = $derived(state.role === 'applicant');
  const role = $derived(ROLES.find(r => r.id === state.role)!);
</script>

{#if isPublic}
  <main class="min-h-screen">
    {@render children?.()}
  </main>
{:else}
  <div class="app-shell">
    <Sidebar />
    <div class="min-h-screen flex flex-col">
      {#if state.role !== 'hr'}
        <div class="role-banner">
          <Icon name="eye" class="w-3.5 h-3.5" />
          Viewing as <b class="ml-1">{role.label}</b>
          <span class="opacity-60 mx-2">·</span>
          <span class="opacity-80">{USERS[state.role].name}</span>
        </div>
      {/if}
      <Topbar />
      <main class="page">
        {@render children?.()}
      </main>
    </div>
  </div>
{/if}
