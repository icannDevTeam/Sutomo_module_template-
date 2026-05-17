/* ==========================================================================
   Sutomo HR — UI components (shell, sidebar, topbar, primitives)
   ========================================================================== */
(function () {
  const { ROLES, USERS, ROUTE_GROUPS, NOTIFICATIONS } = window.DB;
  const H = window.HELPERS;

  const icon = (name, cls = 'w-4 h-4') => `<i data-lucide="${name}" class="${cls}"></i>`;

  // ============ Shell ============
  function shell(inner, opts = {}) {
    const isPublic = STATE.role === 'applicant';
    if (isPublic) return publicShell(inner, opts);
    return `
      <div class="app-shell">
        ${sidebar()}
        <div class="min-h-screen flex flex-col">
          ${roleBanner()}
          ${topbar(opts)}
          <main class="page">${inner}</main>
        </div>
      </div>
      <div id="sbBackdrop" class="fixed inset-0 bg-black/40 z-[45] hidden lg:hidden"></div>
    `;
  }

  function roleBanner() {
    if (STATE.role === 'hr') return '';
    const role = ROLES.find(r => r.id === STATE.role);
    return `<div class="role-banner">
      ${icon('eye', 'w-3.5 h-3.5')}
      Viewing as <b class="ml-1">${role.label}</b>
      <span class="opacity-60 mx-2">·</span>
      <span class="opacity-80">${USERS[STATE.role].name}</span>
      <button data-action="open-role-switcher" class="ml-3 underline opacity-90 hover:opacity-100">Switch</button>
    </div>`;
  }

  function sidebar() {
    const groups = ROUTE_GROUPS[STATE.role] || [];
    const role = ROLES.find(r => r.id === STATE.role);
    const me = USERS[STATE.role];
    return `
      <aside class="sidebar ${STATE.sidebarOpen ? 'open' : ''}" id="sidebar">
        <div class="sb-brand">
          <div class="logo">${icon('users-round','w-4 h-4')}</div>
          <div class="name">HR</div>
        </div>

        <button class="sb-role" data-action="open-role-switcher">
          <span class="dot"></span>
          <div class="meta">
            <div class="l">${role.label}</div>
            <div class="s">${me.dept}</div>
          </div>
          ${icon('chevrons-up-down','w-4 h-4 text-slate-400')}
        </button>

        <nav class="sb-nav">
          ${groups.map(g => `
            <div class="sb-section">
              <div class="sb-section-label">${g.label}</div>
              ${g.items.map(it => `
                <div class="sb-item ${STATE.route === it.id ? 'active' : ''}" data-route="${it.id}">
                  ${icon(it.icon, 'ic')}
                  <span>${it.label}</span>
                  ${it.badge ? `<span class="badge">${it.badge}</span>` : ''}
                </div>`).join('')}
            </div>
          `).join('')}
        </nav>

        <div class="sb-foot">
          <div class="sb-user" data-action="open-user-menu">
            <div class="av av-sm">${H.initials(me.name)}</div>
            <div class="meta">
              <div class="n">${me.name}</div>
              <div class="s">${me.email}</div>
            </div>
            ${icon('more-horizontal','w-4 h-4 text-slate-400')}
          </div>
        </div>
      </aside>
    `;
  }

  function topbar(opts) {
    const crumbs = opts.crumbs || [{ l: 'Workspace' }, { l: titleFromRoute() }];
    const unread = NOTIFICATIONS.filter(n => !n.read && (!n.role || n.role.includes(STATE.role))).length;
    return `
      <div class="topbar">
        <div class="topbar-inner">
          <button class="iconbtn lg:hidden" data-action="toggle-sidebar">${icon('menu')}</button>
          <div class="crumbs">
            ${crumbs.map((c, i) => `
              ${i > 0 ? '<span class="sep">/</span>' : ''}
              <span class="${i === crumbs.length - 1 ? 'cur' : ''}">${c.l}</span>
            `).join('')}
          </div>
          <div class="search hidden md:flex ml-2">
            ${icon('search','w-4 h-4')}
            <input placeholder="Search candidates, vacancies, teachers…" />
            <span class="kbd">⌘K</span>
          </div>
          <div class="ml-auto flex items-center gap-1">
            <button class="iconbtn" data-action="open-help">${icon('help-circle')}</button>
            <button class="iconbtn" data-action="toggle-notif">${icon('bell')}${unread ? '<span class="ping"></span>' : ''}</button>
            <div class="w-px h-6 bg-slate-200 mx-1"></div>
            <button class="iconbtn" data-action="open-user-menu">
              <div class="av av-xs">${H.initials(USERS[STATE.role].name)}</div>
            </button>
          </div>
        </div>
      </div>
      ${notifPanel()}
    `;
  }

  function titleFromRoute() {
    const map = {
      dashboard:'Dashboard', pipeline:'ATS Pipeline', candidates:'Candidates',
      vacancies:'Vacancies', vacancy:'Vacancy', candidate:'Candidate Profile',
      tests:'Written Tests', interviews:'Interviews', deposits:'Deposits',
      psycho:'Psycho Verification', medical:'Medical Verification',
      yayasan:'Yayasan Approval', opl:'OPL Tracker', probation:'Probation',
      contracts:'Contracts', bukuinduk:'Buku Induk Guru', teacher:'Teacher Profile',
      analytics:'HR Analytics', audit:'Audit Log', inbox:'Notifications',
      settings:'Settings', careers:'Open Vacancies', apply:'Apply', tracker:'My Application', mydocs:'My Documents',
      recruitment:'Recruitment', assessments:'Assessments', verifications:'Verifications',
      onboarding:'Onboarding', workforce:'Workforce', governance:'Governance',
      master:'Master Database'
    };
    return map[STATE.route] || STATE.route;
  }

  function notifPanel() {
    if (!STATE.notifOpen) return '';
    const items = NOTIFICATIONS.filter(n => !n.role || n.role.includes(STATE.role));
    return `
      <div class="fixed inset-0 z-[60]" data-action="close-notif">
        <div class="absolute right-3 top-14 w-[360px] max-w-[calc(100vw-1rem)] card bg-white shadow-pop overflow-hidden" onclick="event.stopPropagation()">
          <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
            <div class="font-display font-bold text-sm">Notifications</div>
            <button class="text-xs text-brand-dark font-semibold">Mark all as read</button>
          </div>
          <div class="max-h-[60vh] overflow-y-auto">
            ${items.length ? items.map(n => `
              <div class="flex gap-3 px-4 py-3 border-b border-slate-100 hover:bg-slate-50 cursor-pointer">
                <div class="w-8 h-8 rounded-lg bg-cream flex items-center justify-center text-brand-dark flex-shrink-0">
                  ${icon(n.icon)}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-start gap-2">
                    <div class="text-[13px] font-semibold text-ink leading-tight">${n.title}</div>
                    ${!n.read ? '<span class="w-1.5 h-1.5 rounded-full bg-maroon mt-1.5"></span>' : ''}
                  </div>
                  <div class="text-xs text-ink-mute leading-snug mt-0.5">${n.body}</div>
                  <div class="text-[10px] text-slate-400 mt-1">${n.time}</div>
                </div>
              </div>
            `).join('') : `<div class="px-4 py-8 text-center text-sm text-slate-400">No notifications</div>`}
          </div>
        </div>
      </div>
    `;
  }

  // ============ Primitives ============
  function stat({ label, value, sub, icon: ic = 'circle', tone = 'gray', delta }) {
    const tones = {
      gray: 'bg-slate-100 text-slate-600',
      brown:'bg-cream text-brand-dark',
      gold: 'bg-[#FEF6DC] text-[#92750E]',
      green:'bg-emerald-50 text-emerald-700',
      amber:'bg-amber-50 text-amber-700',
      red:  'bg-red-50 text-red-700',
      blue: 'bg-blue-50 text-blue-700',
      maroon:'bg-pink-50 text-maroon',
    };
    return `
      <div class="card p-4">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-lg flex items-center justify-center ${tones[tone] || tones.gray}">${icon(ic, 'w-4 h-4')}</div>
          <div class="flex-1 min-w-0">
            <div class="text-[11px] uppercase tracking-wider text-ink-faint font-semibold">${label}</div>
            <div class="text-2xl font-display font-bold text-ink mt-0.5 leading-none">${value}</div>
            ${sub ? `<div class="text-xs text-ink-mute mt-1">${sub}</div>` : ''}
          </div>
          ${delta != null ? `<div class="text-[11px] font-semibold ${delta>=0?'text-emerald-600':'text-red-600'}">${delta>=0?'+':''}${delta}%</div>` : ''}
        </div>
      </div>
    `;
  }

  function sectionTitle(label, action) {
    return `
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-display font-semibold text-base text-ink">${label}</h3>
        ${action || ''}
      </div>
    `;
  }

  function pill(text, tone = 'gray', dot = false) {
    return `<span class="pill pill-${tone}">${dot ? '<span class="pill-dot"></span>' : ''}${text}</span>`;
  }

  function avatar(name, size = 'sm') {
    return `<div class="av av-${size}">${H.initials(name)}</div>`;
  }

  function bar(pct, tone = '') {
    return `<div class="bar ${tone}"><i style="width:${Math.max(0,Math.min(100,pct))}%"></i></div>`;
  }

  function ring(pct, size = 64, tone = 'var(--brand-dark)') {
    const r = size/2 - 6, c = 2 * Math.PI * r;
    const off = c * (1 - pct/100);
    return `
      <div class="ring" style="width:${size}px;height:${size}px">
        <svg width="${size}" height="${size}">
          <circle cx="${size/2}" cy="${size/2}" r="${r}" stroke="#E2E8F0" stroke-width="6" fill="none"/>
          <circle cx="${size/2}" cy="${size/2}" r="${r}" stroke="${tone}" stroke-width="6" fill="none"
            stroke-linecap="round" stroke-dasharray="${c}" stroke-dashoffset="${off}"/>
        </svg>
        <div class="pct">${pct}%</div>
      </div>
    `;
  }

  function stepper(steps, currentId, opts = {}) {
    return `<div class="stepper">${steps.map((s, i) => {
      const idx = steps.findIndex(x => x.id === currentId);
      const cls = i < idx ? 'done' : i === idx ? 'cur' : (opts.failedAt === s.id ? 'fail' : '');
      return `
        ${i > 0 ? `<div class="bar ${i <= idx ? '' : ''}" style="width:24px;height:2px;background:${i<=idx?'var(--green)':'var(--border)'};"></div>` : ''}
        <div class="step ${cls}">
          <span class="num">${i < idx ? '✓' : (i + 1)}</span>
          <span class="lbl">${s.label}</span>
        </div>
      `;
    }).join('')}</div>`;
  }

  function modal(html, opts = {}) {
    const size = opts.size === 'lg' ? 'lg' : opts.size === 'xl' ? 'xl' : '';
    return `
      <div class="modal-bd" data-close-modal>
        <div class="modal ${size}" onclick="event.stopPropagation()">
          ${html}
        </div>
      </div>
    `;
  }

  function modalHeader(title, sub) {
    return `
      <div class="px-6 py-4 border-b border-slate-200 flex items-start gap-3">
        <div class="flex-1">
          <h3 class="font-display font-bold text-lg text-ink">${title}</h3>
          ${sub ? `<p class="text-xs text-ink-mute mt-0.5">${sub}</p>` : ''}
        </div>
        <button class="iconbtn" data-close-modal>${icon('x')}</button>
      </div>
    `;
  }

  function drawer(html, opts = {}) {
    return `
      <div class="drawer-bd" data-close-drawer></div>
      <div class="drawer">
        ${html}
      </div>
    `;
  }

  function empty(label, sub, ic = 'inbox') {
    return `
      <div class="card p-10 text-center">
        <div class="w-12 h-12 rounded-xl bg-slate-100 mx-auto flex items-center justify-center text-slate-400">${icon(ic,'w-6 h-6')}</div>
        <div class="mt-3 font-display font-semibold text-ink">${label}</div>
        ${sub ? `<div class="text-sm text-ink-mute mt-1">${sub}</div>` : ''}
      </div>
    `;
  }

  function toast(message, kind = 'success') {
    const el = document.createElement('div');
    el.className = 'toast ' + (kind === 'success' ? '' : kind);
    const ico = { success: 'check-circle-2', err: 'x-circle', warn: 'alert-triangle', info: 'info' }[kind] || 'check-circle-2';
    el.innerHTML = `<i data-lucide="${ico}" class="w-4 h-4 text-white/80"></i><span>${message}</span>`;
    document.getElementById('toastRoot').appendChild(el);
    lucide.createIcons({ attrs: { class: 'w-4 h-4 text-white/80' } });
    setTimeout(() => { el.style.transition = 'opacity .2s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 220); }, 2400);
  }

  // ============ Public shell (applicant-facing) ============
  function publicShell(inner) {
    return `
      <header class="public-nav sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-5 py-3 flex items-center gap-6">
          <div class="flex items-center gap-2 cursor-pointer" data-route="careers">
            <div class="w-8 h-8 rounded-md bg-brand-dark text-white flex items-center justify-center font-bold text-sm">S</div>
            <div class="font-display font-bold text-ink">Sutomo Careers</div>
          </div>
          <nav class="hidden md:flex items-center gap-5 text-sm text-ink-mute font-medium">
            <a class="hover:text-ink cursor-pointer" data-route="careers">Vacancies</a>
            <a class="hover:text-ink cursor-pointer" data-action="scroll-benefits">Benefits</a>
            <a class="hover:text-ink cursor-pointer" data-action="scroll-process">Process</a>
            <a class="hover:text-ink cursor-pointer" data-action="scroll-life">Life at Sutomo</a>
          </nav>
          <div class="ml-auto flex items-center gap-2">
            <button class="btn btn-ghost btn-sm" data-route="tracker">${icon('list-checks','w-3.5 h-3.5')} Track Application</button>
            <button class="btn btn-ghost btn-sm" data-action="open-role-switcher">${icon('user-cog','w-3.5 h-3.5')} Staff sign in</button>
          </div>
        </div>
      </header>
      <main class="min-h-screen">${inner}</main>
      <footer class="border-t border-slate-200 mt-12 bg-white">
        <div class="max-w-6xl mx-auto px-5 py-8 grid md:grid-cols-4 gap-8 text-sm text-ink-mute">
          <div>
            <div class="flex items-center gap-2 mb-3">
              <div class="w-7 h-7 rounded-md bg-brand-dark text-white flex items-center justify-center font-bold text-xs">S</div>
              <div class="font-display font-bold text-ink">Sutomo Schools</div>
            </div>
            <p class="text-xs leading-relaxed">A multi-campus international school ecosystem in Medan, North Sumatra. Founded 1953.</p>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider font-bold text-ink mb-3">Careers</div>
            <ul class="space-y-1.5 text-xs">
              <li><a class="hover:text-ink cursor-pointer" data-route="careers">All vacancies</a></li>
              <li><a class="hover:text-ink cursor-pointer">Internships</a></li>
              <li><a class="hover:text-ink cursor-pointer">Why teach with us</a></li>
            </ul>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider font-bold text-ink mb-3">Campuses</div>
            <ul class="space-y-1.5 text-xs">
              <li>Sutomo Elementary (SD)</li>
              <li>Sutomo Junior High (SMP)</li>
              <li>Sutomo Senior High (SMA)</li>
              <li>Sutomo International</li>
            </ul>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider font-bold text-ink mb-3">Contact</div>
            <ul class="space-y-1.5 text-xs">
              <li>recruitment@sutomo.sch.id</li>
              <li>+62 61 4000 1953</li>
              <li>Jl. Sutomo No.1, Medan</li>
            </ul>
          </div>
        </div>
        <div class="border-t border-slate-100 py-4 text-center text-xs text-slate-400">© 2026 Yayasan Sutomo. All rights reserved.</div>
      </footer>
    `;
  }

  window.UI = { shell, sidebar, topbar, stat, sectionTitle, pill, avatar, bar, ring, stepper, modal, modalHeader, drawer, empty, toast, icon };
})();
