<script lang="ts">
  import { state } from '$lib/state.svelte';
  import {
    ANALYTICS, AUDIT, CANDIDATES, INTERVIEWS, STAGES, VACANCIES,
    countByStage, findCandidate, findVacancy, fmtDate, fmtIDR, today, initials,
    DEPOSITS, daysBetween,
  } from '$lib/data';
  import Stat from '$lib/components/Stat.svelte';
  import Pill from '$lib/components/Pill.svelte';
  import Avatar from '$lib/components/Avatar.svelte';
  import Icon from '$lib/components/Icon.svelte';
  import Bar from '$lib/components/Bar.svelte';
  import Ring from '$lib/components/Ring.svelte';
  import RoleSwitcher from '$lib/components/RoleSwitcher.svelte';
  import { onMount } from 'svelte';

  const cnt = $derived(countByStage());
  const upcoming = $derived(INTERVIEWS.filter(i => i.status === 'scheduled'));
  const stageColors = ['gray','blue','blue','gold','violet','green','maroon','brown','green'];

  let chartEl: HTMLCanvasElement | undefined = $state();
  let chart: any = null;

  $effect(() => {
    // re-mount chart when role changes (canvas may be re-created)
    state.role;
    if (!chartEl) return;
    (async () => {
      const { Chart, registerables } = await import('chart.js');
      Chart.register(...registerables);
      if (chart) chart.destroy();
      chart = new Chart(chartEl, {
        type: 'line',
        data: {
          labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
          datasets: [{
            label: 'Hires',
            data: ANALYTICS.monthly,
            borderColor: '#2563EB',
            backgroundColor: 'rgba(37,99,235,0.10)',
            fill: true, tension: 0.35, borderWidth: 2,
            pointBackgroundColor: '#2563EB', pointRadius: 3,
          }]
        },
        options: {
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, grid: { color: '#EEF2F6' }, ticks: { font: { size: 10 } } },
            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
          },
          maintainAspectRatio: false,
        }
      });
    })();
  });

  onMount(() => () => chart?.destroy());
</script>

<div class="mb-4"><RoleSwitcher /></div>

{#if state.role === 'hr'}
  <div class="flex items-end justify-between flex-wrap gap-3 mb-5">
    <div>
      <div class="text-xs text-[color:var(--ink-faint)] font-semibold uppercase tracking-wider">
        {fmtDate(today)} · Recruitment overview
      </div>
      <h1 class="font-display font-bold text-2xl text-[color:var(--ink)]">Good morning, Sri.</h1>
    </div>
    <div class="flex gap-2">
      <button class="btn btn-ghost btn-sm"><Icon name="download" class="w-3.5 h-3.5" /> Export</button>
      <button class="btn btn-primary btn-sm"><Icon name="plus" class="w-3.5 h-3.5" /> New vacancy</button>
    </div>
  </div>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <Stat label="Active vacancies"  value={VACANCIES.filter(v => v.status !== 'closed').length}
          sub={`${VACANCIES.filter(v => v.status === 'closing').length} closing this month`}
          icon="briefcase" tone="brown" />
    <Stat label="Total candidates" value={CANDIDATES.filter((c: any) => c.stage !== 'rejected' && c.stage !== 'active').length}
          sub="in active pipeline" icon="users-round" tone="blue" delta={12} />
    <Stat label="Pending interviews" value={upcoming.length} sub="next 7 days"
          icon="video" tone="gold" />
    <Stat label="Awaiting Yayasan" value={CANDIDATES.filter((c: any) => c.stage === 'yayasan').length}
          sub="board review needed" icon="landmark" tone="maroon" />
  </div>

  <div class="grid lg:grid-cols-3 gap-4 mb-4">
    <div class="card p-5 lg:col-span-2">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-display font-semibold text-base">Pipeline by stage</h3>
        <a class="btn btn-ghost btn-sm" href="/recruitment">View pipeline <Icon name="arrow-right" class="w-3.5 h-3.5" /></a>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
        {#each STAGES.filter(s => s.id !== 'rejected') as s, i}
          <a class="card-flat p-3 cursor-pointer hover:border-slate-300 block" href="/recruitment">
            <div class="text-[10px] uppercase tracking-wider font-bold text-[color:var(--ink-faint)]">{s.label}</div>
            <div class="font-display font-bold text-2xl mt-1">{cnt[s.id]}</div>
            <Bar pct={Math.min(100, cnt[s.id] * 10)} />
          </a>
        {/each}
      </div>
    </div>

    <div class="card p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-display font-semibold text-base">Hiring funnel (90 d)</h3>
      </div>
      <div class="funnel">
        {#each ANALYTICS.funnel as f}
          {@const max = ANALYTICS.funnel[0].n}
          <div class="row">
            <div class="lbl">{f.stage}</div>
            <Bar pct={(f.n / max) * 100} />
            <div class="n">{f.n}</div>
          </div>
        {/each}
      </div>
    </div>
  </div>

  <div class="grid lg:grid-cols-3 gap-4 mb-4">
    <div class="card p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-display font-semibold text-base">Upcoming interviews</h3>
        <a class="btn btn-ghost btn-sm" href="/assessments">All</a>
      </div>
      <div class="space-y-3">
        {#each upcoming.slice(0, 4) as i}
          {@const c = findCandidate(i.candidateId)}
          <div class="flex items-center gap-3 cursor-pointer">
            <div class="text-center w-12 flex-shrink-0">
              <div class="text-[10px] uppercase font-bold text-[color:var(--ink-faint)]">
                {new Date(i.date).toLocaleDateString('en', { month: 'short' })}
              </div>
              <div class="font-display font-bold text-lg">{new Date(i.date).getDate()}</div>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-sm">{c?.name ?? 'Candidate'}</div>
              <div class="text-xs text-[color:var(--ink-mute)]">{i.type} · {i.time} · {i.room}</div>
            </div>
            <Pill tone="green" dot>{#snippet children()}Confirmed{/snippet}</Pill>
          </div>
        {/each}
      </div>
    </div>

    <div class="card p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-display font-semibold text-base">Action queue</h3>
      </div>
      <div class="space-y-2">
        {#each [
          { ic:'wallet',         t:'2 deposits awaiting verification', s:'Finance escalated' },
          { ic:'landmark',       t:'1 candidate awaiting Yayasan',     s:'Citra Maharani' },
          { ic:'file-warning',   t:'4 documents missing reupload',     s:'Across 3 candidates' },
          { ic:'graduation-cap', t:'OPL session evaluation due',       s:'Aditya Wijaya · S3' },
        ] as a}
          <div class="flex items-center gap-3 card-flat p-3 cursor-pointer hover:border-slate-300">
            <div class="w-9 h-9 rounded-md flex items-center justify-center bg-blue-50 text-blue-700">
              <Icon name={a.ic} class="w-4 h-4" />
            </div>
            <div class="flex-1">
              <div class="text-sm font-semibold">{a.t}</div>
              <div class="text-xs text-[color:var(--ink-mute)]">{a.s}</div>
            </div>
            <Icon name="chevron-right" class="w-4 h-4 text-slate-400" />
          </div>
        {/each}
      </div>
    </div>

    <div class="card p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-display font-semibold text-base">Hires this year</h3>
        <a class="btn btn-ghost btn-sm" href="/analytics">Analytics</a>
      </div>
      <div class="chart-wrap sm"><canvas bind:this={chartEl}></canvas></div>
    </div>
  </div>

  <div class="card p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-display font-semibold text-base">Latest activity</h3>
      <a class="btn btn-ghost btn-sm" href="/governance">Audit log</a>
    </div>
    <table class="tbl tbl-compact">
      <thead>
        <tr><th>Time</th><th>User</th><th>Action</th><th>Target</th><th>Note</th></tr>
      </thead>
      <tbody>
        {#each AUDIT.slice(0, 6) as a}
          <tr>
            <td class="text-[color:var(--ink-mute)]">{a.ts}</td>
            <td>
              <div class="flex items-center gap-2">
                <Avatar name={a.user} size="xs" />
                <span>{a.user}</span>
              </div>
            </td>
            <td><Pill tone="gray">{#snippet children()}{a.action}{/snippet}</Pill></td>
            <td class="font-mono text-xs">{a.target}</td>
            <td class="text-[color:var(--ink-mute)]">{a.note}</td>
          </tr>
        {/each}
      </tbody>
    </table>
  </div>

{:else if state.role === 'principal'}
  <div class="mb-5">
    <div class="text-xs text-[color:var(--ink-faint)] font-semibold uppercase tracking-wider">{fmtDate(today)}</div>
    <h1 class="font-display font-bold text-2xl">Principal Dashboard</h1>
    <p class="text-sm text-[color:var(--ink-mute)]">Sutomo SMA · Hiring & onboarding overview</p>
  </div>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <Stat label="Awaiting my decision" value={4} sub="interview & OPL evaluations" icon="clipboard-check" tone="brown" />
    <Stat label="Active OPL teachers" value={1} sub="1 mentor, 7 sessions remaining" icon="graduation-cap" tone="green" />
    <Stat label="Probation passing" value="5 / 6" sub="90-day track" icon="timer" tone="gold" />
    <Stat label="Faculty satisfaction" value="4.7 / 5" sub="Q1 2026 survey" icon="star" tone="gold" />
  </div>
  <div class="card p-5">
    <h3 class="font-display font-semibold text-base mb-3">OPL progress</h3>
    {#each CANDIDATES.filter((c: any) => c.stage === 'opl') as c}
      {@const done = c.opl.sessions.filter((s: any) => s.status === 'done').length}
      {@const total = c.opl.sessions.length}
      <div class="card-flat p-4">
        <div class="flex items-center gap-3">
          <Avatar name={c.name} size="md" />
          <div class="flex-1">
            <div class="font-semibold text-sm">{c.name}</div>
            <div class="text-xs text-[color:var(--ink-mute)]">{c.opl.sessions[0].mentor} · 90-day OPL</div>
          </div>
          <Ring pct={Math.round((done / total) * 100)} size={56} />
        </div>
        <div class="text-xs text-[color:var(--ink-mute)] mt-3">{done} of {total} sessions completed</div>
        <Bar pct={(done / total) * 100} tone="green" />
      </div>
    {/each}
  </div>

{:else if state.role === 'finance'}
  {@const pend = DEPOSITS.filter(d => d.status === 'pending')}
  {@const ver = DEPOSITS.filter(d => d.status === 'verified')}
  <div class="mb-5">
    <h1 class="font-display font-bold text-2xl">Finance Dashboard</h1>
    <p class="text-sm text-[color:var(--ink-mute)]">Recruitment deposits & refunds</p>
  </div>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <Stat label="Pending verification" value={pend.length} sub="awaiting bank confirmation" icon="clock" tone="amber" />
    <Stat label="Verified deposits" value={ver.length} sub={fmtIDR(ver.reduce((a, b) => a + b.amount, 0))} icon="check-circle-2" tone="green" />
    <Stat label="Refund eligible" value={DEPOSITS.filter(d => d.refundEligible).length} sub="OPL completed" icon="undo-2" tone="brown" />
    <Stat label="Held this month" value={fmtIDR(45000000)} sub="+12% vs last month" icon="wallet" tone="gold" delta={12} />
  </div>

{:else}
  <div class="mb-5">
    <h1 class="font-display font-bold text-2xl">Dashboard</h1>
    <p class="text-sm text-[color:var(--ink-mute)]">Welcome back.</p>
  </div>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <Stat label="Open vacancies" value={VACANCIES.filter(v => v.status !== 'closed').length} icon="briefcase" tone="brown" />
    <Stat label="Faculty" value="327" icon="users-round" tone="blue" />
    <Stat label="Pipeline" value={CANDIDATES.length} icon="kanban" tone="gold" />
    <Stat label="Audit (24h)" value={142} icon="shield-check" tone="green" />
  </div>
{/if}
