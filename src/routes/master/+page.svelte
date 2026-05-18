<script lang="ts">
  import { TEACHERS, DEPTS, CAMPUSES, fmtDate } from '$lib/data';
  import { state } from '$lib/state.svelte';
  import Pill from '$lib/components/Pill.svelte';
  import Avatar from '$lib/components/Avatar.svelte';
  import Icon from '$lib/components/Icon.svelte';

  const f = state.masterFilter;

  const statusTone: Record<string, any> = {
    permanent: 'green', contract: 'blue', probation: 'amber', opl: 'brown',
    leave: 'gold', alumni: 'gray',
  };

  const filtered = $derived(
    TEACHERS.filter(t => {
      if (f.q && !(`${t.name} ${t.subject} ${t.id}`.toLowerCase().includes(f.q.toLowerCase()))) return false;
      if (f.dept !== 'all' && t.dept !== f.dept) return false;
      if (f.campus !== 'all' && t.campus !== f.campus) return false;
      if (f.status !== 'all' && t.status !== f.status) return false;
      if (f.employment !== 'all' && t.employment !== f.employment) return false;
      return true;
    }).sort((a, b) =>
      f.sort === 'rating' ? (b.rating ?? 0) - (a.rating ?? 0)
      : f.sort === 'joined' ? +new Date(b.joined) - +new Date(a.joined)
      : a.name.localeCompare(b.name))
  );
</script>

<div class="flex items-end justify-between flex-wrap gap-3 mb-5">
  <div>
    <h1 class="font-display font-bold text-2xl">Master Database</h1>
    <p class="text-sm text-[color:var(--ink-mute)]">All teachers across campuses · {TEACHERS.length} records</p>
  </div>
  <div class="flex gap-2">
    <button class="btn btn-ghost btn-sm"><Icon name="download" class="w-3.5 h-3.5" /> Export CSV</button>
    <button class="btn btn-primary btn-sm"><Icon name="plus" class="w-3.5 h-3.5" /> Add teacher</button>
  </div>
</div>

<div class="card p-4 mb-3">
  <div class="grid md:grid-cols-6 gap-2">
    <div class="md:col-span-2 search">
      <Icon name="search" class="w-4 h-4" />
      <input class="input" placeholder="Search name, subject, ID…" bind:value={f.q} />
    </div>
    <select class="input select" bind:value={f.dept}>
      <option value="all">All departments</option>
      {#each DEPTS as d}<option value={d}>{d}</option>{/each}
    </select>
    <select class="input select" bind:value={f.campus}>
      <option value="all">All campuses</option>
      {#each CAMPUSES as c}<option value={c.id}>{c.name}</option>{/each}
    </select>
    <select class="input select" bind:value={f.status}>
      <option value="all">All statuses</option>
      <option value="permanent">Permanent</option>
      <option value="contract">Contract</option>
      <option value="probation">Probation</option>
      <option value="opl">OPL</option>
      <option value="leave">On Leave</option>
      <option value="alumni">Alumni</option>
    </select>
    <select class="input select" bind:value={f.sort}>
      <option value="name">Name (A–Z)</option>
      <option value="rating">Rating (High → Low)</option>
      <option value="joined">Joined (Recent)</option>
    </select>
  </div>
</div>

<div class="card overflow-hidden">
  <table class="tbl">
    <thead>
      <tr>
        <th>Teacher</th><th>ID / Emp</th><th>Subject</th><th>Campus</th>
        <th>Status</th><th>Joined</th><th>Rating</th><th></th>
      </tr>
    </thead>
    <tbody>
      {#each filtered as t}
        <tr>
          <td>
            <div class="flex items-center gap-2">
              <Avatar name={t.name} size="sm" />
              <div>
                <div class="font-semibold text-sm">{t.name}</div>
                <div class="text-[11px] text-[color:var(--ink-mute)]">{t.email}</div>
              </div>
            </div>
          </td>
          <td class="font-mono text-xs">{t.id}<br><span class="text-[color:var(--ink-faint)]">{t.employeeNo}</span></td>
          <td>{t.subject}<br><span class="text-[11px] text-[color:var(--ink-mute)]">{t.dept}</span></td>
          <td><Pill tone="gray">{t.campus.toUpperCase()}</Pill></td>
          <td><Pill tone={statusTone[t.status] ?? 'gray'} dot>{t.status}</Pill></td>
          <td class="text-xs">{fmtDate(t.joined)}<br><span class="text-[color:var(--ink-faint)]">{t.tenure}</span></td>
          <td>{t.rating ?? '—'}</td>
          <td><a class="btn btn-ghost btn-sm" href={`/teacher/${t.id}`}>Open</a></td>
        </tr>
      {/each}
    </tbody>
  </table>
  {#if !filtered.length}
    <div class="p-8 text-center text-sm text-[color:var(--ink-mute)]">No teachers match these filters.</div>
  {/if}
</div>
