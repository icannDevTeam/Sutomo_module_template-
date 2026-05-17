/* ==========================================================================
   Sutomo HR — All page renderers (PAGES.<route>)
   ========================================================================== */
(function () {
  const D = window.DB, H = window.HELPERS, U = window.UI;
  const ic = (n, c='w-4 h-4') => `<i data-lucide="${n}" class="${c}"></i>`;

  /* =================================================================
     PUBLIC PORTAL — careers home
  ================================================================= */
  const careers = {
    render() {
      const open = D.VACANCIES.filter(v => v.status !== 'closed');
      const featured = open.filter(v => v.featured);
      const grid = (list) => `
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
          ${list.map(v => {
            const cmp = H.campus(v.campus);
            return `
              <div class="vac-card" data-route="vacancy" data-id="${v.id}">
                <div class="flex items-start gap-3 mb-3">
                  <div class="w-10 h-10 rounded-lg bg-cream text-brand-dark flex items-center justify-center">${ic('briefcase','w-5 h-5')}</div>
                  <div class="flex-1 min-w-0">
                    <div class="text-[11px] text-ink-faint font-semibold uppercase tracking-wide">${v.dept} · ${cmp.short}</div>
                    <div class="font-display font-bold text-ink leading-tight mt-0.5">${v.title}</div>
                  </div>
                  ${v.status === 'closing' ? U.pill('Closing soon','amber') : U.pill('Open','green',true)}
                </div>
                <p class="text-sm text-ink-mute leading-relaxed line-clamp-2">${v.summary}</p>
                <div class="flex flex-wrap gap-1.5 mt-3">
                  ${U.pill(v.type,'gray')}
                  ${U.pill(v.level,'gray')}
                  ${U.pill(cmp.name,'brown')}
                </div>
                <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100">
                  <div class="text-xs text-ink-faint">${v.applicants} applicants · closes ${H.fmtDate(v.closes)}</div>
                  <span class="link text-sm">View role →</span>
                </div>
              </div>
            `;
          }).join('')}
        </div>`;

      return `
        <!-- HERO -->
        <section class="hero">
          <div class="max-w-6xl mx-auto px-5 py-16 md:py-24 grid lg:grid-cols-2 gap-10 items-center">
            <div>
              <div class="pill pill-brown mb-4">${ic('sparkles','w-3 h-3')} 2026/2027 Academic Year Hiring</div>
              <h1 class="font-display font-extrabold text-ink leading-[1.05] text-4xl md:text-5xl">
                Shape the next generation of <span class="text-brand-dark">Sutomo</span> learners.
              </h1>
              <p class="text-base text-ink-mute mt-5 leading-relaxed max-w-lg">
                Join a 70-year-old multi-campus institution serving 6,400+ students across 4 campuses in Medan.
                We are hiring teachers who are curious, rigorous, and deeply student-centered.
              </p>
              <div class="flex flex-wrap items-center gap-3 mt-7">
                <button class="btn btn-primary btn-lg" data-action="scroll-vacancies">${ic('search','w-4 h-4')} Browse open roles</button>
                <button class="btn btn-ghost btn-lg" data-route="tracker">Track an application</button>
              </div>
              <div class="grid grid-cols-3 gap-6 mt-10 max-w-md">
                <div><div class="font-display font-bold text-2xl text-ink">${open.length}</div><div class="text-xs text-ink-mute">Open roles</div></div>
                <div><div class="font-display font-bold text-2xl text-ink">4</div><div class="text-xs text-ink-mute">Campuses</div></div>
                <div><div class="font-display font-bold text-2xl text-ink">320+</div><div class="text-xs text-ink-mute">Faculty</div></div>
              </div>
            </div>
            <div class="hidden lg:block">
              <div class="relative">
                <div class="card-elev p-5 max-w-md ml-auto">
                  <div class="flex items-center gap-3 mb-3">
                    <div class="av av-md">RS</div>
                    <div>
                      <div class="font-semibold text-sm text-ink">Rini Surya, M.Pd</div>
                      <div class="text-xs text-ink-mute">Senior Mathematics Teacher · 8 years</div>
                    </div>
                    ${U.pill('Mentor','brown')}
                  </div>
                  <p class="text-sm text-ink-soft leading-relaxed">
                    "What I value most at Sutomo is the autonomy our department heads give us to design rigorous, joyful curriculum.
                    The mentor program made my first year here transformative."
                  </p>
                  <div class="flex items-center gap-2 mt-3 text-[11px] text-gold-dark">
                    ${ic('star','w-3 h-3')}${ic('star','w-3 h-3')}${ic('star','w-3 h-3')}${ic('star','w-3 h-3')}${ic('star','w-3 h-3')}
                    <span class="text-ink-mute">Faculty satisfaction 4.7/5</span>
                  </div>
                </div>
                <div class="card-elev p-4 max-w-xs mt-4 ml-12">
                  <div class="flex items-center gap-2 text-xs text-ink-mute font-semibold mb-2">${ic('trending-up','w-4 h-4 text-emerald-600')} Median time-to-hire</div>
                  <div class="font-display font-bold text-2xl text-ink">42 days</div>
                  <div class="text-xs text-ink-mute mt-1">From application to OPL start</div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- FILTERS + VACANCIES -->
        <section class="max-w-6xl mx-auto px-5 py-12" id="vacancies">
          <div class="flex items-end justify-between mb-6 flex-wrap gap-3">
            <div>
              <h2 class="font-display font-bold text-2xl text-ink">Open positions</h2>
              <p class="text-sm text-ink-mute mt-1">${open.length} roles across ${D.CAMPUSES.length} campuses</p>
            </div>
            <div class="flex flex-wrap gap-2">
              <select class="select" style="width:auto"><option>All departments</option>${D.DEPTS.map(d=>`<option>${d}</option>`).join('')}</select>
              <select class="select" style="width:auto"><option>All campuses</option>${D.CAMPUSES.map(c=>`<option>${c.name}</option>`).join('')}</select>
              <select class="select" style="width:auto"><option>All types</option><option>Full-time</option><option>Part-time</option></select>
            </div>
          </div>

          ${featured.length ? `
            <div class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gold-dark">${ic('star','w-3.5 h-3.5')} Featured</div>
            ${grid(featured)}
            <div class="my-8 border-t border-slate-200"></div>
          ` : ''}

          ${grid(open.filter(v => !v.featured))}
        </section>

        <!-- BENEFITS -->
        <section class="bg-white border-y border-slate-200" id="benefits">
          <div class="max-w-6xl mx-auto px-5 py-14">
            <div class="text-center mb-10">
              <div class="text-xs uppercase tracking-wider font-bold text-brand-dark mb-2">What we offer</div>
              <h2 class="font-display font-bold text-2xl text-ink">A career — not just a job</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-4">
              ${[
                { i:'wallet',          t:'Competitive compensation', d:'Salaries 2× regional UMR · THR · 13th-month bonus · annual performance review.' },
                { i:'heart-pulse',     t:'Comprehensive coverage',   d:'BPJS Kesehatan & Ketenagakerjaan · in-network specialist clinic · annual health screening.' },
                { i:'graduation-cap',  t:'Tuition discount',         d:'Up to 70% tuition discount for own children across all four Sutomo campuses.' },
                { i:'book-open',       t:'Professional development', d:'Rp 8 jt annual PD budget · Cambridge & IB training · sabbaticals after 7 yrs.' },
                { i:'users-round',     t:'Mentor program',           d:'Every new teacher gets a 10-session OPL mentor and a 90-day structured onboarding.' },
                { i:'palmtree',        t:'Generous leave',           d:'14 days annual leave · all Indonesian public holidays · 2 weeks teacher-only winter break.' },
              ].map(b => `
                <div class="card p-5">
                  <div class="w-10 h-10 rounded-lg bg-cream text-brand-dark flex items-center justify-center mb-3">${ic(b.i,'w-5 h-5')}</div>
                  <div class="font-display font-semibold text-ink">${b.t}</div>
                  <div class="text-sm text-ink-mute mt-1.5 leading-relaxed">${b.d}</div>
                </div>`).join('')}
            </div>
          </div>
        </section>

        <!-- PROCESS -->
        <section class="max-w-6xl mx-auto px-5 py-14" id="process">
          <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-wider font-bold text-brand-dark mb-2">How hiring works</div>
            <h2 class="font-display font-bold text-2xl text-ink">A transparent, structured process</h2>
            <p class="text-sm text-ink-mute mt-2">Median 42 days from application to first OPL session.</p>
          </div>
          <div class="grid md:grid-cols-4 gap-4">
            ${[
              { n:1, t:'Apply',           d:'Submit CV, transcripts, and a short teaching philosophy.', day:'Day 1' },
              { n:2, t:'Screen & Test',   d:'CV review and a subject-matter written test.',              day:'Day 7' },
              { n:3, t:'Teach & Verify',  d:'Micro-teaching, panel interview, psycho & medical clearance.', day:'Day 21' },
              { n:4, t:'Approve & Onboard',d:'Yayasan board approval, then 10-session OPL mentorship.',  day:'Day 42' },
            ].map(s => `
              <div class="card p-5">
                <div class="flex items-center justify-between">
                  <div class="w-9 h-9 rounded-lg bg-brand-dark text-white flex items-center justify-center font-display font-bold">${s.n}</div>
                  ${U.pill(s.day, 'brown')}
                </div>
                <div class="font-display font-semibold text-ink mt-3">${s.t}</div>
                <div class="text-sm text-ink-mute mt-1.5 leading-relaxed">${s.d}</div>
              </div>`).join('')}
          </div>
        </section>

        <!-- LIFE AT SUTOMO -->
        <section class="bg-cream-soft border-y border-slate-200" id="life">
          <div class="max-w-6xl mx-auto px-5 py-14 grid md:grid-cols-2 gap-10 items-center">
            <div>
              <div class="text-xs uppercase tracking-wider font-bold text-brand-dark mb-2">Life at Sutomo</div>
              <h2 class="font-display font-bold text-2xl text-ink leading-tight">A multi-campus community in the heart of Medan</h2>
              <p class="text-sm text-ink-mute mt-3 leading-relaxed">
                Four campuses, one mission. From the SD playgrounds to the IB Diploma physics labs, Sutomo teachers
                collaborate across grade levels and subjects — sharing curriculum, mentoring each other, and showing up for the same students for over a decade.
              </p>
              <div class="grid grid-cols-2 gap-3 mt-6">
                ${D.CAMPUSES.map(c => `
                  <div class="card p-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-md bg-brand-dark text-white flex items-center justify-center font-bold text-xs">${c.short}</div>
                    <div><div class="font-semibold text-sm text-ink">${c.name}</div><div class="text-xs text-ink-faint">${c.city}</div></div>
                  </div>`).join('')}
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              ${[1,2,3,4].map(i=>`<div class="aspect-[4/3] rounded-lg bg-gradient-to-br from-cream to-slate-200 border border-slate-200 flex items-center justify-center text-slate-400">${ic('image','w-8 h-8')}</div>`).join('')}
            </div>
          </div>
        </section>

        <!-- CTA -->
        <section class="max-w-4xl mx-auto px-5 py-14 text-center">
          <h2 class="font-display font-bold text-2xl text-ink">Don't see the right role?</h2>
          <p class="text-sm text-ink-mute mt-2">We accept open applications year-round for exceptional educators.</p>
          <button class="btn btn-primary btn-lg mt-5" data-route="apply">${ic('send','w-4 h-4')} Submit open application</button>
        </section>
      `;
    },
    mount() {
      document.querySelectorAll('[data-action="scroll-vacancies"]').forEach(b => b.addEventListener('click', () => document.getElementById('vacancies')?.scrollIntoView({behavior:'smooth'})));
      ['benefits','process','life'].forEach(s => {
        document.querySelectorAll(`[data-action="scroll-${s}"]`).forEach(b => b.addEventListener('click', () => document.getElementById(s)?.scrollIntoView({behavior:'smooth'})));
      });
    }
  };

  /* =================================================================
     PUBLIC — vacancy detail
  ================================================================= */
  const vacancy = {
    render() {
      const v = H.vacancy(STATE.activeVacancyId) || D.VACANCIES[0];
      const cmp = H.campus(v.campus);
      const isPublic = STATE.role === 'applicant';
      return `
        <div class="${isPublic ? 'max-w-5xl mx-auto px-5 py-10' : ''}">
          ${isPublic ? `
            <button class="text-sm text-ink-mute hover:text-ink mb-4 inline-flex items-center gap-1" data-route="careers">${ic('arrow-left','w-4 h-4')} Back to all vacancies</button>
          ` : ''}
          <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
              <div class="card p-6">
                <div class="flex items-center gap-2 mb-2">
                  ${U.pill(v.dept, 'brown')}
                  ${U.pill(cmp.name, 'gray')}
                  ${U.pill(v.type, 'gray')}
                  ${v.status === 'open' ? U.pill('Open', 'green', true) : v.status === 'closing' ? U.pill('Closing soon', 'amber') : U.pill('Closed', 'gray')}
                </div>
                <h1 class="font-display font-bold text-2xl text-ink">${v.title}</h1>
                <div class="text-sm text-ink-mute mt-1">${v.id} · Posted ${H.fmtDate(v.posted)} · Closes ${H.fmtDate(v.closes)}</div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5 pt-5 border-t border-slate-100">
                  <div><div class="text-[11px] uppercase text-ink-faint font-semibold">Openings</div><div class="font-display font-bold text-lg">${v.openings}</div></div>
                  <div><div class="text-[11px] uppercase text-ink-faint font-semibold">Applicants</div><div class="font-display font-bold text-lg">${v.applicants}</div></div>
                  <div><div class="text-[11px] uppercase text-ink-faint font-semibold">Level</div><div class="font-semibold text-sm">${v.level}</div></div>
                  <div><div class="text-[11px] uppercase text-ink-faint font-semibold">Campus</div><div class="font-semibold text-sm">${cmp.name}</div></div>
                </div>
              </div>

              <div class="card p-6">
                <h3 class="font-display font-semibold text-base mb-2">About the role</h3>
                <p class="text-sm text-ink-soft leading-relaxed">${v.summary}</p>
              </div>

              <div class="card p-6">
                <h3 class="font-display font-semibold text-base mb-3">Responsibilities</h3>
                <ul class="space-y-2">
                  ${v.responsibilities.map(r => `<li class="flex gap-2 text-sm text-ink-soft"><i data-lucide="check" class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5"></i><span>${r}</span></li>`).join('')}
                </ul>
              </div>

              <div class="card p-6">
                <h3 class="font-display font-semibold text-base mb-3">Requirements</h3>
                <ul class="space-y-2">
                  ${v.requirements.map(r => `<li class="flex gap-2 text-sm text-ink-soft"><i data-lucide="dot" class="w-4 h-4 text-brand-dark flex-shrink-0 mt-0.5"></i><span>${r}</span></li>`).join('')}
                </ul>
              </div>

              <div class="card p-6">
                <h3 class="font-display font-semibold text-base mb-3">Benefits</h3>
                <div class="grid sm:grid-cols-2 gap-2">
                  ${v.benefits.map(b => `<div class="flex gap-2 text-sm text-ink-soft"><i data-lucide="gift" class="w-4 h-4 text-gold-dark flex-shrink-0 mt-0.5"></i><span>${b}</span></div>`).join('')}
                </div>
              </div>

              <div class="card p-6">
                <h3 class="font-display font-semibold text-base mb-3">Frequently asked questions</h3>
                <div class="space-y-3">
                  ${[
                    { q:'Do I need a Sertifikat Pendidik?', a:'It is preferred but not required. We offer a structured pathway to certification within your first two years.' },
                    { q:'Is the deposit refundable?',         a:'Yes — the Rp 5,000,000 deposit is fully refundable upon completion of OPL, regardless of the outcome.' },
                    { q:'Can I apply to multiple campuses?',  a:'Yes, you may apply to as many open roles as you qualify for. Each application is considered independently.' },
                    { q:'Are housing allowances offered?',    a:'For roles requiring relocation from outside North Sumatra, a one-time settling allowance is offered.' },
                  ].map(f => `
                    <details class="border-b border-slate-100 pb-3 last:border-0">
                      <summary class="font-semibold text-sm text-ink cursor-pointer">${f.q}</summary>
                      <p class="text-sm text-ink-mute mt-2 leading-relaxed">${f.a}</p>
                    </details>`).join('')}
                </div>
              </div>
            </div>

            <!-- STICKY APPLY -->
            <div class="space-y-4">
              <div class="card p-5 sticky top-20">
                <div class="text-xs text-ink-faint">Compensation range</div>
                <div class="font-display font-bold text-2xl text-ink mt-1">Rp 7.5 – 12 jt</div>
                <div class="text-xs text-ink-mute">per month, based on experience</div>
                <button class="btn btn-primary w-full mt-4" data-action="apply-now" data-id="${v.id}">${ic('send','w-4 h-4')} Apply for this role</button>
                <button class="btn btn-ghost w-full mt-2" data-action="save-vacancy">${ic('bookmark','w-4 h-4')} Save</button>
                <div class="divider"></div>
                <div class="text-xs text-ink-faint mb-2">Application deadline</div>
                <div class="text-sm font-semibold">${H.fmtDate(v.closes)}</div>
                <div class="text-xs text-ink-mute mt-0.5">${H.daysBetween(H.today, v.closes)} days remaining</div>
                <div class="divider"></div>
                <div class="text-xs text-ink-faint mb-2">Hiring team</div>
                <div class="space-y-2">
                  <div class="flex items-center gap-2"><div class="av av-sm">BS</div><div><div class="text-sm font-semibold">Drs. Budi Santoso</div><div class="text-[11px] text-ink-faint">Principal · SMA</div></div></div>
                  <div class="flex items-center gap-2"><div class="av av-sm">MH</div><div><div class="text-sm font-semibold">Maria Hartanto, M.Pd</div><div class="text-[11px] text-ink-faint">Head of ${v.dept}</div></div></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    }
  };

  /* =================================================================
     PUBLIC — application wizard
  ================================================================= */
  const apply = {
    render() {
      const steps = [
        { id:1, t:'Personal' },{ id:2, t:'Academic' },{ id:3, t:'Experience' },
        { id:4, t:'Subjects' },{ id:5, t:'Languages' },{ id:6, t:'School History' },
        { id:7, t:'Documents' },{ id:8, t:'Review' },
      ];
      const cur = STATE.applyStep;

      return `
        <div class="max-w-4xl mx-auto px-5 py-10">
          <div class="text-xs text-ink-faint font-semibold uppercase tracking-wider">Apply</div>
          <h1 class="font-display font-bold text-2xl text-ink">Mathematics Teacher · SMA</h1>
          <div class="text-sm text-ink-mute mt-1">Step ${cur} of ${steps.length} — your progress is auto-saved</div>

          <div class="mt-6 mb-6 overflow-x-auto">
            <div class="flex items-center gap-2">
              ${steps.map(s => `
                <div class="flex items-center gap-2 px-3 py-2 rounded-md text-xs flex-shrink-0
                  ${s.id < cur ? 'bg-emerald-50 text-emerald-700' : s.id === cur ? 'bg-cream text-brand-dark font-semibold' : 'bg-slate-100 text-slate-500'}">
                  <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold
                    ${s.id < cur ? 'bg-emerald-600 text-white' : s.id === cur ? 'bg-brand-dark text-white' : 'bg-slate-200 text-slate-600'}">
                    ${s.id < cur ? '✓' : s.id}
                  </span>
                  ${s.t}
                </div>
                ${s.id < steps.length ? '<div class="h-px w-3 bg-slate-200 flex-shrink-0"></div>' : ''}
              `).join('')}
            </div>
          </div>

          <div class="card p-6">
            ${apply.stepHTML(cur)}
            <div class="flex items-center justify-between mt-6 pt-5 border-t border-slate-100">
              <button class="btn btn-ghost" data-action="apply-prev" ${cur === 1 ? 'disabled' : ''}>${ic('chevron-left','w-4 h-4')} Back</button>
              <div class="text-xs text-ink-faint">Auto-saved 12 sec ago</div>
              ${cur < steps.length
                ? `<button class="btn btn-primary" data-action="apply-next">Continue ${ic('chevron-right','w-4 h-4')}</button>`
                : `<button class="btn btn-primary" data-action="apply-submit">${ic('send','w-4 h-4')} Submit application</button>`}
            </div>
          </div>
        </div>
      `;
    },
    stepHTML(s) {
      if (s === 1) return `
        <h3 class="font-display font-semibold text-base mb-4">Personal information</h3>
        <div class="grid md:grid-cols-2 gap-4">
          <div><label class="label">Full name</label><input class="input" placeholder="As shown on KTP"/></div>
          <div><label class="label">NIK / National ID</label><input class="input" placeholder="16 digits"/></div>
          <div><label class="label">Email</label><input class="input" type="email" placeholder="you@example.com"/></div>
          <div><label class="label">Phone (WhatsApp)</label><input class="input" placeholder="+62 ..."/></div>
          <div><label class="label">Date of birth</label><input class="input" type="date"/></div>
          <div><label class="label">Gender</label><select class="select"><option>Male</option><option>Female</option></select></div>
          <div class="md:col-span-2"><label class="label">Current address</label><textarea class="textarea" rows="2"></textarea></div>
        </div>`;
      if (s === 2) return `
        <h3 class="font-display font-semibold text-base mb-4">Academic background</h3>
        <div class="space-y-4">
          ${[1,2].map(i=>`
            <div class="card-flat p-4 grid md:grid-cols-4 gap-3">
              <div><label class="label">Degree</label><select class="select"><option>S1</option><option>S2</option><option>S3</option></select></div>
              <div class="md:col-span-2"><label class="label">Institution</label><input class="input" placeholder="University name"/></div>
              <div><label class="label">Year</label><input class="input" placeholder="2023"/></div>
              <div class="md:col-span-2"><label class="label">Major</label><input class="input" placeholder="Mathematics Education"/></div>
              <div><label class="label">GPA</label><input class="input" placeholder="3.65"/></div>
              <div><label class="label">Country</label><input class="input" placeholder="Indonesia" value="Indonesia"/></div>
            </div>`).join('')}
          <button class="btn btn-ghost btn-sm">${ic('plus','w-3.5 h-3.5')} Add another degree</button>
        </div>`;
      if (s === 3) return `
        <h3 class="font-display font-semibold text-base mb-4">Professional experience</h3>
        <div class="space-y-4">
          <div class="card-flat p-4 grid md:grid-cols-2 gap-3">
            <div><label class="label">School / institution</label><input class="input" placeholder="Previous school"/></div>
            <div><label class="label">Role</label><input class="input" placeholder="Mathematics Teacher"/></div>
            <div><label class="label">Start date</label><input class="input" type="date"/></div>
            <div><label class="label">End date</label><input class="input" type="date"/></div>
            <div class="md:col-span-2"><label class="label">Key responsibilities</label><textarea class="textarea" rows="3"></textarea></div>
          </div>
          <button class="btn btn-ghost btn-sm">${ic('plus','w-3.5 h-3.5')} Add another position</button>
        </div>`;
      if (s === 4) return `
        <h3 class="font-display font-semibold text-base mb-4">Subject specialization</h3>
        <p class="text-sm text-ink-mute mb-4">Select all subjects you are qualified to teach.</p>
        <div class="grid md:grid-cols-3 gap-2">
          ${D.DEPTS.map(d=>`
            <label class="flex items-center gap-2 card-flat px-3 py-2 cursor-pointer">
              <input type="checkbox" class="rounded"/> <span class="text-sm">${d}</span>
            </label>`).join('')}
        </div>
        <div class="mt-5">
          <label class="label">Preferred grade level</label>
          <div class="flex gap-2 flex-wrap">
            ${['SD G1-G3','SD G4-G6','SMP G7-G9','SMA G10-G12','IB DP'].map(g=>`<button class="btn btn-soft btn-sm">${g}</button>`).join('')}
          </div>
        </div>`;
      if (s === 5) return `
        <h3 class="font-display font-semibold text-base mb-4">Language skills</h3>
        <div class="space-y-3">
          ${['Bahasa Indonesia','English','Mandarin','Other'].map(l=>`
            <div class="grid grid-cols-12 items-center gap-3 card-flat p-3">
              <div class="col-span-3 font-semibold text-sm">${l}</div>
              <div class="col-span-9">
                <select class="select"><option>None</option><option>Basic</option><option>Conversational</option><option>Working proficiency</option><option>Fluent / Native</option></select>
              </div>
            </div>`).join('')}
          <div class="grid md:grid-cols-2 gap-3">
            <div><label class="label">TOEFL / IELTS / HSK score (optional)</label><input class="input"/></div>
            <div><label class="label">Test date</label><input class="input" type="date"/></div>
          </div>
        </div>`;
      if (s === 6) return `
        <h3 class="font-display font-semibold text-base mb-4">Previous school history</h3>
        <p class="text-sm text-ink-mute mb-4">List schools you taught at, including any shadow / volunteer experience.</p>
        <div class="card-flat p-4 grid md:grid-cols-2 gap-3">
          <div><label class="label">School name</label><input class="input"/></div>
          <div><label class="label">Type</label><select class="select"><option>Public</option><option>Private</option><option>International</option></select></div>
          <div><label class="label">Years</label><input class="input" placeholder="2020 – 2024"/></div>
          <div><label class="label">Reference name</label><input class="input"/></div>
          <div class="md:col-span-2"><label class="label">Reference contact</label><input class="input"/></div>
        </div>`;
      if (s === 7) return `
        <h3 class="font-display font-semibold text-base mb-2">Upload documents</h3>
        <p class="text-sm text-ink-mute mb-4">All documents must be PDF or JPG, under 10 MB each.</p>
        <div class="space-y-3">
          ${[
            { n:'CV / Resume',         req:true, status:'uploaded', file:'CV_Aditya_Wijaya.pdf' },
            { n:'Bachelor Degree',     req:true, status:'uploaded', file:'Ijazah_S1.pdf' },
            { n:'Master Degree',       req:false, status:'uploaded', file:'Ijazah_S2.pdf' },
            { n:'Academic Transcripts',req:true, status:'uploaded', file:'Transkrip.pdf' },
            { n:'Sertifikat Pendidik', req:false, status:'pending' },
            { n:'Teaching Portfolio',  req:false, status:'pending' },
            { n:'KTP / National ID',   req:true, status:'uploaded', file:'KTP.jpg' },
          ].map(d=>`
            <div class="card-flat p-3 flex items-center gap-3">
              <div class="w-9 h-9 rounded-md ${d.status==='uploaded'?'bg-emerald-50 text-emerald-600':'bg-slate-100 text-slate-400'} flex items-center justify-center">${ic(d.status==='uploaded'?'file-check':'file-plus','w-5 h-5')}</div>
              <div class="flex-1 min-w-0">
                <div class="font-semibold text-sm">${d.n} ${d.req?'<span class="text-red-500">*</span>':''}</div>
                <div class="text-xs text-ink-faint">${d.status==='uploaded'?d.file:'PDF or JPG · max 10 MB'}</div>
              </div>
              ${d.status==='uploaded'
                ? `<button class="btn btn-ghost btn-sm">${ic('eye','w-3.5 h-3.5')} View</button><button class="btn btn-ghost btn-sm">Replace</button>`
                : `<button class="btn btn-soft btn-sm">${ic('upload','w-3.5 h-3.5')} Upload</button>`}
            </div>`).join('')}
        </div>
        <div class="dropzone mt-4">
          ${ic('upload-cloud','w-7 h-7 mx-auto mb-2')}
          <div class="font-semibold text-sm">Drop files here or click to browse</div>
          <div class="text-xs mt-1">Supports PDF, JPG, PNG · max 10 MB per file</div>
        </div>`;
      // step 8 — review
      return `
        <h3 class="font-display font-semibold text-base mb-4">Review & submit</h3>
        <div class="space-y-3">
          ${[
            { t:'Personal',  v:'Aditya P. Wijaya · 29 yrs · Medan' },
            { t:'Academic',  v:'S2 Pendidikan Matematika — Univ. Negeri Yogyakarta (2021)' },
            { t:'Experience',v:'5 years · most recently SMA Sutomo 1 (Praktik)' },
            { t:'Subjects',  v:'Mathematics, Statistics · SMA G10–G12 + IB DP' },
            { t:'Languages', v:'Indonesian (Native), English (Working), Mandarin (Basic)' },
            { t:'Documents', v:'5 of 7 uploaded' },
          ].map(r=>`
            <div class="flex items-start justify-between gap-3 py-3 border-b border-slate-100">
              <div><div class="text-[11px] uppercase font-bold text-ink-faint">${r.t}</div><div class="text-sm text-ink mt-0.5">${r.v}</div></div>
              <button class="text-xs text-brand-dark font-semibold">Edit</button>
            </div>`).join('')}
        </div>
        <label class="flex items-start gap-2 mt-4 text-sm text-ink-soft">
          <input type="checkbox" class="mt-1"/>
          I confirm all information above is accurate and consent to Sutomo's data handling for recruitment purposes.
        </label>
      `;
    }
  };

  /* =================================================================
     APPLICANT — tracker
  ================================================================= */
  const tracker = {
    render() {
      const c = H.candidate('C-1001'); // demo: applicant sees Aditya
      const v = H.vacancy(c.vacancyId);
      const stages = D.STAGES.filter(s => s.id !== 'rejected');
      return `
        <div class="max-w-4xl mx-auto px-5 py-10">
          <div class="text-xs text-ink-faint font-semibold uppercase tracking-wider">My Application</div>
          <h1 class="font-display font-bold text-2xl text-ink">${v.title}</h1>
          <div class="text-sm text-ink-mute mt-1">${v.id} · Applied ${H.fmtDate(c.appliedAt)}</div>

          <div class="card p-6 mt-6">
            <h3 class="font-display font-semibold text-base mb-4">Application progress</h3>
            <div class="overflow-x-auto pb-2">${U.stepper(stages, c.stage)}</div>
            <div class="divider"></div>
            <p class="text-sm text-ink-soft">
              You are currently in the <b class="text-brand-dark">${H.stageLabel(c.stage).toUpperCase()}</b> stage. 
              Your mentor <b>${c.opl?.sessions?.[0]?.mentor || 'Rini Surya'}</b> has scheduled the next session.
            </p>
          </div>

          <div class="grid md:grid-cols-2 gap-4 mt-4">
            <div class="card p-5">
              <h3 class="font-display font-semibold text-base mb-3">Recent updates</h3>
              <div class="tl">
                ${(c.timeline || []).slice().reverse().slice(0,6).map(t=>`
                  <div class="tl-item ok">
                    <div class="text-sm font-semibold text-ink">${t.t}</div>
                    <div class="text-xs text-ink-faint">${H.fmtDate(t.d)} · ${t.who}</div>
                  </div>`).join('')}
              </div>
            </div>
            <div class="card p-5">
              <h3 class="font-display font-semibold text-base mb-3">Next steps</h3>
              <div class="space-y-3">
                ${(c.opl?.sessions || []).filter(s=>s.status!=='done').slice(0,3).map(s=>`
                  <div class="flex items-start gap-3 card-flat p-3">
                    <div class="w-9 h-9 rounded-md bg-cream text-brand-dark flex items-center justify-center font-bold">${s.n}</div>
                    <div class="flex-1">
                      <div class="font-semibold text-sm">OPL Session ${s.n} · ${s.topic}</div>
                      <div class="text-xs text-ink-mute">${H.fmtDate(s.date)} · with ${s.mentor}</div>
                    </div>
                    ${U.pill(s.status, s.status==='scheduled'?'blue':'gray')}
                  </div>`).join('')}
              </div>
              <button class="btn btn-ghost w-full mt-3">View full schedule</button>
            </div>
          </div>

          <div class="card p-5 mt-4">
            <h3 class="font-display font-semibold text-base mb-3">Hiring team</h3>
            <div class="grid sm:grid-cols-3 gap-3">
              ${[['BS','Drs. Budi Santoso','Principal'],['MH','Maria Hartanto','Dept Head'],['SL','Sri Lestari','HR']].map(p=>`
                <div class="flex items-center gap-2 card-flat p-3"><div class="av av-md">${p[0]}</div><div><div class="text-sm font-semibold">${p[1]}</div><div class="text-xs text-ink-faint">${p[2]}</div></div></div>
              `).join('')}
            </div>
          </div>
        </div>
      `;
    }
  };

  const mydocs = {
    render() {
      return `
        <div class="max-w-4xl mx-auto px-5 py-10">
          <h1 class="font-display font-bold text-2xl text-ink">My documents</h1>
          <p class="text-sm text-ink-mute mt-1">All documents shared during your application.</p>
          <div class="card mt-5 overflow-hidden">
            <table class="tbl">
              <thead><tr><th>Document</th><th>Uploaded</th><th>Status</th><th></th></tr></thead>
              <tbody>
                ${[
                  ['CV_Aditya_Wijaya.pdf','2026-04-23','verified'],
                  ['Ijazah_S1.pdf','2026-04-23','verified'],
                  ['Ijazah_S2.pdf','2026-04-23','verified'],
                  ['Transkrip_S2.pdf','2026-04-23','verified'],
                  ['KTP.jpg','2026-04-23','verified'],
                  ['Sertifikat_TOEFL.pdf','2026-04-25','verified'],
                ].map(r=>`
                  <tr>
                    <td data-label="Document"><div class="flex items-center gap-2">${ic('file-text','w-4 h-4 text-ink-mute')}<span class="font-semibold">${r[0]}</span></div></td>
                    <td data-label="Uploaded">${H.fmtDate(r[1])}</td>
                    <td data-label="Status">${U.pill('Verified','green',true)}</td>
                    <td><button class="btn btn-ghost btn-sm">${ic('download','w-3.5 h-3.5')}</button></td>
                  </tr>`).join('')}
              </tbody>
            </table>
          </div>
        </div>
      `;
    }
  };

  /* =================================================================
     STAFF — Dashboard (varies by role)
  ================================================================= */
  const dashboard = {
    render() {
      const r = STATE.role;
      if (r === 'hr')        return dashboard.hr();
      if (r === 'principal') return dashboard.principal();
      if (r === 'depthead')  return dashboard.depthead();
      if (r === 'mentor')    return dashboard.mentor();
      if (r === 'finance')   return dashboard.finance();
      if (r === 'yayasan')   return dashboard.yayasan();
      if (r === 'it')        return dashboard.it();
      return dashboard.hr();
    },
    hr() {
      const cnt = H.countByStage();
      const upcoming = D.INTERVIEWS.filter(i => i.status === 'scheduled');
      return `
        <div class="flex items-end justify-between flex-wrap gap-3 mb-5">
          <div>
            <div class="text-xs text-ink-faint font-semibold uppercase tracking-wider">${H.fmtDate(H.today)} · Recruitment overview</div>
            <h1 class="font-display font-bold text-2xl text-ink">Good morning, Sri.</h1>
          </div>
          <div class="flex gap-2">
            <button class="btn btn-ghost btn-sm" data-action="export">${ic('download','w-3.5 h-3.5')} Export</button>
            <button class="btn btn-primary btn-sm" data-action="new-vacancy">${ic('plus','w-3.5 h-3.5')} New vacancy</button>
          </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
          ${U.stat({label:'Active vacancies', value: D.VACANCIES.filter(v=>v.status!=='closed').length, sub:`${D.VACANCIES.filter(v=>v.status==='closing').length} closing this month`, icon:'briefcase', tone:'brown'})}
          ${U.stat({label:'Total candidates', value: D.CANDIDATES.filter(c=>c.stage!=='rejected'&&c.stage!=='active').length, sub:'in active pipeline', icon:'users-round', tone:'blue', delta: 12})}
          ${U.stat({label:'Pending interviews', value: upcoming.length, sub:'next 7 days', icon:'video', tone:'gold'})}
          ${U.stat({label:'Awaiting Yayasan', value: D.CANDIDATES.filter(c=>c.stage==='yayasan').length, sub:'board review needed', icon:'landmark', tone:'maroon'})}
        </div>

        <div class="grid lg:grid-cols-3 gap-4 mb-4">
          <div class="card p-5 lg:col-span-2">
            ${U.sectionTitle('Pipeline by stage', `<button class="btn btn-ghost btn-sm" data-route="pipeline">View pipeline ${ic('arrow-right','w-3.5 h-3.5')}</button>`)}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
              ${D.STAGES.filter(s=>s.id!=='rejected').map((s,i)=>{
                const colors = ['gray','blue','blue','gold','violet','green','maroon','brown','green'];
                return `
                  <div class="card-flat p-3 cursor-pointer hover:border-slate-300" data-route="pipeline">
                    <div class="text-[10px] uppercase tracking-wider font-bold text-ink-faint">${s.label}</div>
                    <div class="font-display font-bold text-2xl text-ink mt-1">${cnt[s.id]}</div>
                    <div class="bar mt-2"><i style="width:${Math.min(100, cnt[s.id]*10)}%; background: var(--brand-dark)"></i></div>
                  </div>`;
              }).join('')}
            </div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Hiring funnel (90 d)')}
            <div class="funnel">
              ${D.ANALYTICS.funnel.map(f=>{
                const max = D.ANALYTICS.funnel[0].n;
                return `<div class="row"><div class="lbl">${f.stage}</div><div class="bar"><i style="width:${f.n/max*100}%"></i></div><div class="n">${f.n}</div></div>`;
              }).join('')}
            </div>
          </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-4 mb-4">
          <div class="card p-5">
            ${U.sectionTitle('Upcoming interviews', `<button class="btn btn-ghost btn-sm" data-route="interviews">All</button>`)}
            <div class="space-y-3">
              ${upcoming.slice(0,4).map(i=>{
                const c = H.candidate(i.candidateId);
                return `
                  <div class="flex items-center gap-3 cursor-pointer" data-route="candidate" data-id="${i.candidateId}">
                    <div class="text-center w-12 flex-shrink-0">
                      <div class="text-[10px] uppercase font-bold text-ink-faint">${new Date(i.date).toLocaleDateString('en',{month:'short'})}</div>
                      <div class="font-display font-bold text-lg text-ink">${new Date(i.date).getDate()}</div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="font-semibold text-sm">${c?.name || 'Candidate'}</div>
                      <div class="text-xs text-ink-mute">${i.type} · ${i.time} · ${i.room}</div>
                    </div>
                    ${U.pill('Confirmed','green',true)}
                  </div>`;
              }).join('') || '<div class="text-sm text-ink-mute">No upcoming.</div>'}
            </div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Action queue')}
            <div class="space-y-2">
              ${[
                { ic:'wallet',         t:'2 deposits awaiting verification', s:'Finance escalated', tone:'amber' },
                { ic:'landmark',       t:'1 candidate awaiting Yayasan',     s:'Citra Maharani', tone:'maroon' },
                { ic:'file-warning',   t:'4 documents missing reupload',     s:'Across 3 candidates', tone:'red' },
                { ic:'graduation-cap', t:'OPL session evaluation due',       s:'Aditya Wijaya · S3', tone:'brown' },
              ].map(a=>`
                <div class="flex items-center gap-3 card-flat p-3 cursor-pointer hover:border-slate-300">
                  <div class="w-9 h-9 rounded-md flex items-center justify-center text-${a.tone==='amber'?'amber':a.tone==='maroon'?'maroon':a.tone==='red'?'red':'brand'}-dark bg-${a.tone==='amber'?'amber':a.tone==='maroon'?'pink':a.tone==='red'?'red':'cream'}-50">${ic(a.ic,'w-4 h-4')}</div>
                  <div class="flex-1"><div class="text-sm font-semibold">${a.t}</div><div class="text-xs text-ink-mute">${a.s}</div></div>
                  ${ic('chevron-right','w-4 h-4 text-slate-400')}
                </div>`).join('')}
            </div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Hires this year', `<button class="btn btn-ghost btn-sm" data-route="analytics">Analytics</button>`)}
            <div class="chart-wrap sm"><canvas id="dashChart"></canvas></div>
          </div>
        </div>

        <div class="card p-5">
          ${U.sectionTitle('Latest activity', `<button class="btn btn-ghost btn-sm" data-route="audit">Audit log</button>`)}
          <table class="tbl tbl-compact">
            <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Target</th><th>Note</th></tr></thead>
            <tbody>
              ${D.AUDIT.slice(0,6).map(a=>`
                <tr>
                  <td data-label="Time" class="text-ink-mute">${a.ts}</td>
                  <td data-label="User"><div class="flex items-center gap-2"><div class="av av-xs">${H.initials(a.user)}</div><span>${a.user}</span></div></td>
                  <td data-label="Action">${U.pill(a.action,'gray')}</td>
                  <td data-label="Target" class="font-mono text-xs">${a.target}</td>
                  <td data-label="Note" class="text-ink-mute">${a.note}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        </div>
      `;
    },
    principal() {
      return `
        <div class="mb-5">
          <div class="text-xs text-ink-faint font-semibold uppercase tracking-wider">${H.fmtDate(H.today)}</div>
          <h1 class="font-display font-bold text-2xl text-ink">Principal Dashboard</h1>
          <p class="text-sm text-ink-mute">Sutomo SMA · Hiring & onboarding overview</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
          ${U.stat({label:'Awaiting my decision', value: 4, sub:'interview & OPL evaluations', icon:'clipboard-check', tone:'brown'})}
          ${U.stat({label:'Active OPL teachers', value: 1, sub:'1 mentor, 7 sessions remaining', icon:'graduation-cap', tone:'green'})}
          ${U.stat({label:'Probation passing', value:'5 / 6', sub:'90-day track', icon:'timer', tone:'gold'})}
          ${U.stat({label:'Faculty satisfaction', value:'4.7 / 5', sub:'Q1 2026 survey', icon:'star', tone:'gold'})}
        </div>
        <div class="grid lg:grid-cols-2 gap-4">
          <div class="card p-5">
            ${U.sectionTitle('Decisions awaiting you')}
            <div class="space-y-2">
              ${D.CANDIDATES.filter(c=>['interview','opl','psycho'].includes(c.stage)).slice(0,4).map(c=>{
                const v = H.vacancy(c.vacancyId);
                return `
                  <div class="card-flat p-3 flex items-center gap-3 cursor-pointer hover:border-slate-300" data-route="candidate" data-id="${c.id}">
                    <div class="av av-md">${H.initials(c.name)}</div>
                    <div class="flex-1 min-w-0"><div class="font-semibold text-sm">${c.name}</div><div class="text-xs text-ink-mute">${v.title} · ${H.stageLabel(c.stage)}</div></div>
                    <button class="btn btn-soft btn-sm">Review</button>
                  </div>`;
              }).join('')}
            </div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('OPL progress')}
            ${D.CANDIDATES.filter(c=>c.stage==='opl').map(c=>{
              const sess = c.opl.sessions; const done = sess.filter(s=>s.status==='done').length;
              return `
                <div class="card-flat p-4">
                  <div class="flex items-center gap-3">
                    <div class="av av-md">${H.initials(c.name)}</div>
                    <div class="flex-1"><div class="font-semibold text-sm">${c.name}</div><div class="text-xs text-ink-mute">${sess[0].mentor} · 90-day OPL</div></div>
                    ${U.ring(Math.round(done/sess.length*100), 56)}
                  </div>
                  <div class="text-xs text-ink-mute mt-3">${done} of ${sess.length} sessions completed · avg score ${Math.round(sess.filter(s=>s.score).reduce((a,b)=>a+b.score,0)/Math.max(1,sess.filter(s=>s.score).length))}/100</div>
                  ${U.bar(done/sess.length*100,'green')}
                </div>`;
            }).join('')}
          </div>
        </div>
      `;
    },
    depthead() {
      return `
        <div class="mb-5">
          <h1 class="font-display font-bold text-2xl text-ink">Mathematics Department</h1>
          <p class="text-sm text-ink-mute">Hiring pipeline for your subject area.</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
          ${U.stat({label:'Math candidates', value: D.CANDIDATES.filter(c=>{const v=H.vacancy(c.vacancyId);return v?.dept==='Mathematics';}).length, icon:'sigma', tone:'brown'})}
          ${U.stat({label:'Need my evaluation', value: 2, icon:'clipboard-check', tone:'amber'})}
          ${U.stat({label:'Open vacancies', value: D.VACANCIES.filter(v=>v.dept==='Mathematics'&&v.status!=='closed').length, icon:'briefcase', tone:'gold'})}
          ${U.stat({label:'Current faculty', value: 8, icon:'users-round', tone:'blue'})}
        </div>
        ${pipeline.render(true)}
      `;
    },
    mentor() {
      const mentees = D.CANDIDATES.filter(c=>c.stage==='opl');
      return `
        <div class="mb-5">
          <h1 class="font-display font-bold text-2xl text-ink">My Mentees</h1>
          <p class="text-sm text-ink-mute">Track OPL progress and submit session evaluations.</p>
        </div>
        <div class="grid lg:grid-cols-2 gap-4">
          ${mentees.map(c=>{
            const sess = c.opl.sessions; const done = sess.filter(s=>s.status==='done').length;
            const next = sess.find(s=>s.status==='scheduled');
            return `
              <div class="card p-5">
                <div class="flex items-start gap-3">
                  <div class="av av-lg">${H.initials(c.name)}</div>
                  <div class="flex-1">
                    <div class="font-display font-semibold text-base">${c.name}</div>
                    <div class="text-xs text-ink-mute">${H.vacancy(c.vacancyId).title} · ${c.subjects.join(', ')}</div>
                    <div class="flex flex-wrap gap-1.5 mt-2">${U.pill('OPL Day '+H.daysBetween(c.opl.start,H.today),'brown')}${U.pill(`${done}/${sess.length} sessions`,'green')}</div>
                  </div>
                  ${U.ring(Math.round(done/sess.length*100),58)}
                </div>
                ${next ? `
                  <div class="card-flat p-3 mt-4">
                    <div class="text-xs text-ink-faint font-semibold uppercase tracking-wider">Next session</div>
                    <div class="font-semibold text-sm mt-1">Session ${next.n} · ${next.topic}</div>
                    <div class="text-xs text-ink-mute">${H.fmtDate(next.date)}</div>
                  </div>` : ''}
                <div class="flex gap-2 mt-3">
                  <button class="btn btn-primary btn-sm flex-1" data-action="opl-eval" data-id="${c.id}">${ic('clipboard-check','w-3.5 h-3.5')} Submit evaluation</button>
                  <button class="btn btn-ghost btn-sm" data-route="candidate" data-id="${c.id}">View profile</button>
                </div>
              </div>`;
          }).join('')}
        </div>
      `;
    },
    finance() {
      const pend = D.DEPOSITS.filter(d=>d.status==='pending');
      const ver  = D.DEPOSITS.filter(d=>d.status==='verified');
      return `
        <div class="mb-5">
          <h1 class="font-display font-bold text-2xl text-ink">Finance Dashboard</h1>
          <p class="text-sm text-ink-mute">Recruitment deposits & refunds</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
          ${U.stat({label:'Pending verification', value: pend.length, sub:'awaiting bank confirmation', icon:'clock', tone:'amber'})}
          ${U.stat({label:'Verified deposits', value: ver.length, sub: H.fmtIDR(ver.reduce((a,b)=>a+b.amount,0)), icon:'check-circle-2', tone:'green'})}
          ${U.stat({label:'Refund eligible', value: D.DEPOSITS.filter(d=>d.refundEligible).length, sub:'OPL completed', icon:'undo-2', tone:'brown'})}
          ${U.stat({label:'Held this month', value: H.fmtIDR(45000000), sub:'+12% vs last month', icon:'wallet', tone:'gold', delta: 12})}
        </div>
        ${deposits.render()}
      `;
    },
    yayasan() {
      const queue = D.CANDIDATES.filter(c=>c.stage==='yayasan');
      return `
        <div class="mb-5">
          <h1 class="font-display font-bold text-2xl text-ink">Yayasan Executive Dashboard</h1>
          <p class="text-sm text-ink-mute">Board-level visibility on hiring & teacher lifecycle.</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
          ${U.stat({label:'Awaiting board approval', value: queue.length, sub:'high-priority queue', icon:'landmark', tone:'maroon'})}
          ${U.stat({label:'Hires YTD', value: 11, sub:'Target 18 by EOY', icon:'check-circle-2', tone:'green'})}
          ${U.stat({label:'Active faculty', value: 327, sub:'across 4 campuses', icon:'users-round', tone:'brown'})}
          ${U.stat({label:'Y1 retention', value:'93%', sub:'+2 pts vs 2025', icon:'trending-up', tone:'gold', delta: 2})}
        </div>
        <div class="grid lg:grid-cols-3 gap-4">
          <div class="card p-5 lg:col-span-2">
            ${U.sectionTitle('Pending board decisions', `<button class="btn btn-ghost btn-sm" data-route="yayasan">All</button>`)}
            <div class="space-y-2">
              ${queue.map(c=>`
                <div class="card-flat p-3 flex items-center gap-3 cursor-pointer hover:border-slate-300" data-route="candidate" data-id="${c.id}">
                  <div class="av av-md">${H.initials(c.name)}</div>
                  <div class="flex-1"><div class="font-semibold text-sm">${c.name}</div><div class="text-xs text-ink-mute">${H.vacancy(c.vacancyId).title} · queued ${H.fmtDate(c.yayasan?.queuedAt||H.today)}</div></div>
                  ${U.pill('Strong Hire','green')}
                </div>`).join('') || '<div class="text-sm text-ink-mute">No items in queue.</div>'}
            </div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Hiring trend')}
            <div class="chart-wrap sm"><canvas id="dashChart"></canvas></div>
          </div>
        </div>
      `;
    },
    it() {
      return `
        <div class="mb-5">
          <h1 class="font-display font-bold text-2xl text-ink">IT Operations</h1>
          <p class="text-sm text-ink-mute">Audit, permissions & access governance.</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
          ${U.stat({label:'Active users', value: 327, sub:'+8 this week', icon:'users-round', tone:'blue'})}
          ${U.stat({label:'Audit events (24h)', value: 142, sub:'all clean', icon:'shield-check', tone:'green'})}
          ${U.stat({label:'2FA adoption', value:'94%', sub:'Mandatory by Aug', icon:'key-round', tone:'gold'})}
          ${U.stat({label:'Failed logins (24h)', value: 6, sub:'no anomalies detected', icon:'alert-triangle', tone:'amber'})}
        </div>
        ${audit.render()}
      `;
    },
  };

  /* =================================================================
     PIPELINE — Kanban
  ================================================================= */
  const pipeline = {
    render(embed) {
      const cols = D.STAGES.filter(s => s.id !== 'rejected');
      const grouped = {}; cols.forEach(c => grouped[c.id] = []);
      grouped['rejected'] = [];
      D.CANDIDATES.forEach(c => { (grouped[c.stage] || (grouped[c.stage]=[])).push(c); });
      return `
        ${embed ? '' : `
          <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
            <div>
              <h1 class="font-display font-bold text-2xl text-ink">ATS Pipeline</h1>
              <p class="text-sm text-ink-mute">Drag-style kanban view of every active candidate</p>
            </div>
            <div class="flex flex-wrap gap-2">
              <input class="input" style="width:240px" placeholder="Search candidate..."/>
              <select class="select" style="width:auto"><option>All departments</option>${D.DEPTS.map(d=>`<option>${d}</option>`).join('')}</select>
              <select class="select" style="width:auto"><option>All campuses</option>${D.CAMPUSES.map(c=>`<option>${c.name}</option>`).join('')}</select>
              <button class="btn btn-soft btn-sm">${ic('filter','w-3.5 h-3.5')} Filters</button>
              <button class="btn btn-ghost btn-sm" data-route="candidates">${ic('table','w-3.5 h-3.5')} Table view</button>
            </div>
          </div>
        `}
        <div class="kanban">
          ${cols.map(col => {
            const list = grouped[col.id] || [];
            return `
              <div class="kcol">
                <div class="kcol-h">
                  <span class="t">${col.label}</span>
                  <span class="c">${list.length}</span>
                  <button class="iconbtn ml-auto">${ic('plus','w-3.5 h-3.5')}</button>
                </div>
                <div class="kcol-body">
                  ${list.map(c => {
                    const v = H.vacancy(c.vacancyId);
                    const score = c.score ? Math.round(((c.score.written||0)+(c.score.interview||0)+(c.score.micro||0)) / [c.score.written,c.score.interview,c.score.micro].filter(Boolean).length) : null;
                    return `
                      <div class="kcard" data-route="candidate" data-id="${c.id}">
                        <div class="top">
                          <div class="av av-sm">${H.initials(c.name)}</div>
                          <div class="flex-1 min-w-0">
                            <div class="name truncate">${c.name}</div>
                            <div class="sub truncate">${v?.title || ''}</div>
                          </div>
                          ${score ? `<span class="score">${score}</span>` : ''}
                        </div>
                        <div class="meta">
                          ${c.priority === 'high' ? U.pill('High priority','maroon') : ''}
                          ${c.deposit?.status === 'verified' ? U.pill('Deposit ✓','green') : c.deposit?.status === 'pending' ? U.pill('Deposit ⏳','amber') : ''}
                          ${c.subjects?.[0] ? U.pill(c.subjects[0],'gray') : ''}
                        </div>
                      </div>`;
                  }).join('')}
                  ${list.length === 0 ? '<div class="text-xs text-slate-400 text-center py-6">No candidates</div>' : ''}
                </div>
              </div>`;
          }).join('')}
          <!-- rejected column -->
          <div class="kcol" style="background:#FEE2E2;border-color:#FCA5A5">
            <div class="kcol-h"><span class="t" style="color:#991B1B">Rejected</span><span class="c">${grouped['rejected'].length}</span></div>
            <div class="kcol-body">
              ${grouped['rejected'].map(c=>`
                <div class="kcard" data-route="candidate" data-id="${c.id}">
                  <div class="top"><div class="av av-sm">${H.initials(c.name)}</div><div class="flex-1 min-w-0"><div class="name truncate">${c.name}</div><div class="sub truncate">${c.rejectedReason||''}</div></div></div>
                </div>`).join('')}
            </div>
          </div>
        </div>
      `;
    }
  };

  /* =================================================================
     CANDIDATES — table view
  ================================================================= */
  const candidates = {
    render() {
      return `
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
          <div>
            <h1 class="font-display font-bold text-2xl text-ink">Candidates</h1>
            <p class="text-sm text-ink-mute">${D.CANDIDATES.length} candidates across all stages</p>
          </div>
          <div class="flex gap-2">
            <input class="input" style="width:240px" placeholder="Search..."/>
            <button class="btn btn-soft btn-sm">${ic('filter','w-3.5 h-3.5')} Filter</button>
            <button class="btn btn-ghost btn-sm" data-route="pipeline">${ic('kanban','w-3.5 h-3.5')} Kanban</button>
            <button class="btn btn-primary btn-sm">${ic('download','w-3.5 h-3.5')} Export CSV</button>
          </div>
        </div>
        <div class="card overflow-hidden">
          <table class="tbl">
            <thead>
              <tr><th></th><th>Candidate</th><th>Vacancy</th><th>Stage</th><th>Score</th><th>Deposit</th><th>Applied</th><th></th></tr>
            </thead>
            <tbody>
              ${D.CANDIDATES.map(c=>{
                const v = H.vacancy(c.vacancyId);
                const score = c.score ? Math.round(Object.values(c.score).reduce((a,b)=>a+b,0)/Object.values(c.score).length) : null;
                const stagePill = {
                  applied:'gray', screening:'blue', written:'blue', interview:'gold', psycho:'violet',
                  medical:'green', yayasan:'maroon', opl:'brown', active:'green', rejected:'red'
                }[c.stage];
                return `
                  <tr data-route="candidate" data-id="${c.id}" style="cursor:pointer">
                    <td><div class="av av-sm">${H.initials(c.name)}</div></td>
                    <td data-label="Candidate"><div class="font-semibold">${c.name}</div><div class="text-xs text-ink-faint">${c.subjects?.join(', ') || ''}</div></td>
                    <td data-label="Vacancy"><div class="font-mono text-xs text-ink-mute">${c.vacancyId}</div><div class="text-xs">${v?.title || ''}</div></td>
                    <td data-label="Stage">${U.pill(H.stageLabel(c.stage), stagePill, true)}</td>
                    <td data-label="Score">${score ? `<span class="font-bold">${score}</span>` : '—'}</td>
                    <td data-label="Deposit">${c.deposit ? U.pill(c.deposit.status, c.deposit.status==='verified'?'green':c.deposit.status==='pending'?'amber':'gray') : '—'}</td>
                    <td data-label="Applied" class="text-xs text-ink-mute">${H.fmtDate(c.appliedAt)}</td>
                    <td><button class="iconbtn">${ic('more-horizontal','w-4 h-4')}</button></td>
                  </tr>`;
              }).join('')}
            </tbody>
          </table>
        </div>
      `;
    }
  };

  /* =================================================================
     CANDIDATE PROFILE — full screen, tabbed
  ================================================================= */
  const candidate = {
    render() {
      const c = H.candidate(STATE.activeCandidateId) || D.CANDIDATES[0];
      const v = H.vacancy(c.vacancyId);
      const tab = STATE.candidateTab;
      const stages = D.STAGES.filter(s=>s.id!=='rejected');
      const tabs = [
        { id:'overview',  l:'Overview' },
        { id:'docs',      l:'Documents' },
        { id:'assess',    l:'Assessments' },
        { id:'interview', l:'Interview Notes' },
        { id:'deposit',   l:'Deposit' },
        { id:'medical',   l:'Medical' },
        { id:'psycho',    l:'Psycho' },
        { id:'timeline',  l:'Timeline' },
        { id:'audit',     l:'Audit' },
      ];

      return `
        <button class="text-sm text-ink-mute hover:text-ink mb-4 inline-flex items-center gap-1" data-route="pipeline">${ic('arrow-left','w-4 h-4')} Back to pipeline</button>

        <div class="card p-6 mb-4">
          <div class="flex items-start gap-4 flex-wrap">
            <div class="av av-xl">${H.initials(c.name)}</div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <h1 class="font-display font-bold text-2xl text-ink">${c.name}</h1>
                ${c.priority === 'high' ? U.pill('High priority','maroon') : ''}
                ${U.pill(H.stageLabel(c.stage),'brown',true)}
              </div>
              <div class="text-sm text-ink-mute mt-1">${c.education}</div>
              <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-ink-mute">
                <span>${ic('briefcase','w-3 h-3 inline')} ${v?.title}</span>
                <span>${ic('mail','w-3 h-3 inline')} ${c.email||'—'}</span>
                <span>${ic('phone','w-3 h-3 inline')} ${c.phone||'—'}</span>
                <span>${ic('calendar','w-3 h-3 inline')} Applied ${H.fmtDate(c.appliedAt)}</span>
                <span class="font-mono">${c.id}</span>
              </div>
            </div>
            <div class="flex gap-2">
              <button class="btn btn-ghost btn-sm">${ic('mail','w-3.5 h-3.5')} Email</button>
              <button class="btn btn-ghost btn-sm">${ic('message-circle','w-3.5 h-3.5')} WhatsApp</button>
              <button class="btn btn-soft btn-sm" data-action="cand-reject" data-id="${c.id}">Reject</button>
              <button class="btn btn-primary btn-sm" data-action="cand-advance" data-id="${c.id}">${ic('arrow-right','w-3.5 h-3.5')} Advance stage</button>
            </div>
          </div>
          <div class="divider"></div>
          <div class="overflow-x-auto pb-1">${U.stepper(stages, c.stage)}</div>
        </div>

        <div class="card p-0">
          <div class="tab-bar px-2 overflow-x-auto">
            ${tabs.map(t=>`<div class="tab ${tab===t.id?'active':''}" data-tab="${t.id}">${t.l}</div>`).join('')}
          </div>
          <div class="p-5">
            ${candidate.tabHTML(tab, c, v)}
          </div>
        </div>
      `;
    },
    tabHTML(tab, c, v) {
      if (tab === 'overview') return `
        <div class="grid lg:grid-cols-3 gap-4">
          <div class="lg:col-span-2 space-y-4">
            <div>
              <div class="text-xs uppercase font-bold text-ink-faint tracking-wider mb-1">Candidate summary</div>
              <p class="text-sm text-ink-soft leading-relaxed">${c.years} years of teaching experience in ${(c.subjects||[]).join(', ')}. Applying for <b>${v.title}</b>. Strong written-test performance and consistent micro-teaching evaluations.</p>
            </div>
            <div>
              <div class="text-xs uppercase font-bold text-ink-faint tracking-wider mb-2">Internal recommendation</div>
              <div class="card-flat p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center">${ic('thumbs-up','w-5 h-5')}</div>
                <div>
                  <div class="font-display font-semibold text-base">Strong Hire</div>
                  <div class="text-xs text-ink-mute">Per panel evaluation, May 4 2026 · 3 of 3 evaluators agreed.</div>
                </div>
              </div>
            </div>
            <div>
              <div class="text-xs uppercase font-bold text-ink-faint tracking-wider mb-2">Internal notes (3)</div>
              <div class="space-y-2">
                ${[
                  { who:'Maria Hartanto', when:'2026-05-13', t:'Excellent grasp of pedagogical sequencing during micro-teaching. Should mentor olympiad team.' },
                  { who:'Drs. Budi Santoso', when:'2026-05-08', t:'Recommend OPL with Rini as mentor. Watch pacing in differentiated groups.' },
                  { who:'Sri Lestari', when:'2026-04-26', t:'Documents complete. TOEFL ITP 547 — exceeds requirement.' },
                ].map(n=>`
                  <div class="card-flat p-3">
                    <div class="flex items-center gap-2 mb-1"><div class="av av-xs">${H.initials(n.who)}</div><span class="text-xs font-semibold">${n.who}</span><span class="text-[10px] text-ink-faint">${H.fmtDate(n.when)}</span></div>
                    <div class="text-sm text-ink-soft">${n.t}</div>
                  </div>`).join('')}
              </div>
              <div class="mt-3"><textarea class="textarea" rows="2" placeholder="Add internal note (visible to hiring team only)…"></textarea><button class="btn btn-soft btn-sm mt-2">${ic('send','w-3.5 h-3.5')} Post note</button></div>
            </div>
          </div>
          <div class="space-y-3">
            <div class="card-flat p-4">
              <div class="text-xs text-ink-faint font-semibold uppercase">Scores</div>
              <div class="grid grid-cols-3 gap-2 mt-2">
                ${[['Written',c.score?.written],['Interview',c.score?.interview],['Micro',c.score?.micro]].map(s=>`
                  <div class="text-center">
                    <div class="font-display font-bold text-xl text-ink">${s[1]||'—'}</div>
                    <div class="text-[10px] uppercase text-ink-faint">${s[0]}</div>
                  </div>`).join('')}
              </div>
            </div>
            <div class="card-flat p-4">
              <div class="text-xs text-ink-faint font-semibold uppercase mb-2">Verifications</div>
              <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between"><span class="flex items-center gap-2">${ic('wallet','w-4 h-4 text-ink-mute')} Deposit</span>${c.deposit?U.pill(c.deposit.status, c.deposit.status==='verified'?'green':'amber'):'—'}</div>
                <div class="flex items-center justify-between"><span class="flex items-center gap-2">${ic('brain','w-4 h-4 text-ink-mute')} Psycho</span>${c.psycho?U.pill(c.psycho.status, c.psycho.status==='passed'?'green':'amber'):'—'}</div>
                <div class="flex items-center justify-between"><span class="flex items-center gap-2">${ic('stethoscope','w-4 h-4 text-ink-mute')} Medical</span>${c.medical?U.pill(c.medical.status, c.medical.status==='passed'?'green':'amber'):'—'}</div>
                <div class="flex items-center justify-between"><span class="flex items-center gap-2">${ic('landmark','w-4 h-4 text-ink-mute')} Yayasan</span>${c.yayasan?U.pill(c.yayasan.status, c.yayasan.status==='approved'?'green':'amber'):'—'}</div>
              </div>
            </div>
            <div class="card-flat p-4">
              <div class="text-xs text-ink-faint font-semibold uppercase mb-2">Vacancy</div>
              <div class="font-semibold text-sm">${v.title}</div>
              <div class="text-xs text-ink-mute mt-1">${v.id} · ${H.campus(v.campus).name}</div>
              <button class="btn btn-ghost btn-sm w-full mt-3" data-route="vacancy" data-id="${v.id}">View vacancy</button>
            </div>
          </div>
        </div>
      `;
      if (tab === 'docs') return `
        <table class="tbl">
          <thead><tr><th>Document</th><th>Uploaded</th><th>Verified by</th><th>Status</th><th></th></tr></thead>
          <tbody>
            ${[
              ['CV.pdf','2026-04-23','Sri Lestari','verified'],
              ['Ijazah_S2.pdf','2026-04-23','Sri Lestari','verified'],
              ['Transkrip.pdf','2026-04-23','Sri Lestari','verified'],
              ['Sertifikat_TOEFL.pdf','2026-04-25','Sri Lestari','verified'],
              ['KTP.jpg','2026-04-23','Sri Lestari','verified'],
              ['Teaching_Portfolio.pdf','2026-04-26','—','pending'],
            ].map(r=>`
              <tr><td data-label="Document"><div class="flex items-center gap-2">${ic('file-text','w-4 h-4 text-ink-mute')}<span class="font-semibold">${r[0]}</span></div></td>
              <td data-label="Uploaded">${H.fmtDate(r[1])}</td><td data-label="Verified by">${r[2]}</td>
              <td data-label="Status">${U.pill(r[3], r[3]==='verified'?'green':'amber')}</td>
              <td><button class="btn btn-ghost btn-sm">${ic('eye','w-3.5 h-3.5')}</button><button class="btn btn-ghost btn-sm">${ic('download','w-3.5 h-3.5')}</button></td></tr>`).join('')}
          </tbody>
        </table>
      `;
      if (tab === 'assess') return `
        <div class="grid md:grid-cols-2 gap-4">
          <div class="card-flat p-4">
            <div class="text-xs uppercase font-bold text-ink-faint tracking-wider mb-2">Written test</div>
            <div class="font-display font-bold text-3xl">${c.score?.written ?? '—'}<span class="text-base text-ink-mute font-normal"> / 100</span></div>
            <div class="text-xs text-ink-mute mt-1">May 2 2026 · pass threshold 70</div>
            <div class="mt-3 text-sm">Algebra: 92 · Geometry: 84 · Statistics: 88 · Calculus: 84</div>
          </div>
          <div class="card-flat p-4">
            <div class="text-xs uppercase font-bold text-ink-faint tracking-wider mb-2">Micro-teaching</div>
            <div class="font-display font-bold text-3xl">${c.score?.micro ?? '—'}<span class="text-base text-ink-mute font-normal"> / 100</span></div>
            <div class="text-xs text-ink-mute mt-1">May 8 2026 · 30 min lesson on Quadratics</div>
          </div>
          <div class="card-flat p-4 md:col-span-2">
            <div class="text-xs uppercase font-bold text-ink-faint tracking-wider mb-3">Interview rubric</div>
            <table class="tbl tbl-compact">
              <thead><tr><th>Criterion</th><th>Weight</th><th>Score</th><th>Notes</th></tr></thead>
              <tbody>
                ${D.EVAL_RUBRIC.map((r,i)=>`
                  <tr><td>${r.label}</td><td>${r.weight}%</td><td><span class="font-bold">${[88,86,90,87,89,82][i]}</span></td><td class="text-ink-mute">${['Deep content knowledge','Clear classroom routines','Good pacing','Engaging Q&A','Inquiry-based','TOEFL 547'][i]}</td></tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      `;
      if (tab === 'interview') return `
        <div class="space-y-3">
          ${D.INTERVIEWS.filter(i=>i.candidateId===c.id || i.candidateId==='C-1001').map(i=>`
            <div class="card-flat p-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-md bg-cream text-brand-dark flex items-center justify-center">${ic('video','w-5 h-5')}</div>
                <div class="flex-1">
                  <div class="font-display font-semibold text-base">${i.type}</div>
                  <div class="text-xs text-ink-mute">${H.fmtDate(i.date)} · ${i.time} · ${i.room} · Panel: ${i.panel.join(', ')}</div>
                </div>
                ${i.recommendation ? U.pill(i.recommendation,'green') : U.pill(i.status,'blue')}
              </div>
              ${i.recommendation ? `
                <div class="divider"></div>
                <div class="text-sm text-ink-soft">"Excellent subject mastery and warm classroom presence. Recommend offer with OPL track."</div>
              ` : ''}
            </div>`).join('')}
          <button class="btn btn-soft btn-sm">${ic('plus','w-3.5 h-3.5')} Schedule new interview</button>
        </div>
      `;
      if (tab === 'deposit') return `
        ${c.deposit ? `
          <div class="grid md:grid-cols-3 gap-4">
            <div class="card-flat p-4 md:col-span-2">
              <div class="flex items-center justify-between mb-3">
                <div class="text-xs uppercase font-bold text-ink-faint tracking-wider">Deposit detail</div>
                ${U.pill(c.deposit.status, c.deposit.status==='verified'?'green':'amber')}
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div><div class="text-xs text-ink-faint">Amount</div><div class="font-display font-bold text-xl">${H.fmtIDR(c.deposit.amount)}</div></div>
                <div><div class="text-xs text-ink-faint">Paid date</div><div class="font-semibold">${H.fmtDate(c.deposit.paidAt)}</div></div>
                <div><div class="text-xs text-ink-faint">Bank</div><div class="font-semibold">BCA · 0123-456-789</div></div>
                <div><div class="text-xs text-ink-faint">Refund eligible</div><div>${c.deposit.refundEligible ? U.pill('Yes','green') : U.pill('Not yet','gray')}</div></div>
              </div>
            </div>
            <div class="card-flat p-4">
              <div class="text-xs uppercase font-bold text-ink-faint tracking-wider mb-2">Receipt</div>
              <div class="aspect-[3/4] bg-white border border-slate-200 rounded-md flex items-center justify-center text-slate-400">${ic('file-text','w-10 h-10')}</div>
              <button class="btn btn-ghost btn-sm w-full mt-2">${ic('download','w-3.5 h-3.5')} Download PDF</button>
            </div>
          </div>` : '<div class="text-sm text-ink-mute">No deposit recorded yet.</div>'}
      `;
      if (tab === 'medical' || tab === 'psycho') {
        const isMed = tab === 'medical';
        const data = isMed ? c.medical : c.psycho;
        return `
          <div class="sec-strip confidential mb-4">
            ${ic('lock','w-4 h-4')} <b>Restricted access</b> — ${isMed?'Medical':'Psychological'} records visible to authorized roles only. All views are audit-logged.
          </div>
          ${data ? `
            <div class="card-flat p-5 max-w-xl">
              <div class="flex items-center justify-between mb-4">
                <div class="font-display font-semibold text-base">${isMed?'Medical clearance':'Psychological evaluation'}</div>
                ${U.pill(data.status, data.status==='passed'?'green':'amber')}
              </div>
              <div class="grid grid-cols-2 gap-3 text-sm">
                <div><div class="text-xs text-ink-faint">Date</div><div class="font-semibold">${H.fmtDate(data.date||data.scheduledAt)}</div></div>
                <div><div class="text-xs text-ink-faint">${isMed?'Clinic':'Counselor'}</div><div class="font-semibold">${data.clinic||data.counselor||'—'}</div></div>
              </div>
              <div class="divider"></div>
              <div class="text-xs text-ink-faint">Confidential summary</div>
              <p class="text-sm text-ink-soft mt-1 italic">Document available to viewer-role users via secure preview only. No download permitted.</p>
              <div class="flex gap-2 mt-4">
                <button class="btn btn-ghost btn-sm">${ic('eye','w-3.5 h-3.5')} Secure preview</button>
                <button class="btn btn-ghost btn-sm" disabled>${ic('download','w-3.5 h-3.5')} Download (restricted)</button>
              </div>
            </div>
          ` : `<div class="text-sm text-ink-mute">No record yet.</div>`}
        `;
      }
      if (tab === 'timeline') return `
        <div class="tl">
          ${(c.timeline||[]).slice().reverse().map(t=>`
            <div class="tl-item ok"><div class="text-sm font-semibold text-ink">${t.t}</div><div class="text-xs text-ink-faint">${H.fmtDate(t.d)} · ${t.who}</div></div>
          `).join('') || '<div class="text-sm text-ink-mute">No events.</div>'}
        </div>
      `;
      if (tab === 'audit') return `
        <table class="tbl tbl-compact">
          <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Change</th><th>Note</th></tr></thead>
          <tbody>
            ${D.AUDIT.filter(a=>a.target.includes(c.id)).map(a=>`
              <tr><td>${a.ts}</td><td>${a.user}</td><td>${U.pill(a.action,'gray')}</td><td class="text-xs">${a.from} → <b>${a.to}</b></td><td class="text-ink-mute">${a.note}</td></tr>
            `).join('') || '<tr><td colspan="5" class="text-center text-sm text-ink-mute py-6">No audit events for this candidate.</td></tr>'}
          </tbody>
        </table>
      `;
      return '';
    }
  };

  /* =================================================================
     VACANCIES (admin)
  ================================================================= */
  const vacancies = {
    render() {
      return `
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
          <div>
            <h1 class="font-display font-bold text-2xl text-ink">Vacancies</h1>
            <p class="text-sm text-ink-mute">${D.VACANCIES.length} total · ${D.VACANCIES.filter(v=>v.status==='open').length} open</p>
          </div>
          <div class="flex gap-2">
            <input class="input" style="width:240px" placeholder="Search..."/>
            <button class="btn btn-soft btn-sm">${ic('filter','w-3.5 h-3.5')} Filter</button>
            <button class="btn btn-primary btn-sm" data-action="new-vacancy">${ic('plus','w-3.5 h-3.5')} New vacancy</button>
          </div>
        </div>
        <div class="card overflow-hidden">
          <table class="tbl">
            <thead><tr><th>Vacancy</th><th>Campus</th><th>Type</th><th>Applicants</th><th>Posted</th><th>Closes</th><th>Status</th><th></th></tr></thead>
            <tbody>
              ${D.VACANCIES.map(v=>{
                const cmp = H.campus(v.campus);
                return `
                  <tr style="cursor:pointer" data-route="vacancy" data-id="${v.id}">
                    <td><div class="font-semibold">${v.title}</div><div class="text-xs text-ink-faint">${v.id} · ${v.dept}</div></td>
                    <td data-label="Campus">${cmp.name}</td>
                    <td data-label="Type">${v.type}</td>
                    <td data-label="Applicants"><div class="flex items-center gap-2"><span class="font-bold">${v.applicants}</span><div class="bar w-14"><i style="width:${Math.min(100,v.applicants*1.5)}%"></i></div></div></td>
                    <td data-label="Posted" class="text-xs text-ink-mute">${H.fmtDate(v.posted)}</td>
                    <td data-label="Closes" class="text-xs text-ink-mute">${H.fmtDate(v.closes)}</td>
                    <td data-label="Status">${U.pill(v.status, v.status==='open'?'green':v.status==='closing'?'amber':'gray',true)}</td>
                    <td><button class="iconbtn">${ic('more-horizontal','w-4 h-4')}</button></td>
                  </tr>`;
              }).join('')}
            </tbody>
          </table>
        </div>
      `;
    }
  };

  /* =================================================================
     WRITTEN TESTS
  ================================================================= */
  const tests = {
    render() {
      const cands = D.CANDIDATES.filter(c=>c.score?.written != null || ['written','interview','psycho','medical','yayasan','opl','active'].includes(c.stage));
      return `
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
          <div>
            <h1 class="font-display font-bold text-2xl text-ink">Written Tests</h1>
            <p class="text-sm text-ink-mute">Schedule, score and review written assessments</p>
          </div>
          <div class="flex gap-2">
            <button class="btn btn-soft btn-sm">${ic('calendar-plus','w-3.5 h-3.5')} Schedule test</button>
            <button class="btn btn-primary btn-sm">${ic('upload','w-3.5 h-3.5')} Upload results</button>
          </div>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
          ${U.stat({label:'Tests scheduled', value:6, icon:'calendar', tone:'blue'})}
          ${U.stat({label:'Awaiting score',  value:3, icon:'clock', tone:'amber'})}
          ${U.stat({label:'Pass rate',       value:'68%', icon:'check-circle-2', tone:'green'})}
          ${U.stat({label:'Avg score',       value:'81/100', icon:'bar-chart-3', tone:'gold'})}
        </div>
        <div class="card overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 flex items-center gap-3">
            <div class="font-display font-semibold">Test results</div>
            <div class="ml-auto text-xs text-ink-mute">Threshold: <b class="text-ink">70</b> · Edit window: <b class="text-ink">48 h</b></div>
          </div>
          <table class="tbl">
            <thead><tr><th>Candidate</th><th>Subject</th><th>Score</th><th>Pass</th><th>Evaluator</th><th>Date</th><th></th></tr></thead>
            <tbody>
              ${cands.slice(0,8).map(c=>{
                const v = H.vacancy(c.vacancyId);
                const sc = c.score?.written ?? '—';
                const pass = sc !== '—' && sc >= 70;
                return `
                  <tr>
                    <td><div class="flex items-center gap-2"><div class="av av-sm">${H.initials(c.name)}</div><div><div class="font-semibold">${c.name}</div><div class="text-[11px] text-ink-faint">${c.id}</div></div></div></td>
                    <td data-label="Subject">${v?.dept || ''}</td>
                    <td data-label="Score"><span class="font-display font-bold text-lg">${sc}</span><span class="text-ink-faint text-xs">/100</span></td>
                    <td data-label="Pass">${sc==='—' ? U.pill('Pending','gray') : pass ? U.pill('Pass','green',true) : U.pill('Fail','red',true)}</td>
                    <td data-label="Evaluator">Maria Hartanto</td>
                    <td data-label="Date" class="text-xs text-ink-mute">${H.fmtDate('2026-05-02')}</td>
                    <td><button class="btn btn-ghost btn-sm" data-action="edit-test" data-id="${c.id}">${ic('pencil','w-3.5 h-3.5')} Edit</button></td>
                  </tr>`;
              }).join('')}
            </tbody>
          </table>
        </div>
      `;
    }
  };

  /* =================================================================
     INTERVIEWS
  ================================================================= */
  const interviews = {
    render() {
      return `
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
          <div>
            <h1 class="font-display font-bold text-2xl text-ink">Interviews & Micro-Teaching</h1>
            <p class="text-sm text-ink-mute">Schedule, evaluate and compare candidate sessions</p>
          </div>
          <div class="flex gap-2">
            <button class="btn btn-ghost btn-sm">${ic('calendar','w-3.5 h-3.5')} Calendar view</button>
            <button class="btn btn-primary btn-sm">${ic('plus','w-3.5 h-3.5')} New interview</button>
          </div>
        </div>
        <div class="grid lg:grid-cols-3 gap-4 mb-4">
          <div class="card p-5 lg:col-span-2">
            ${U.sectionTitle('Schedule')}
            <table class="tbl tbl-compact">
              <thead><tr><th>Candidate</th><th>Type</th><th>Date / Time</th><th>Room</th><th>Panel</th><th>Status</th></tr></thead>
              <tbody>
                ${D.INTERVIEWS.map(i=>{
                  const c = H.candidate(i.candidateId) || { name:'—' };
                  return `
                    <tr style="cursor:pointer" data-route="candidate" data-id="${i.candidateId}">
                      <td><div class="flex items-center gap-2"><div class="av av-sm">${H.initials(c.name)}</div><span class="font-semibold">${c.name}</span></div></td>
                      <td>${i.type}</td>
                      <td>${H.fmtDate(i.date)} · ${i.time}</td>
                      <td>${i.room}</td>
                      <td class="text-xs">${i.panel.slice(0,2).join(', ')}${i.panel.length>2?` +${i.panel.length-2}`:''}</td>
                      <td>${U.pill(i.status, i.status==='scheduled'?'blue':'green',true)}</td>
                    </tr>`;
                }).join('')}
              </tbody>
            </table>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Evaluation rubric')}
            ${D.EVAL_RUBRIC.map(r=>`
              <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                <div class="text-sm font-semibold">${r.label}</div>
                <div class="text-xs text-ink-mute">${r.weight}%</div>
              </div>`).join('')}
            <button class="btn btn-soft btn-sm w-full mt-3">${ic('settings','w-3.5 h-3.5')} Edit rubric</button>
          </div>
        </div>

        <div class="card p-5">
          ${U.sectionTitle('Side-by-side evaluation comparison')}
          <table class="tbl tbl-compact">
            <thead><tr><th>Criterion</th><th>Aditya P. Wijaya</th><th>Dewi Anggraini</th><th>Indra Maulana</th></tr></thead>
            <tbody>
              ${D.EVAL_RUBRIC.map((r,i)=>{
                const a=[88,86,90,87,89,82][i], b=[83,81,80,82,84,78][i], c=[76,74,72,78,75,70][i];
                const dot = (n)=>`<div class="flex items-center gap-2"><div class="bar flex-1"><i style="width:${n}%; background: ${n>85?'var(--green)':n>75?'var(--amber)':'var(--red)'}"></i></div><span class="font-bold w-7 text-right">${n}</span></div>`;
                return `<tr><td>${r.label}</td><td>${dot(a)}</td><td>${dot(b)}</td><td>${dot(c)}</td></tr>`;
              }).join('')}
              <tr><td class="font-bold">Recommendation</td><td>${U.pill('Strong Hire','green')}</td><td>${U.pill('Hire','blue')}</td><td>${U.pill('No Hire','red')}</td></tr>
            </tbody>
          </table>
        </div>
      `;
    }
  };

  /* =================================================================
     DEPOSITS
  ================================================================= */
  const deposits = {
    render() {
      return `
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
          <div>
            <h1 class="font-display font-bold text-2xl text-ink">Deposit Management</h1>
            <p class="text-sm text-ink-mute">Recruitment deposits & refund tracking</p>
          </div>
          <div class="flex gap-2">
            <button class="btn btn-ghost btn-sm">${ic('download','w-3.5 h-3.5')} Export</button>
            <button class="btn btn-primary btn-sm">${ic('upload','w-3.5 h-3.5')} Upload bank statement</button>
          </div>
        </div>

        <div class="sec-strip mb-4">${ic('shield-alert','w-4 h-4')} Finance role required to verify or refund deposits. All actions are audit-logged.</div>

        <div class="card overflow-hidden">
          <div class="tab-bar px-2">
            ${['Pending','Verified','Refund eligible','Refunded','Failed'].map((t,i)=>`<div class="tab ${i===0?'active':''}">${t}</div>`).join('')}
          </div>
          <table class="tbl">
            <thead><tr><th>Candidate</th><th>Amount</th><th>Status</th><th>Paid date / Due</th><th>Receipt</th><th>Actions</th></tr></thead>
            <tbody>
              ${D.DEPOSITS.map(d=>{
                const overdue = d.status==='pending' && d.dueDate && new Date(d.dueDate) < H.today;
                return `
                  <tr style="${overdue?'background:#FEF2F2':''}">
                    <td><div class="font-semibold">${d.candidateName}</div><div class="text-[11px] text-ink-faint">${d.candidateId}</div></td>
                    <td data-label="Amount" class="font-display font-bold">${H.fmtIDR(d.amount)}</td>
                    <td data-label="Status">${U.pill(d.status, d.status==='verified'?'green':d.status==='pending'?'amber':d.status==='failed'?'red':'brown',true)}</td>
                    <td data-label="Date" class="text-xs">
                      ${d.paidAt ? `Paid ${H.fmtDate(d.paidAt)}` : ''}
                      ${d.dueDate ? `<span class="${overdue?'text-red-600 font-bold':'text-ink-mute'}">Due ${H.fmtDate(d.dueDate)}${overdue?' · OVERDUE':''}</span>` : ''}
                    </td>
                    <td data-label="Receipt">${d.receipt ? `<button class="btn btn-ghost btn-sm">${ic('file-text','w-3.5 h-3.5')} ${d.receipt}</button>` : '<span class="text-ink-faint text-xs">—</span>'}</td>
                    <td>
                      ${d.status==='pending' ? `<button class="btn btn-success btn-sm" data-action="verify-deposit" data-id="${d.candidateId}">${ic('check','w-3.5 h-3.5')} Verify</button>` : ''}
                      ${d.status==='verified' && d.refundEligible ? `<button class="btn btn-soft btn-sm">${ic('undo-2','w-3.5 h-3.5')} Refund</button>` : ''}
                    </td>
                  </tr>`;
              }).join('')}
            </tbody>
          </table>
        </div>
      `;
    }
  };

  /* =================================================================
     PSYCHO / MEDICAL
  ================================================================= */
  function verificationPage(kind) {
    const isMed = kind === 'medical';
    return {
      render() {
        const list = D.CANDIDATES.filter(c=>c[kind]);
        return `
          <div class="mb-4">
            <h1 class="font-display font-bold text-2xl text-ink">${isMed ? 'Medical' : 'Psycho'} Verification</h1>
            <p class="text-sm text-ink-mute">Confidential clearance records — restricted-access</p>
          </div>
          <div class="sec-strip danger mb-4">
            ${ic('lock','w-4 h-4')} <b>Confidential — viewer access only.</b> Document downloads disabled. Every preview is recorded in the audit log.
          </div>
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            ${U.stat({label:'Cleared', value: list.filter(c=>c[kind].status==='passed').length, icon:'check-circle-2', tone:'green'})}
            ${U.stat({label:'Pending', value: list.filter(c=>c[kind].status==='pending').length, icon:'clock', tone:'amber'})}
            ${U.stat({label:'Failed',  value: list.filter(c=>c[kind].status==='failed').length, icon:'x-circle', tone:'red'})}
            ${U.stat({label:'Avg turnaround', value:'3.2 d', icon:'timer', tone:'gold'})}
          </div>
          <div class="card overflow-hidden">
            <table class="tbl">
              <thead><tr><th>Candidate</th><th>Status</th><th>Date</th><th>${isMed?'Clinic':'Counselor'}</th><th>Actions</th></tr></thead>
              <tbody>
                ${list.map(c=>{
                  const d = c[kind];
                  return `
                    <tr>
                      <td><div class="flex items-center gap-2"><div class="av av-sm">${H.initials(c.name)}</div><div><div class="font-semibold">${c.name}</div><div class="text-[11px] text-ink-faint">${c.id}</div></div></div></td>
                      <td>${U.pill(d.status, d.status==='passed'?'green':d.status==='pending'?'amber':'red',true)}</td>
                      <td>${H.fmtDate(d.date || d.scheduledAt)}</td>
                      <td>${d.clinic || d.counselor || '—'}</td>
                      <td>
                        <button class="btn btn-ghost btn-sm" data-route="candidate" data-id="${c.id}">${ic('eye','w-3.5 h-3.5')} Secure preview</button>
                        <button class="btn btn-ghost btn-sm" disabled>${ic('download','w-3.5 h-3.5')} Restricted</button>
                      </td>
                    </tr>`;
                }).join('')}
              </tbody>
            </table>
          </div>
        `;
      }
    };
  }
  const psycho = verificationPage('psycho');
  const medical = verificationPage('medical');

  /* =================================================================
     YAYASAN APPROVAL
  ================================================================= */
  const yayasan = {
    render() {
      const queue = D.CANDIDATES.filter(c=>c.stage==='yayasan');
      return `
        <div class="mb-4">
          <h1 class="font-display font-bold text-2xl text-ink">Yayasan Approval Center</h1>
          <p class="text-sm text-ink-mute">Board-level final approval before OPL activation</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
          ${U.stat({label:'Awaiting decision', value: queue.length, icon:'landmark', tone:'maroon'})}
          ${U.stat({label:'Approved (30d)',    value: 6, icon:'check-circle-2', tone:'green'})}
          ${U.stat({label:'Returned for review',value: 1, icon:'undo-2', tone:'amber'})}
          ${U.stat({label:'Rejected (30d)',    value: 0, icon:'x-circle', tone:'red'})}
        </div>

        <div class="space-y-3">
          ${queue.map(c=>{
            const v = H.vacancy(c.vacancyId);
            return `
              <div class="card p-5">
                <div class="flex items-center gap-4 flex-wrap">
                  <div class="av av-lg">${H.initials(c.name)}</div>
                  <div class="flex-1 min-w-0">
                    <div class="font-display font-bold text-base">${c.name}</div>
                    <div class="text-xs text-ink-mute">${v.title} · ${H.campus(v.campus).name} · ${c.years} yrs experience</div>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                      ${U.pill('Strong Hire','green')}
                      ${U.pill('All checks passed','blue')}
                      ${U.pill(`Score ${Math.round(Object.values(c.score||{}).reduce((a,b)=>a+b,0)/Math.max(1,Object.values(c.score||{}).length))}`,'brown')}
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <button class="btn btn-ghost btn-sm" data-route="candidate" data-id="${c.id}">${ic('file-text','w-3.5 h-3.5')} Full dossier</button>
                    <button class="btn btn-soft btn-sm" data-action="yay-escalate" data-id="${c.id}">Escalate</button>
                    <button class="btn btn-danger btn-sm" data-action="yay-reject" data-id="${c.id}">Reject</button>
                    <button class="btn btn-success btn-sm" data-action="yay-approve" data-id="${c.id}">${ic('check','w-3.5 h-3.5')} Approve</button>
                  </div>
                </div>
                <div class="divider"></div>
                <div class="grid md:grid-cols-4 gap-3 text-sm">
                  <div><div class="text-xs text-ink-faint">Written</div><div class="font-bold">${c.score?.written || '—'}/100</div></div>
                  <div><div class="text-xs text-ink-faint">Interview</div><div class="font-bold">${c.score?.interview || '—'}/100</div></div>
                  <div><div class="text-xs text-ink-faint">Psycho</div><div>${U.pill(c.psycho?.status||'pending', c.psycho?.status==='passed'?'green':'amber')}</div></div>
                  <div><div class="text-xs text-ink-faint">Medical</div><div>${U.pill(c.medical?.status||'pending', c.medical?.status==='passed'?'green':'amber')}</div></div>
                </div>
                <div class="divider"></div>
                <div class="grid md:grid-cols-2 gap-3">
                  <div class="card-flat p-3">
                    <div class="text-xs text-ink-faint font-semibold uppercase">Principal recommendation</div>
                    <p class="text-sm text-ink-soft mt-1">"Excellent subject mastery and warm classroom presence. Recommend offer with OPL track under Rini Surya."</p>
                  </div>
                  <div class="card-flat p-3">
                    <div class="text-xs text-ink-faint font-semibold uppercase">Risk indicators</div>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                      ${U.pill('No flags','green',true)}
                      ${U.pill('Background OK','green')}
                      ${U.pill('References verified','green')}
                    </div>
                  </div>
                </div>
              </div>`;
          }).join('') || U.empty('Inbox empty','All candidates processed.','check-circle-2')}
        </div>
      `;
    }
  };

  /* =================================================================
     OPL TRACKER
  ================================================================= */
  const opl = {
    render() {
      const list = D.CANDIDATES.filter(c=>c.stage==='opl');
      return `
        <div class="mb-4">
          <h1 class="font-display font-bold text-2xl text-ink">OPL Tracker</h1>
          <p class="text-sm text-ink-mute">10-session probation onboarding for new teachers</p>
        </div>

        ${list.map(c=>{
          const sess = c.opl.sessions; const done = sess.filter(s=>s.status==='done');
          const avg = done.length ? Math.round(done.reduce((a,b)=>a+b.score,0)/done.length) : 0;
          return `
            <div class="card p-5 mb-4">
              <div class="flex items-start gap-4 flex-wrap">
                <div class="av av-xl">${H.initials(c.name)}</div>
                <div class="flex-1 min-w-0">
                  <div class="font-display font-bold text-lg">${c.name}</div>
                  <div class="text-xs text-ink-mute">${H.vacancy(c.vacancyId).title} · OPL started ${H.fmtDate(c.opl.start)} · Mentor: ${sess[0].mentor}</div>
                  <div class="flex flex-wrap gap-1.5 mt-2">
                    ${U.pill(`Day ${H.daysBetween(c.opl.start,H.today)} of 90`,'brown')}
                    ${U.pill(`${done.length}/${sess.length} sessions`,'green')}
                    ${U.pill(`Avg ${avg}/100`,'gold')}
                  </div>
                </div>
                ${U.ring(Math.round(done.length/sess.length*100), 72)}
              </div>

              <div class="divider"></div>

              <div class="overflow-x-auto">
                <div class="flex gap-2 min-w-max pb-2">
                  ${sess.map(s=>{
                    const cls = s.status==='done' ? 'bg-emerald-50 border-emerald-300 text-emerald-800'
                              : s.status==='scheduled' ? 'bg-blue-50 border-blue-300 text-blue-800'
                              : 'bg-slate-50 border-slate-200 text-slate-500';
                    return `
                      <div class="border ${cls} rounded-lg p-3 w-40 flex-shrink-0">
                        <div class="flex items-center justify-between text-[10px] uppercase font-bold tracking-wider">
                          <span>Session ${s.n}</span>
                          ${s.status==='done' ? `<span>${s.score}/100</span>` : ''}
                        </div>
                        <div class="text-sm font-semibold mt-1 truncate">${s.topic}</div>
                        <div class="text-[11px] mt-1">${H.fmtDate(s.date)}</div>
                      </div>`;
                  }).join('')}
                </div>
              </div>

              <div class="divider"></div>

              <div class="grid md:grid-cols-2 gap-3">
                <div class="card-flat p-4">
                  <div class="text-xs text-ink-faint font-semibold uppercase mb-2">Latest session notes</div>
                  ${done.slice(-1).map(s=>`
                    <div class="text-sm font-semibold">Session ${s.n} · ${s.topic}</div>
                    <div class="text-xs text-ink-mute mb-2">${H.fmtDate(s.date)} · ${s.mentor}</div>
                    <p class="text-sm text-ink-soft">${s.notes}</p>
                  `).join('')}
                </div>
                <div class="card-flat p-4">
                  <div class="text-xs text-ink-faint font-semibold uppercase mb-2">Next session</div>
                  ${sess.find(s=>s.status==='scheduled') ? (() => { const n = sess.find(s=>s.status==='scheduled'); return `
                    <div class="text-sm font-semibold">Session ${n.n} · ${n.topic}</div>
                    <div class="text-xs text-ink-mute mb-2">${H.fmtDate(n.date)} · ${n.mentor}</div>
                    <button class="btn btn-primary btn-sm" data-action="opl-eval" data-id="${c.id}">${ic('clipboard-check','w-3.5 h-3.5')} Submit evaluation</button>
                  `; })() : '<div class="text-sm text-ink-mute">No upcoming session.</div>'}
                </div>
              </div>
            </div>`;
        }).join('') || U.empty('No OPL teachers','Activated candidates will appear here.','graduation-cap')}
      `;
    }
  };

  /* =================================================================
     PROBATION
  ================================================================= */
  const probation = {
    render() {
      const list = [...D.CANDIDATES.filter(c=>c.stage==='opl').map(c=>({
        name:c.name, role:H.vacancy(c.vacancyId).title, start:c.opl.start, status:'active', score:88
      })), ...[
        { name:'Muhammad Reza, M.Si', role:'Physics Teacher (IB DP)', start:'2026-01-10', status:'passed', score:91 },
        { name:'Novita Putri, S.S',   role:'Mandarin Teacher',         start:'2026-02-01', status:'passed', score:87 },
        { name:'Ahmad Solihin',       role:'PE Teacher',               start:'2026-03-15', status:'warning', score:71 },
        { name:'Siti Rahayu',         role:'Tematik G2',               start:'2026-04-01', status:'review', score:75 },
      ]];

      return `
        <div class="mb-4">
          <h1 class="font-display font-bold text-2xl text-ink">Probation Tracker</h1>
          <p class="text-sm text-ink-mute">90-day probation periods for new teachers</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
          ${U.stat({label:'Active probation', value: D.ANALYTICS.probation.active, icon:'timer', tone:'brown'})}
          ${U.stat({label:'Passed (YTD)',     value: D.ANALYTICS.probation.passed, icon:'check-circle-2', tone:'green'})}
          ${U.stat({label:'Warning',          value: 1, icon:'alert-triangle', tone:'amber'})}
          ${U.stat({label:'Failed (YTD)',     value: D.ANALYTICS.probation.failed, icon:'x-circle', tone:'red'})}
        </div>

        <div class="card overflow-hidden">
          <table class="tbl">
            <thead><tr><th>Teacher</th><th>Role</th><th>Probation period</th><th>Day</th><th>Score</th><th>Status</th><th></th></tr></thead>
            <tbody>
              ${list.map(p=>{
                const day = H.daysBetween(p.start, H.today);
                const pct = Math.min(100, Math.round(day/90*100));
                const tone = p.status==='passed'?'green':p.status==='warning'?'amber':p.status==='review'?'gold':p.status==='failed'?'red':'brown';
                return `
                  <tr>
                    <td><div class="flex items-center gap-2"><div class="av av-sm">${H.initials(p.name)}</div><span class="font-semibold">${p.name}</span></div></td>
                    <td>${p.role}</td>
                    <td class="text-xs text-ink-mute">${H.fmtDate(p.start)} → ${H.fmtDate(new Date(new Date(p.start).getTime()+90*86400000))}</td>
                    <td><div class="flex items-center gap-2"><div class="bar w-20 ${day>=90?'green':''}"><i style="width:${pct}%"></i></div><span class="text-xs font-bold">${day}/90</span></div></td>
                    <td class="font-bold">${p.score}/100</td>
                    <td>${U.pill(p.status, tone, true)}</td>
                    <td><button class="btn btn-ghost btn-sm">Decision</button></td>
                  </tr>`;
              }).join('')}
            </tbody>
          </table>
        </div>
      `;
    }
  };

  /* =================================================================
     CONTRACTS
  ================================================================= */
  const contracts = {
    render() {
      return `
        <div class="mb-4">
          <h1 class="font-display font-bold text-2xl text-ink">Contract Management</h1>
          <p class="text-sm text-ink-mute">Active contracts, renewals & SK generation</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
          ${U.stat({label:'Permanent (Tetap)', value: D.TEACHERS.filter(t=>t.status==='permanent').length, icon:'badge-check', tone:'green'})}
          ${U.stat({label:'Contract (1yr)',    value: D.TEACHERS.filter(t=>t.status==='contract').length,  icon:'file-signature', tone:'blue'})}
          ${U.stat({label:'OPL probation',     value: D.TEACHERS.filter(t=>t.status==='opl').length,       icon:'timer', tone:'brown'})}
          ${U.stat({label:'Renewal in 90d',    value: 2, icon:'calendar-clock', tone:'amber'})}
        </div>
        <div class="card overflow-hidden">
          <table class="tbl">
            <thead><tr><th>Teacher</th><th>Subject</th><th>Type</th><th>Period</th><th>SK</th><th>Status</th><th></th></tr></thead>
            <tbody>
              ${D.TEACHERS.map(t=>`
                <tr>
                  <td><div class="flex items-center gap-2"><div class="av av-sm">${H.initials(t.name)}</div><div><div class="font-semibold">${t.name}</div><div class="text-[11px] text-ink-faint">${t.id}</div></div></div></td>
                  <td>${t.subject}</td>
                  <td>${t.contract}</td>
                  <td class="text-xs">${H.fmtDate(t.joined)} → ${t.status==='permanent'?'—':'2027-XX-XX'}</td>
                  <td>${U.pill(t.status==='opl'?'Pending':'Generated', t.status==='opl'?'amber':'green')}</td>
                  <td>${U.pill(t.status, t.status==='permanent'?'green':t.status==='contract'?'blue':'brown',true)}</td>
                  <td><button class="btn btn-ghost btn-sm" data-route="teacher" data-id="${t.id}">${ic('eye','w-3.5 h-3.5')}</button></td>
                </tr>`).join('')}
            </tbody>
          </table>
        </div>
      `;
    }
  };

  /* =================================================================
     BUKU INDUK GURU (master record list + per-teacher profile)
  ================================================================= */
  const bukuinduk = {
    render() {
      return `
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
          <div>
            <h1 class="font-display font-bold text-2xl text-ink">Buku Induk Guru</h1>
            <p class="text-sm text-ink-mute">Master teacher records · ${D.TEACHERS.length} faculty</p>
          </div>
          <div class="flex gap-2">
            <input class="input" style="width:240px" placeholder="Search teacher..."/>
            <button class="btn btn-soft btn-sm">${ic('filter','w-3.5 h-3.5')} Filter</button>
            <button class="btn btn-ghost btn-sm">${ic('download','w-3.5 h-3.5')} Export</button>
          </div>
        </div>
        <div class="grid lg:grid-cols-2 gap-3">
          ${D.TEACHERS.map(t=>`
            <div class="card p-5 cursor-pointer hover:border-slate-300" data-route="teacher" data-id="${t.id}">
              <div class="flex items-start gap-3">
                <div class="av av-lg">${H.initials(t.name)}</div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2"><div class="font-display font-bold">${t.name}</div>${U.pill(t.status,t.status==='permanent'?'green':t.status==='contract'?'blue':'brown',true)}</div>
                  <div class="text-xs text-ink-mute">${t.id} · ${t.subject} · ${H.campus(t.campus).name}</div>
                  <div class="flex flex-wrap gap-1.5 mt-2">
                    ${U.pill(`Joined ${new Date(t.joined).getFullYear()}`,'gray')}
                    ${t.rating ? U.pill(`★ ${t.rating}`,'gold') : U.pill('Not yet rated','gray')}
                  </div>
                </div>
                ${ic('chevron-right','w-4 h-4 text-slate-400')}
              </div>
            </div>`).join('')}
        </div>
      `;
    }
  };

  const teacher = {
    render() {
      const t = H.teacher(STATE.activeTeacherId) || D.TEACHERS[0];
      const yrs = (new Date(H.today) - new Date(t.joined)) / 31536000000;
      return `
        <button class="text-sm text-ink-mute hover:text-ink mb-4 inline-flex items-center gap-1" data-route="bukuinduk">${ic('arrow-left','w-4 h-4')} Back to Buku Induk</button>

        <div class="card p-6 mb-4">
          <div class="flex items-start gap-5 flex-wrap">
            <div class="av av-xl">${H.initials(t.name)}</div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap"><h1 class="font-display font-bold text-2xl">${t.name}</h1>${U.pill(t.status,t.status==='permanent'?'green':t.status==='contract'?'blue':'brown',true)}</div>
              <div class="text-sm text-ink-mute">${t.id} · ${t.subject} · ${H.campus(t.campus).name}</div>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-slate-100">
                <div><div class="text-xs text-ink-faint">Years at Sutomo</div><div class="font-display font-bold text-lg">${yrs.toFixed(1)}</div></div>
                <div><div class="text-xs text-ink-faint">Contract</div><div class="font-semibold text-sm">${t.contract}</div></div>
                <div><div class="text-xs text-ink-faint">Last review</div><div class="font-semibold text-sm">${H.fmtDate(t.lastReview)||'—'}</div></div>
                <div><div class="text-xs text-ink-faint">Rating</div><div class="font-display font-bold text-lg">${t.rating?'★ '+t.rating:'—'}</div></div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
          <div class="card p-5">
            ${U.sectionTitle('Personal information')}
            <div class="grid grid-cols-2 gap-3 text-sm">
              ${[
                ['Full name', t.name],['Teacher ID', t.id],['Campus', H.campus(t.campus).name],
                ['Subject', t.subject],['Date joined', H.fmtDate(t.joined)],['NIP / NIK', '197801102003121002'],
                ['Email', t.name.toLowerCase().split(',')[0].replace(/ /g,'.')+'@sutomo.sch.id'], ['Phone', '+62 812 3456 ----']
              ].map(p=>`<div><div class="text-[11px] uppercase font-bold text-ink-faint">${p[0]}</div><div class="text-sm">${p[1]}</div></div>`).join('')}
            </div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('ATS history')}
            <div class="tl">
              <div class="tl-item ok"><div class="text-sm font-semibold">Application submitted</div><div class="text-xs text-ink-faint">Mar 2018 · referred by Dewi K.</div></div>
              <div class="tl-item ok"><div class="text-sm font-semibold">Written test passed (89/100)</div><div class="text-xs text-ink-faint">Apr 2018</div></div>
              <div class="tl-item ok"><div class="text-sm font-semibold">Yayasan approval</div><div class="text-xs text-ink-faint">May 2018</div></div>
              <div class="tl-item ok"><div class="text-sm font-semibold">OPL completed (avg 92/100)</div><div class="text-xs text-ink-faint">Jun 2018</div></div>
              <div class="tl-item ok"><div class="text-sm font-semibold">Contract → Permanent</div><div class="text-xs text-ink-faint">Jul 2021</div></div>
            </div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Contracts')}
            <table class="tbl tbl-compact">
              <thead><tr><th>Period</th><th>Type</th><th>SK No.</th></tr></thead>
              <tbody>
                <tr><td>2018-07-01 → 2019-06-30</td><td>Contract</td><td class="font-mono text-xs">SK/HRD/2018/047</td></tr>
                <tr><td>2019-07-01 → 2021-06-30</td><td>Contract (2yr)</td><td class="font-mono text-xs">SK/HRD/2019/082</td></tr>
                <tr><td>2021-07-01 → present</td><td>Permanent</td><td class="font-mono text-xs">SK/HRD/2021/121</td></tr>
              </tbody>
            </table>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Awards & professional development')}
            <ul class="text-sm space-y-2">
              <li class="flex gap-2">${ic('trophy','w-4 h-4 text-gold-dark')}<span><b>Teacher of the Year</b> · 2023</span></li>
              <li class="flex gap-2">${ic('award','w-4 h-4 text-gold-dark')}<span>Cambridge IGCSE Math Trainer Cert · 2022</span></li>
              <li class="flex gap-2">${ic('graduation-cap','w-4 h-4 text-brand-dark')}<span>IB DP Math AA Workshop (Cat. 1) · 2021</span></li>
              <li class="flex gap-2">${ic('graduation-cap','w-4 h-4 text-brand-dark')}<span>S2 Pendidikan Matematika UPI · 2017</span></li>
            </ul>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Supervisi evaluations')}
            <div class="space-y-2">
              ${[2025,2024,2023,2022].map(y=>`
                <div class="flex items-center gap-3 card-flat p-3"><div class="font-display font-bold">${y}</div><div class="flex-1">${U.bar([94,91,92,88][2025-y],'green')}</div><div class="text-sm font-bold">${[4.8,4.7,4.7,4.5][2025-y]}/5</div></div>
              `).join('')}
            </div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Leave history')}
            <table class="tbl tbl-compact">
              <thead><tr><th>Type</th><th>Days</th><th>Period</th></tr></thead>
              <tbody>
                <tr><td>Maternity</td><td>90</td><td>2024-03 → 06</td></tr>
                <tr><td>Annual leave</td><td>14</td><td>2025</td></tr>
                <tr><td>Sick leave</td><td>3</td><td>2025-09</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      `;
    }
  };

  /* =================================================================
     AUDIT LOG
  ================================================================= */
  const audit = {
    render() {
      return `
        ${STATE.route === 'audit' ? `
          <div class="mb-4">
            <h1 class="font-display font-bold text-2xl text-ink">Audit Log</h1>
            <p class="text-sm text-ink-mute">Tamper-proof record of every state-changing action</p>
          </div>
        ` : ''}
        <div class="card p-5 mb-3 flex flex-wrap gap-2">
          <input class="input" style="width:240px" placeholder="Search by user, target, action..."/>
          <select class="select" style="width:auto"><option>All actions</option><option>stage.move</option><option>override.score</option><option>deposit.verify</option><option>approval.grant</option></select>
          <select class="select" style="width:auto"><option>All roles</option>${D.ROLES.map(r=>`<option>${r.label}</option>`).join('')}</select>
          <input class="input" type="date" style="width:auto"/>
          <button class="btn btn-ghost btn-sm ml-auto">${ic('download','w-3.5 h-3.5')} Export</button>
        </div>
        <div class="card overflow-hidden">
          <table class="tbl">
            <thead><tr><th>Timestamp</th><th>User</th><th>Role</th><th>Action</th><th>Target</th><th>Change</th><th>Note</th></tr></thead>
            <tbody>
              ${D.AUDIT.map(a=>`
                <tr>
                  <td class="font-mono text-xs">${a.ts}</td>
                  <td><div class="flex items-center gap-2"><div class="av av-xs">${H.initials(a.user)}</div>${a.user}</div></td>
                  <td>${U.pill(a.role,'gray')}</td>
                  <td>${U.pill(a.action,'brown')}</td>
                  <td class="font-mono text-xs">${a.target}</td>
                  <td class="text-xs"><span class="text-ink-faint line-through">${a.from}</span> → <b>${a.to}</b></td>
                  <td class="text-ink-mute">${a.note}</td>
                </tr>`).join('')}
            </tbody>
          </table>
        </div>
      `;
    }
  };

  /* =================================================================
     ANALYTICS
  ================================================================= */
  const analytics = {
    render() {
      return `
        <div class="mb-4">
          <h1 class="font-display font-bold text-2xl text-ink">HR Analytics</h1>
          <p class="text-sm text-ink-mute">Recruitment funnel, time-to-hire & retention metrics</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
          ${U.stat({label:'Median time-to-hire', value: D.ANALYTICS.timeToHire.median+'d', sub:'p90: '+D.ANALYTICS.timeToHire.p90+'d', icon:'timer', tone:'brown', delta:-8})}
          ${U.stat({label:'Funnel conversion',   value:'3.8%', sub:'Applied → Active', icon:'percent', tone:'gold'})}
          ${U.stat({label:'Y1 retention',        value: Math.round(D.ANALYTICS.retention.y1*100)+'%', icon:'trending-up', tone:'green', delta:2})}
          ${U.stat({label:'Cost per hire',       value:'Rp 4.8 jt', icon:'wallet', tone:'blue', delta:-5})}
        </div>

        <div class="grid lg:grid-cols-3 gap-4 mb-4">
          <div class="card p-5 lg:col-span-2">
            ${U.sectionTitle('Recruitment funnel (90d)')}
            <div class="funnel">
              ${D.ANALYTICS.funnel.map(f=>{
                const max = D.ANALYTICS.funnel[0].n;
                return `<div class="row"><div class="lbl">${f.stage}</div><div class="bar"><i style="width:${f.n/max*100}%"></i></div><div class="n">${f.n}</div></div>`;
              }).join('')}
            </div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Source mix')}
            <div class="chart-wrap sm"><canvas id="srcChart"></canvas></div>
          </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4 mb-4">
          <div class="card p-5">
            ${U.sectionTitle('Hires by month')}
            <div class="chart-wrap sm"><canvas id="monthChart"></canvas></div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Hires by department')}
            <div class="space-y-2 mt-2">
              ${D.ANALYTICS.deptHires.map(d=>{
                const max = Math.max(...D.ANALYTICS.deptHires.map(x=>x.v));
                return `<div class="flex items-center gap-3"><div class="w-24 text-sm">${d.k}</div><div class="bar flex-1"><i style="width:${d.v/max*100}%; background: var(--brand-dark)"></i></div><div class="w-8 text-right text-sm font-bold">${d.v}</div></div>`;
              }).join('')}
            </div>
          </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-4">
          <div class="card p-5">
            ${U.sectionTitle('Retention by tenure')}
            <div class="grid grid-cols-3 text-center gap-3">
              ${[['Y1',D.ANALYTICS.retention.y1],['Y2',D.ANALYTICS.retention.y2],['Y3',D.ANALYTICS.retention.y3]].map(r=>`
                <div><div class="font-display font-bold text-2xl">${Math.round(r[1]*100)}%</div><div class="text-xs text-ink-mute">${r[0]}</div></div>
              `).join('')}
            </div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Probation outcomes')}
            <div class="grid grid-cols-3 gap-3 text-center">
              <div><div class="font-display font-bold text-2xl text-emerald-600">${D.ANALYTICS.probation.passed}</div><div class="text-xs text-ink-mute">Passed</div></div>
              <div><div class="font-display font-bold text-2xl text-red-600">${D.ANALYTICS.probation.failed}</div><div class="text-xs text-ink-mute">Failed</div></div>
              <div><div class="font-display font-bold text-2xl text-brand-dark">${D.ANALYTICS.probation.active}</div><div class="text-xs text-ink-mute">Active</div></div>
            </div>
          </div>
          <div class="card p-5">
            ${U.sectionTitle('Application heatmap')}
            <div class="hmap">${Array.from({length:60}).map((_,i)=>{
              const lv = Math.floor(Math.random()*5);
              return `<div class="hcell l${lv||''}"></div>`;
            }).join('')}</div>
            <div class="flex items-center gap-2 mt-3 text-xs text-ink-faint">
              Less <div class="hcell"></div><div class="hcell l1"></div><div class="hcell l2"></div><div class="hcell l3"></div><div class="hcell l4"></div> More
            </div>
          </div>
        </div>
      `;
    },
    mount() {
      const monthCtx = document.getElementById('monthChart');
      if (monthCtx) {
        const ex = Chart.getChart && Chart.getChart(monthCtx); if (ex) ex.destroy();
        new Chart(monthCtx, {
          type:'bar',
          data:{ labels:['J','F','M','A','M','J','J','A','S','O','N','D'],
            datasets:[{ label:'Hires', data: D.ANALYTICS.monthly, backgroundColor:'#6B3A00', borderRadius:4 }] },
          options:{ plugins:{legend:{display:false}}, scales:{ y:{beginAtZero:true, grid:{color:'#EEF2F6'}}, x:{grid:{display:false}} }, maintainAspectRatio:false, responsive:true }
        });
      }
      const srcCtx = document.getElementById('srcChart');
      if (srcCtx) {
        const ex = Chart.getChart && Chart.getChart(srcCtx); if (ex) ex.destroy();
        new Chart(srcCtx, {
          type:'doughnut',
          data:{ labels: D.ANALYTICS.sources.map(s=>s.k), datasets:[{ data: D.ANALYTICS.sources.map(s=>s.v), backgroundColor:['#6B3A00','#D4AF37','#8A1538','#1E293B'] }] },
          options:{ plugins:{legend:{position:'bottom', labels:{boxWidth:10,font:{size:11}}}}, cutout:'62%', maintainAspectRatio:false, responsive:true }
        });
      }
    }
  };

  /* =================================================================
     INBOX (Notifications full page)
  ================================================================= */
  const inbox = {
    render() {
      const items = D.NOTIFICATIONS.filter(n => !n.role || n.role.includes(STATE.role));
      return `
        <div class="mb-4">
          <h1 class="font-display font-bold text-2xl text-ink">Notifications</h1>
          <p class="text-sm text-ink-mute">${items.filter(i=>!i.read).length} unread · all channels</p>
        </div>
        <div class="grid lg:grid-cols-4 gap-4">
          <div class="card p-3">
            ${['All','Unread','Mentions','System'].map((t,i)=>`
              <div class="px-3 py-2 rounded-md cursor-pointer ${i===0?'bg-cream text-brand-dark font-semibold':'text-ink-mute hover:bg-slate-50'} text-sm">${t}</div>
            `).join('')}
            <div class="divider"></div>
            <div class="text-[11px] uppercase font-bold text-ink-faint tracking-wider px-3 mb-1">Channels</div>
            ${[['mail','Email'],['smartphone','Push'],['message-circle','WhatsApp']].map(c=>`
              <div class="px-3 py-2 rounded-md text-sm flex items-center gap-2 text-ink-mute hover:bg-slate-50 cursor-pointer">${ic(c[0],'w-4 h-4')}${c[1]}</div>
            `).join('')}
          </div>
          <div class="card overflow-hidden lg:col-span-3">
            ${items.map(n=>`
              <div class="flex gap-3 px-4 py-4 border-b border-slate-100 last:border-0 hover:bg-slate-50 cursor-pointer">
                <div class="w-9 h-9 rounded-md bg-cream text-brand-dark flex items-center justify-center flex-shrink-0">${ic(n.icon,'w-4 h-4')}</div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2"><div class="font-semibold text-sm">${n.title}</div>${!n.read?'<span class="w-1.5 h-1.5 rounded-full bg-maroon"></span>':''}</div>
                  <div class="text-sm text-ink-mute mt-0.5">${n.body}</div>
                  <div class="text-[11px] text-slate-400 mt-1">${n.time}</div>
                </div>
                <button class="iconbtn">${ic('more-horizontal','w-4 h-4')}</button>
              </div>`).join('')}
          </div>
        </div>
      `;
    }
  };

  /* =================================================================
     SETTINGS / PERMISSIONS
  ================================================================= */
  const settings = {
    render() {
      return `
        <div class="mb-4">
          <h1 class="font-display font-bold text-2xl text-ink">Settings & Permissions</h1>
          <p class="text-sm text-ink-mute">Role-based access control matrix</p>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
          ${U.stat({label:'User roles',  value: D.ROLES.length, icon:'users-round', tone:'brown'})}
          ${U.stat({label:'Modules',     value: D.PERMISSIONS.length, icon:'layers', tone:'blue'})}
          ${U.stat({label:'2FA enabled', value:'94%', icon:'key-round', tone:'green'})}
          ${U.stat({label:'SSO',         value:'Active', sub:'Microsoft Entra ID', icon:'shield-check', tone:'gold'})}
        </div>
        <div class="card overflow-hidden">
          <div class="px-4 py-3 border-b border-slate-200 flex items-center gap-3">
            <div class="font-display font-semibold">Permissions matrix</div>
            <span class="ml-auto text-xs text-ink-mute">${ic('lock','w-3 h-3 inline')} Edits restricted to IT Admin</span>
          </div>
          <div class="overflow-x-auto">
            <table class="tbl">
              <thead>
                <tr><th>Module</th>
                  ${D.ROLES.map(r=>`<th>${r.label}</th>`).join('')}
                </tr>
              </thead>
              <tbody>
                ${D.PERMISSIONS.map(p=>`
                  <tr>
                    <td class="font-semibold">${p.module}</td>
                    ${D.ROLES.map(r=>{
                      const v = p[r.id];
                      const tone = v==='edit'?'green':v==='view'?'blue':'gray';
                      const lbl = v==='edit'?'Edit':v==='view'?'View':'—';
                      return `<td>${v==='-'?'<span class="text-ink-faint">—</span>':U.pill(lbl,tone)}</td>`;
                    }).join('')}
                  </tr>`).join('')}
              </tbody>
            </table>
          </div>
        </div>
      `;
    }
  };

  /* =================================================================
     MASTER DATABASE — staff directory across ALL employment statuses
     Search + filter all teachers (active, contract, OPL, probation,
     on-leave, alumni) without going through the Applications module.
  ================================================================= */
  const STATUS_TONES = {
    permanent: 'green',  contract: 'blue',  opl: 'amber',
    probation: 'amber',  leave: 'violet',   alumni: 'gray'
  };
  const STATUS_LABEL = {
    permanent:'Permanent', contract:'Contract', opl:'OPL',
    probation:'Probation', leave:'On Leave',    alumni:'Alumni'
  };
  const EMP_LABEL = {
    'full-time':'Full-time', 'part-time':'Part-time',
    'contract':'Contract',   'guest':'Guest'
  };

  function masterFiltered() {
    const f = STATE.masterFilter;
    let list = D.TEACHERS.slice();
    if (f.q) {
      const q = f.q.toLowerCase();
      list = list.filter(t =>
        t.name.toLowerCase().includes(q) ||
        t.id.toLowerCase().includes(q) ||
        (t.employeeNo||'').toLowerCase().includes(q) ||
        (t.email||'').toLowerCase().includes(q) ||
        (t.subject||'').toLowerCase().includes(q) ||
        (t.dept||'').toLowerCase().includes(q)
      );
    }
    if (f.dept !== 'all')       list = list.filter(t => t.dept === f.dept);
    if (f.campus !== 'all')     list = list.filter(t => t.campus === f.campus);
    if (f.status !== 'all')     list = list.filter(t => t.status === f.status);
    if (f.employment !== 'all') list = list.filter(t => t.employment === f.employment);

    const sorters = {
      name:    (a,b) => a.name.localeCompare(b.name),
      joined:  (a,b) => a.joined.localeCompare(b.joined),
      tenure:  (a,b) => a.joined.localeCompare(b.joined), // older first
      rating:  (a,b) => (b.rating||0) - (a.rating||0),
      campus:  (a,b) => a.campus.localeCompare(b.campus),
    };
    return list.sort(sorters[f.sort] || sorters.name);
  }

  const master = {
    render() {
      const f = STATE.masterFilter;
      const list = masterFiltered();
      const total = D.TEACHERS.length;
      const counts = D.TEACHERS.reduce((m,t)=>{ m[t.status]=(m[t.status]||0)+1; return m; },{});
      const depts  = Array.from(new Set(D.TEACHERS.map(t=>t.dept))).sort();
      const subj   = (s) => D.TEACHERS.filter(t=>t.dept===s.dept).length;

      return `
        <div class="space-y-4">
          <!-- Stat strip -->
          <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            ${U.stat({label:'Total staff',     value:String(total),                 icon:'users-round',     tone:'brand'})}
            ${U.stat({label:'Permanent',       value:String(counts.permanent||0),   icon:'check-circle-2',  tone:'green'})}
            ${U.stat({label:'Contract',        value:String(counts.contract||0),    icon:'file-signature',  tone:'blue'})}
            ${U.stat({label:'OPL / Probation', value:String((counts.opl||0)+(counts.probation||0)), icon:'graduation-cap', tone:'amber'})}
            ${U.stat({label:'On leave',        value:String(counts.leave||0),       icon:'plane',           tone:'violet'})}
            ${U.stat({label:'Alumni',          value:String(counts.alumni||0),      icon:'history',         tone:'gray'})}
          </div>

          <!-- Filter bar -->
          <div class="card p-3">
            <div class="flex flex-wrap items-center gap-2">
              <div class="search flex-1 min-w-[220px]">
                ${ic('search','w-4 h-4')}
                <input id="masterQ" placeholder="Search name, ID, email, subject…" value="${f.q||''}"/>
              </div>
              <select class="select select-sm" id="masterDept">
                <option value="all">All departments</option>
                ${depts.map(d=>`<option value="${d}" ${f.dept===d?'selected':''}>${d}</option>`).join('')}
              </select>
              <select class="select select-sm" id="masterCampus">
                <option value="all">All campuses</option>
                ${D.CAMPUSES.map(c=>`<option value="${c.id}" ${f.campus===c.id?'selected':''}>${c.name}</option>`).join('')}
              </select>
              <select class="select select-sm" id="masterStatus">
                <option value="all">All statuses</option>
                ${Object.entries(STATUS_LABEL).map(([k,v])=>`<option value="${k}" ${f.status===k?'selected':''}>${v}</option>`).join('')}
              </select>
              <select class="select select-sm" id="masterEmp">
                <option value="all">All employment</option>
                ${Object.entries(EMP_LABEL).map(([k,v])=>`<option value="${k}" ${f.employment===k?'selected':''}>${v}</option>`).join('')}
              </select>
              <select class="select select-sm" id="masterSort">
                <option value="name"   ${f.sort==='name'?'selected':''}>Sort: Name</option>
                <option value="joined" ${f.sort==='joined'?'selected':''}>Sort: Date joined</option>
                <option value="rating" ${f.sort==='rating'?'selected':''}>Sort: Rating</option>
                <option value="campus" ${f.sort==='campus'?'selected':''}>Sort: Campus</option>
              </select>
              <button class="btn btn-ghost btn-sm" data-action="master-reset">${ic('rotate-ccw','w-4 h-4')} Reset</button>
              <div class="ml-auto flex items-center gap-2">
                <span class="text-xs text-ink-mute">${list.length} of ${total}</span>
                <button class="btn btn-soft btn-sm" data-action="export">${ic('download','w-4 h-4')} Export CSV</button>
                <button class="btn btn-primary btn-sm" data-action="master-add">${ic('user-plus','w-4 h-4')} Add staff</button>
              </div>
            </div>
          </div>

          <!-- Results table -->
          <div class="card overflow-hidden">
            <div class="overflow-x-auto">
              <table class="tbl">
                <thead>
                  <tr>
                    <th>Staff</th>
                    <th>Employee No.</th>
                    <th>Department · Subject</th>
                    <th>Campus</th>
                    <th>Status</th>
                    <th>Employment</th>
                    <th>Joined</th>
                    <th class="text-right">Rating</th>
                    <th class="w-12"></th>
                  </tr>
                </thead>
                <tbody>
                  ${list.length ? list.map(t => `
                    <tr class="cursor-pointer" data-route="teacher" data-id="${t.id}">
                      <td>
                        <div class="flex items-center gap-3">
                          <div class="av av-sm">${H.initials(t.name)}</div>
                          <div class="min-w-0">
                            <div class="font-semibold text-ink truncate">${t.name}</div>
                            <div class="text-xs text-ink-faint truncate">${t.email}</div>
                          </div>
                        </div>
                      </td>
                      <td><code class="text-xs text-ink-mute">${t.employeeNo}</code></td>
                      <td>
                        <div class="text-sm">${t.dept}</div>
                        <div class="text-xs text-ink-faint">${t.subject}</div>
                      </td>
                      <td><span class="pill pill-gray">${(H.campus(t.campus)?.name)||t.campus}</span></td>
                      <td>${U.pill(STATUS_LABEL[t.status]||t.status, STATUS_TONES[t.status]||'gray', true)}</td>
                      <td><span class="text-xs text-ink-mute">${EMP_LABEL[t.employment]||t.employment}</span></td>
                      <td>
                        <div class="text-sm">${H.fmtDate(t.joined)}</div>
                        <div class="text-xs text-ink-faint">${t.tenure||''}</div>
                      </td>
                      <td class="text-right">
                        ${t.rating ? `<span class="font-semibold text-ink">${t.rating.toFixed(1)}</span> <span class="text-amber-500">★</span>` : '<span class="text-ink-faint">—</span>'}
                      </td>
                      <td class="text-right" onclick="event.stopPropagation()">
                        <button class="iconbtn" data-action="master-quick" data-id="${t.id}">${ic('more-horizontal','w-4 h-4')}</button>
                      </td>
                    </tr>`).join('') : `
                    <tr><td colspan="9">${U.empty('No staff match your filters','Try clearing some filters or use a different search term.','search-x')}</td></tr>
                  `}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      `;
    },
    mount() {
      const wire = (id, key) => {
        const el = document.getElementById(id);
        if (!el) return;
        const ev = el.tagName === 'INPUT' ? 'input' : 'change';
        el.addEventListener(ev, (e) => {
          STATE.masterFilter[key] = e.target.value;
          // Re-render only the master section by triggering a full re-render
          window.dispatchEvent(new CustomEvent('rerender'));
        });
      };
      wire('masterQ',     'q');
      wire('masterDept',  'dept');
      wire('masterCampus','campus');
      wire('masterStatus','status');
      wire('masterEmp',   'employment');
      wire('masterSort',  'sort');
    }
  };

  // ============ HUB PAGES (consolidated sidebar) ============
  // Each hub renders a tab-strip + delegates body to an existing page renderer.
  // Tabs are tracked in STATE.hubTab[<hubId>] and switched via [data-hub-tab].
  function hubShell(hubId, title, subtitle, tabs, ic) {
    const active = STATE.hubTab[hubId] || tabs[0].id;
    const target = (window.PAGES && window.PAGES[active]);
    const child = target && typeof target.render === 'function' ? target.render() : '';
    return `
      <div class="page">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h1 class="font-display text-2xl font-bold text-ink flex items-center gap-2">
              <i data-lucide="${ic}" class="w-6 h-6 text-brand-dark"></i> ${title}
            </h1>
            <p class="text-sm text-ink-mute mt-0.5">${subtitle}</p>
          </div>
        </div>
        <div class="tab-bar mb-4">
          ${tabs.map(t => `
            <button class="tab ${active === t.id ? 'active' : ''}"
                    data-hub="${hubId}" data-hub-tab="${t.id}">
              <i data-lucide="${t.icon}" class="w-4 h-4"></i>
              <span>${t.label}</span>
              ${t.badge ? `<span class="pill pill-amber ml-1">${t.badge}</span>` : ''}
            </button>`).join('')}
        </div>
        <div class="hub-body">${child}</div>
      </div>
    `;
  }

  const recruitment = {
    render: () => hubShell('recruitment', 'Recruitment', 'Vacancies, ATS pipeline and candidate database — all in one workspace.',
      [
        { id:'pipeline',   label:'Pipeline',   icon:'kanban-square' },
        { id:'candidates', label:'Candidates', icon:'users-round' },
        { id:'vacancies',  label:'Vacancies',  icon:'briefcase' },
      ], 'kanban-square'),
    mount() { const a = STATE.hubTab.recruitment; if (window.PAGES[a]?.mount) window.PAGES[a].mount(); }
  };

  const assessments = {
    render: () => hubShell('assessments', 'Assessments', 'Written tests, interview scoring and rubric library.',
      [
        { id:'tests',      label:'Written tests', icon:'file-text' },
        { id:'interviews', label:'Interviews',    icon:'video' },
      ], 'clipboard-check'),
    mount() { const a = STATE.hubTab.assessments; if (window.PAGES[a]?.mount) window.PAGES[a].mount(); }
  };

  const verifications = {
    render: () => hubShell('verifications', 'Verifications', 'Deposit, psychological and medical clearance — all gated by role.',
      [
        { id:'deposits', label:'Deposits',  icon:'wallet' },
        { id:'psycho',   label:'Psycho',    icon:'brain' },
        { id:'medical',  label:'Medical',   icon:'stethoscope' },
      ], 'shield-check'),
    mount() { const a = STATE.hubTab.verifications; if (window.PAGES[a]?.mount) window.PAGES[a].mount(); }
  };

  const onboarding = {
    render: () => hubShell('onboarding', 'Onboarding', 'OPL micro-teaching, 90-day probation and contract management.',
      [
        { id:'opl',       label:'OPL sessions', icon:'graduation-cap' },
        { id:'probation', label:'Probation',    icon:'timer' },
        { id:'contracts', label:'Contracts',    icon:'file-signature' },
      ], 'graduation-cap'),
    mount() { const a = STATE.hubTab.onboarding; if (window.PAGES[a]?.mount) window.PAGES[a].mount(); }
  };

  const workforce = {
    render: () => hubShell('workforce', 'Workforce', 'Master database of all teaching staff — search, filter and drill into any record.',
      [
        { id:'master',    label:'Master database', icon:'database' },
        { id:'bukuinduk', label:'Buku Induk',      icon:'book-open' },
        { id:'teacher',   label:'Teacher profile', icon:'id-card' },
      ], 'users-round'),
    mount() { const a = STATE.hubTab.workforce; if (window.PAGES[a]?.mount) window.PAGES[a].mount(); }
  };

  const governance = {
    render: () => hubShell('governance', 'Governance', 'Audit log, RBAC matrix and system settings.',
      [
        { id:'audit',    label:'Audit log',   icon:'shield-check' },
        { id:'settings', label:'Permissions', icon:'settings' },
      ], 'shield'),
    mount() { const a = STATE.hubTab.governance; if (window.PAGES[a]?.mount) window.PAGES[a].mount(); }
  };

  // ============ Expose ============
  window.PAGES = {
    careers, vacancy, apply, tracker, mydocs,
    dashboard, pipeline, candidates, candidate, vacancies,
    tests, interviews, deposits, psycho, medical, yayasan,
    opl, probation, contracts, bukuinduk, teacher,
    audit, analytics, inbox, settings,
    master,
    // hubs
    recruitment, assessments, verifications, onboarding, workforce, governance,
  };
})();
