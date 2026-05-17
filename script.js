/* ==========================================================================
   Sutomo HR — App bootstrap, hash router, event delegation, modals, charts
   ========================================================================== */
(function () {
  const D = window.DB, H = window.HELPERS, U = window.UI, P = window.PAGES;
  const $app = document.getElementById('app');
  const $modal = document.getElementById('modalRoot');

  /* -------- Routing -------- */
  function defaultRouteForRole(role) {
    return D.ROUTES[role][0];
  }
  function ensureRouteAllowed() {
    const allowed = D.ROUTES[STATE.role] || [];
    if (!allowed.includes(STATE.route)) STATE.route = defaultRouteForRole(STATE.role);
  }
  function navigate(route, opts = {}) {
    if (!route) return;
    STATE.route = route;
    if (opts.id) {
      if (route === 'vacancy')   STATE.activeVacancyId   = opts.id;
      if (route === 'candidate') STATE.activeCandidateId = opts.id;
      if (route === 'teacher')   STATE.activeTeacherId   = opts.id;
    }
    if (route === 'candidate') STATE.candidateTab = 'overview';
    STATE.sidebarOpen = false;
    ensureRouteAllowed();
    render();
    window.scrollTo({ top: 0, behavior: 'instant' });
  }

  /* -------- Render pipeline -------- */
  function render() {
    // Preserve focus + caret across re-renders (live filters)
    const ae = document.activeElement;
    const focus = (ae && ae.id) ? { id: ae.id, start: ae.selectionStart, end: ae.selectionEnd } : null;

    const fn = P[STATE.route];
    if (!fn) { $app.innerHTML = U.shell('<div class="card p-10 text-center">Page not found</div>'); return; }
    const inner = fn.render();
    $app.innerHTML = U.shell(inner);
    if (window.lucide) lucide.createIcons();
    if (typeof fn.mount === 'function') fn.mount();
    mountDashChart();

    if (focus) {
      const el = document.getElementById(focus.id);
      if (el) {
        el.focus();
        try { el.setSelectionRange(focus.start, focus.end); } catch (_) {}
      }
    }
  }

  /* -------- Dashboard chart (small widget on multiple dashboards) -------- */
  function mountDashChart() {
    const c = document.getElementById('dashChart');
    if (!c) return;
    // Destroy any chart bound to this canvas (prevents infinite-grow on re-render)
    const existing = Chart.getChart && Chart.getChart(c);
    if (existing) existing.destroy();
    new Chart(c, {
      type: 'line',
      data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
          label: 'Hires',
          data: D.ANALYTICS.monthly,
          borderColor: '#6B3A00', backgroundColor: 'rgba(107,58,0,0.10)',
          fill: true, tension: 0.35, borderWidth: 2,
          pointBackgroundColor: '#6B3A00', pointRadius: 3,
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#EEF2F6' }, ticks: { font: { size: 10 } } },
          x: { grid: { display: false }, ticks: { font: { size: 10 } } }
        },
        maintainAspectRatio: false
      }
    });
  }

  /* -------- Event delegation -------- */
  document.addEventListener('click', (e) => {
    // close modal/drawer backdrops
    if (e.target.matches('[data-close-modal]') || e.target.closest('[data-close-modal]')) {
      $modal.innerHTML = ''; return;
    }
    if (e.target.matches('[data-close-drawer]')) {
      document.getElementById('drawerRoot').innerHTML = ''; return;
    }

    const routeEl = e.target.closest('[data-route]');
    if (routeEl) {
      const route = routeEl.dataset.route;
      const id = routeEl.dataset.id || null;
      navigate(route, { id });
      return;
    }

    const tabEl = e.target.closest('[data-tab]');
    if (tabEl && STATE.route === 'candidate') {
      STATE.candidateTab = tabEl.dataset.tab;
      render();
      return;
    }

    const hubTabEl = e.target.closest('[data-hub-tab]');
    if (hubTabEl) {
      const hub = hubTabEl.dataset.hub;
      const tab = hubTabEl.dataset.hubTab;
      if (hub && tab) {
        STATE.hubTab[hub] = tab;
        render();
      }
      return;
    }

    const actionEl = e.target.closest('[data-action]');
    if (actionEl) {
      handleAction(actionEl.dataset.action, actionEl.dataset.id, actionEl);
    }
  });

  /* -------- Actions -------- */
  function handleAction(action, id, el) {
    switch (action) {
      case 'toggle-sidebar':
        STATE.sidebarOpen = !STATE.sidebarOpen; render(); break;
      case 'toggle-notif':
        STATE.notifOpen = !STATE.notifOpen; render(); break;
      case 'close-notif':
        STATE.notifOpen = false; render(); break;

      case 'open-role-switcher': openRoleSwitcher(); break;
      case 'open-user-menu':     openUserMenu(); break;
      case 'open-help':          U.toast('Help center coming soon', 'info'); break;

      case 'apply-now':
        STATE.activeVacancyId = id || STATE.activeVacancyId;
        STATE.applyStep = 1; navigate('apply'); break;
      case 'apply-prev': STATE.applyStep = Math.max(1, STATE.applyStep - 1); render(); break;
      case 'apply-next': STATE.applyStep = Math.min(8, STATE.applyStep + 1); render(); break;
      case 'apply-submit':
        $modal.innerHTML = U.modal(`
          ${U.modalHeader('Application submitted','Thank you — we have received your application.')}
          <div class="px-6 py-6 text-center">
            <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-700 mx-auto flex items-center justify-center"><i data-lucide="check" class="w-7 h-7"></i></div>
            <h3 class="font-display font-bold text-lg mt-3">You're in the pipeline</h3>
            <p class="text-sm text-ink-mute mt-1">A confirmation has been sent to your email. You can track progress on the dashboard.</p>
            <button class="btn btn-primary mt-5" data-action="goto-tracker">Go to my application</button>
          </div>
        `);
        if (window.lucide) lucide.createIcons();
        break;
      case 'goto-tracker':
        $modal.innerHTML = ''; STATE.applyStep = 1; navigate('tracker'); break;
      case 'save-vacancy': U.toast('Vacancy saved to your shortlist'); break;

      case 'new-vacancy': openNewVacancy(); break;
      case 'export':      U.toast('CSV export queued — check email', 'info'); break;

      case 'cand-advance':
        advanceCandidate(id || STATE.activeCandidateId); break;
      case 'cand-reject':
        rejectCandidate(id || STATE.activeCandidateId); break;

      case 'verify-deposit': {
        const cid = id;
        const c = H.candidate(cid);
        if (c?.deposit) c.deposit.status = 'verified';
        const dep = D.DEPOSITS.find(d => d.candidateId === cid); if (dep) dep.status = 'verified';
        U.toast(`Deposit verified for ${c?.name || cid}`); render(); break;
      }
      case 'edit-test':
        openEditTest(id); break;

      case 'yay-approve': {
        const c = H.candidate(id);
        if (c) { c.yayasan = { status:'approved', date: H.ymd(2026,5,15), by:'Dr. Tanto Halim' }; c.stage = 'opl'; }
        U.toast(`${c?.name} approved — moved to OPL`); render(); break;
      }
      case 'yay-reject': {
        const c = H.candidate(id);
        if (c) { c.stage = 'rejected'; c.rejectedReason = 'Yayasan board decision'; }
        U.toast(`${c?.name} rejected`, 'warn'); render(); break;
      }
      case 'yay-escalate':
        U.toast('Returned to HR for additional review', 'info'); break;

      case 'opl-eval':
        openOplEval(id); break;

      case 'master-reset':
        STATE.masterFilter = { q:'', dept:'all', campus:'all', status:'all', employment:'all', sort:'name' };
        render(); break;
      case 'master-add':
        U.toast('Add staff form — coming soon', 'info'); break;
      case 'master-quick': {
        const t = D.TEACHERS.find(x => x.id === id);
        if (t) U.toast(`${t.name} · ${t.employeeNo}`, 'info');
        break;
      }
    }
  }

  function advanceCandidate(cid) {
    const c = H.candidate(cid); if (!c) return;
    const order = D.STAGES.filter(s=>s.id!=='rejected').map(s=>s.id);
    const i = order.indexOf(c.stage);
    if (i < 0 || i === order.length - 1) { U.toast('Already at final stage','info'); return; }
    c.stage = order[i+1];
    c.timeline = c.timeline || [];
    c.timeline.push({ d: H.ymd(2026,5,15), t:`Moved to ${H.stageLabel(c.stage)}`, who:'Sri Lestari (HR)' });
    U.toast(`Advanced to ${H.stageLabel(c.stage)}`);
    render();
  }
  function rejectCandidate(cid) {
    const c = H.candidate(cid); if (!c) return;
    c.stage = 'rejected'; c.rejectedReason = 'Did not progress';
    U.toast(`${c.name} rejected`, 'warn'); render();
  }

  /* -------- Modals -------- */
  function openRoleSwitcher() {
    $modal.innerHTML = U.modal(`
      ${U.modalHeader('Switch role','Demo: instantly impersonate any user role to view the system from their perspective.')}
      <div class="p-4 grid sm:grid-cols-2 gap-2">
        ${D.ROLES.map(r => {
          const u = D.USERS[r.id];
          const active = STATE.role === r.id;
          return `
            <button data-role="${r.id}" class="text-left card-flat p-3 hover:border-slate-300 ${active?'!border-brand !bg-cream':''}">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-cream text-brand-dark flex items-center justify-center"><i data-lucide="${r.icon}" class="w-5 h-5"></i></div>
                <div class="flex-1 min-w-0">
                  <div class="font-display font-semibold text-sm text-ink">${r.label}</div>
                  <div class="text-xs text-ink-faint truncate">${u.name}</div>
                  <div class="text-[11px] text-ink-faint">${u.dept}</div>
                </div>
                ${active?'<i data-lucide="check-circle-2" class="w-4 h-4 text-brand-dark"></i>':''}
              </div>
            </button>`;
        }).join('')}
      </div>
    `, { size: 'lg' });
    if (window.lucide) lucide.createIcons();
    $modal.querySelectorAll('[data-role]').forEach(btn => {
      btn.addEventListener('click', () => {
        STATE.role = btn.dataset.role;
        STATE.route = defaultRouteForRole(STATE.role);
        $modal.innerHTML = '';
        render();
        U.toast(`Switched to ${D.ROLES.find(r=>r.id===STATE.role).label}`, 'info');
      });
    });
  }

  function openUserMenu() {
    const u = D.USERS[STATE.role];
    $modal.innerHTML = U.modal(`
      ${U.modalHeader('Account','Manage your profile and preferences.')}
      <div class="px-6 py-5">
        <div class="flex items-center gap-3 mb-4">
          <div class="av av-lg">${H.initials(u.name)}</div>
          <div><div class="font-display font-semibold">${u.name}</div><div class="text-xs text-ink-mute">${u.email}</div></div>
        </div>
        <div class="menu">
          <div class="menu-item"><i data-lucide="user" class="w-4 h-4"></i> Profile settings</div>
          <div class="menu-item"><i data-lucide="bell" class="w-4 h-4"></i> Notification preferences</div>
          <div class="menu-item"><i data-lucide="shield" class="w-4 h-4"></i> Security & 2FA</div>
          <div class="menu-item"><i data-lucide="languages" class="w-4 h-4"></i> Language: English</div>
          <div class="menu-item" data-action="open-role-switcher"><i data-lucide="user-cog" class="w-4 h-4"></i> Switch role…</div>
          <div class="divider my-2"></div>
          <div class="menu-item text-red-600"><i data-lucide="log-out" class="w-4 h-4"></i> Sign out</div>
        </div>
      </div>
    `);
    if (window.lucide) lucide.createIcons();
  }

  function openNewVacancy() {
    $modal.innerHTML = U.modal(`
      ${U.modalHeader('New vacancy','Post a new teaching role to the public careers portal.')}
      <div class="px-6 py-5 grid md:grid-cols-2 gap-4">
        <div class="md:col-span-2"><label class="label">Role title</label><input class="input" placeholder="e.g. Mathematics Teacher"/></div>
        <div><label class="label">Department</label><select class="select">${D.DEPTS.map(d=>`<option>${d}</option>`).join('')}</select></div>
        <div><label class="label">Campus</label><select class="select">${D.CAMPUSES.map(c=>`<option>${c.name}</option>`).join('')}</select></div>
        <div><label class="label">Type</label><select class="select"><option>Full-time</option><option>Part-time</option><option>Contract</option></select></div>
        <div><label class="label">Openings</label><input class="input" type="number" value="1"/></div>
        <div><label class="label">Posted</label><input class="input" type="date"/></div>
        <div><label class="label">Closes</label><input class="input" type="date"/></div>
        <div class="md:col-span-2"><label class="label">Summary</label><textarea class="textarea" rows="3" placeholder="Short public-facing description…"></textarea></div>
      </div>
      <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-2">
        <button class="btn btn-ghost" data-close-modal>Cancel</button>
        <button class="btn btn-soft" data-action="vac-save-draft">Save as draft</button>
        <button class="btn btn-primary" data-action="vac-publish">${`<i data-lucide="send" class="w-4 h-4"></i>`} Publish</button>
      </div>
    `, { size: 'lg' });
    if (window.lucide) lucide.createIcons();
    $modal.querySelector('[data-action="vac-publish"]').addEventListener('click', () => {
      $modal.innerHTML = ''; U.toast('Vacancy published to public portal');
    });
    $modal.querySelector('[data-action="vac-save-draft"]').addEventListener('click', () => {
      $modal.innerHTML = ''; U.toast('Draft saved', 'info');
    });
  }

  function openEditTest(cid) {
    const c = H.candidate(cid) || D.CANDIDATES[0];
    $modal.innerHTML = U.modal(`
      ${U.modalHeader(`Edit written test score · ${c.name}`,'Edit window: 48 hours after submission. All edits are audit-logged.')}
      <div class="px-6 py-5 grid md:grid-cols-2 gap-4">
        <div><label class="label">Algebra (max 25)</label><input class="input" type="number" value="22"/></div>
        <div><label class="label">Geometry (max 25)</label><input class="input" type="number" value="21"/></div>
        <div><label class="label">Statistics (max 25)</label><input class="input" type="number" value="22"/></div>
        <div><label class="label">Calculus (max 25)</label><input class="input" type="number" value="21"/></div>
        <div class="md:col-span-2"><label class="label">Reason for edit</label><textarea class="textarea" rows="2" placeholder="Required — appears in audit log"></textarea></div>
      </div>
      <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-2">
        <button class="btn btn-ghost" data-close-modal>Cancel</button>
        <button class="btn btn-primary" data-action="save-test">Save & log</button>
      </div>
    `);
    if (window.lucide) lucide.createIcons();
    $modal.querySelector('[data-action="save-test"]').addEventListener('click', () => {
      $modal.innerHTML = ''; U.toast('Score updated · audit log entry created');
    });
  }

  function openOplEval(cid) {
    const c = H.candidate(cid) || D.CANDIDATES[0];
    const next = c.opl?.sessions?.find(s => s.status === 'scheduled') || c.opl?.sessions?.[0];
    $modal.innerHTML = U.modal(`
      ${U.modalHeader(`OPL Session ${next.n} evaluation · ${c.name}`, `Topic: ${next.topic} · ${H.fmtDate(next.date)}`)}
      <div class="px-6 py-5 space-y-4">
        ${[
          ['Lesson planning','Clarity of objectives, sequencing, pacing'],
          ['Subject mastery','Accuracy of explanation and depth'],
          ['Classroom presence','Voice, posture, board management'],
          ['Student engagement','Q&A, group work, attention'],
          ['Assessment','Formative checks, exit tickets'],
        ].map(r=>`
          <div>
            <div class="flex items-center justify-between mb-1">
              <div class="text-sm font-semibold">${r[0]}</div><div class="text-xs text-ink-faint">${r[1]}</div>
            </div>
            <div class="flex gap-1">
              ${[1,2,3,4,5].map(n=>`<button class="w-9 h-9 rounded-md border border-slate-200 hover:bg-cream hover:border-brand text-sm font-bold">${n}</button>`).join('')}
            </div>
          </div>`).join('')}
        <div><label class="label">Mentor notes</label><textarea class="textarea" rows="3" placeholder="Strengths, areas for next session…"></textarea></div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" checked/> Notify HR & Principal of this evaluation</label>
      </div>
      <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-2">
        <button class="btn btn-ghost" data-close-modal>Cancel</button>
        <button class="btn btn-primary" data-action="save-opl">${`<i data-lucide="check" class="w-4 h-4"></i>`} Save evaluation</button>
      </div>
    `, { size: 'lg' });
    if (window.lucide) lucide.createIcons();
    $modal.querySelector('[data-action="save-opl"]').addEventListener('click', () => {
      if (next) { next.status = 'done'; next.score = 88; next.notes = 'Strong session — see mentor notes.'; }
      $modal.innerHTML = ''; U.toast('OPL session evaluation saved'); render();
    });
  }

  /* -------- Boot -------- */
  ensureRouteAllowed();
  render();

  // Allow pages to request a re-render (e.g. after live filter changes)
  window.addEventListener('rerender', () => render());

  // Keyboard shortcut for command palette (placeholder)
  document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault(); U.toast('Command palette · coming soon', 'info');
    }
  });
})();
