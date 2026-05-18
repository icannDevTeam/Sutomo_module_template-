<script lang="ts">
  import { page } from '$app/state';
  import { NOTIFICATIONS, USERS } from '$lib/data';
  import { state } from '$lib/state.svelte';
  import Icon from './Icon.svelte';
  import Avatar from './Avatar.svelte';

  const titleMap: Record<string, string> = {
    '/':              'Dashboard',
    '/analytics':     'HR Analytics',
    '/recruitment':   'Recruitment',
    '/assessments':   'Assessments',
    '/verifications': 'Verifications',
    '/onboarding':    'Onboarding',
    '/workforce':     'Workforce',
    '/governance':    'Governance',
    '/master':        'Master Database',
    '/yayasan':       'Yayasan Approval',
    '/inbox':         'Notifications',
    '/careers':       'Open Vacancies',
    '/apply':         'Apply',
    '/tracker':       'My Application',
    '/mydocs':        'My Documents',
  };

  const title = $derived(titleMap[page.url.pathname] || 'Workspace');
  const me = $derived(USERS[state.role]);
  const unread = $derived(
    NOTIFICATIONS.filter(n => !n.read && (!n.role || n.role.includes(state.role))).length
  );
</script>

<div class="topbar">
  <div class="topbar-inner">
    <button class="iconbtn lg:hidden" type="button" onclick={() => (state.sidebarOpen = !state.sidebarOpen)}>
      <Icon name="menu" class="w-4 h-4" />
    </button>
    <div class="crumbs">
      <span>Workspace</span>
      <span class="sep">/</span>
      <span class="cur">{title}</span>
    </div>
    <div class="search hidden md:flex ml-2">
      <Icon name="search" class="w-4 h-4" />
      <input placeholder="Search candidates, vacancies, teachers…" />
      <span class="kbd">⌘K</span>
    </div>
    <div class="ml-auto flex items-center gap-1">
      <button class="iconbtn" type="button"><Icon name="help-circle" class="w-4 h-4" /></button>
      <button class="iconbtn" type="button" onclick={() => (state.notifOpen = !state.notifOpen)}>
        <Icon name="bell" class="w-4 h-4" />
        {#if unread}<span class="ping"></span>{/if}
      </button>
      <div class="w-px h-6 bg-slate-200 mx-1"></div>
      <button class="iconbtn" type="button"><Avatar name={me.name} size="xs" /></button>
    </div>
  </div>
</div>
