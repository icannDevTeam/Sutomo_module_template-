<script lang="ts">
  import { page } from '$app/state';
  import { ROUTE_GROUPS, ROLES, USERS } from '$lib/data';
  import { state } from '$lib/state.svelte';
  import Icon from './Icon.svelte';
  import Avatar from './Avatar.svelte';

  const groups = $derived(ROUTE_GROUPS[state.role] || []);
  const role = $derived(ROLES.find(r => r.id === state.role)!);
  const me = $derived(USERS[state.role]);

  function isActive(route: string) {
    const path = page.url.pathname;
    if (route === '/') return path === '/';
    return path === route || path.startsWith(route + '/');
  }
</script>

<aside class="sidebar" class:open={state.sidebarOpen} id="sidebar">
  <div class="sb-brand">
    <div class="logo"><Icon name="users-round" class="w-4 h-4" /></div>
    <div class="name">HR</div>
  </div>

  <button class="sb-role" type="button" onclick={() => alert('Role switcher — coming soon. Edit state.role for now.')}>
    <span class="dot"></span>
    <div class="meta">
      <div class="l">{role.label}</div>
      <div class="s">{me.dept}</div>
    </div>
    <Icon name="chevrons-up-down" class="w-4 h-4 text-slate-400" />
  </button>

  <nav class="sb-nav">
    {#each groups as g (g.label)}
      <div class="sb-section">
        <div class="sb-section-label">{g.label}</div>
        {#each g.items as it (it.route)}
          <a
            class="sb-item"
            class:active={isActive(it.route)}
            href={it.route}
            data-sveltekit-preload-data="hover"
            onclick={() => { state.sidebarOpen = false; }}
          >
            <span class="ic"><Icon name={it.icon} class="w-4 h-4" /></span>
            <span>{it.label}</span>
            {#if it.badge}<span class="badge">{it.badge}</span>{/if}
          </a>
        {/each}
      </div>
    {/each}
  </nav>

  <div class="sb-foot">
    <div class="sb-user">
      <Avatar name={me.name} size="sm" />
      <div class="meta">
        <div class="n">{me.name}</div>
        <div class="s">{me.email}</div>
      </div>
      <Icon name="more-horizontal" class="w-4 h-4 text-slate-400" />
    </div>
  </div>
</aside>

<style>
  a.sb-item { text-decoration: none; }
</style>
