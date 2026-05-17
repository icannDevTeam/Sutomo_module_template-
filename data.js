/* ==========================================================================
   Sutomo HR — Mock data layer (vacancies, candidates, OPL, contracts, audit)
   ==========================================================================
   All data is in-memory; ATS state changes mutate these arrays for the demo.
   ========================================================================== */

(function () {
  const today = new Date('2026-05-15');
  const ymd = (y, m, d) => `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;

  // ---------------- ROLES ----------------
  const ROLES = [
    { id: 'applicant', label: 'Public Applicant',  scope: 'public',  icon: 'user-round',     color: 'gray' },
    { id: 'hr',        label: 'HR / Admin',         scope: 'staff',   icon: 'briefcase',      color: 'brown' },
    { id: 'principal', label: 'Principal',          scope: 'staff',   icon: 'school',         color: 'gold' },
    { id: 'depthead',  label: 'Department Head',    scope: 'staff',   icon: 'users-round',    color: 'blue' },
    { id: 'mentor',    label: 'Mentor Teacher',     scope: 'staff',   icon: 'graduation-cap', color: 'green' },
    { id: 'finance',   label: 'Finance',            scope: 'staff',   icon: 'wallet',         color: 'violet' },
    { id: 'yayasan',   label: 'Yayasan Board',      scope: 'exec',    icon: 'landmark',       color: 'maroon' },
    { id: 'it',        label: 'IT Admin',           scope: 'staff',   icon: 'shield-check',   color: 'gray' },
  ];

  // ---------------- USERS (current "me" per role) ----------------
  const USERS = {
    applicant: { name: 'Aditya P. Wijaya',     email: 'aditya.wijaya@gmail.com',     dept: 'Applicant — Mathematics' },
    hr:        { name: 'Sri Lestari, S.Psi',   email: 'sri.lestari@sutomo.sch.id',   dept: 'Human Resources' },
    principal: { name: 'Drs. Budi Santoso',    email: 'budi.santoso@sutomo.sch.id',  dept: 'Principal — Senior High' },
    depthead:  { name: 'Maria Hartanto, M.Pd', email: 'maria.hartanto@sutomo.sch.id',dept: 'Head of Mathematics Dept.' },
    mentor:    { name: 'Rini Surya, M.Pd',     email: 'rini.surya@sutomo.sch.id',    dept: 'Senior Teacher — Mentor' },
    finance:   { name: 'Hendra Wijaya, S.E',   email: 'hendra.wijaya@sutomo.sch.id', dept: 'Finance & Treasury' },
    yayasan:   { name: 'Dr. Tanto Halim',      email: 'tanto.halim@yayasan-sutomo.org', dept: 'Yayasan Sutomo — Vice Chair' },
    it:        { name: 'Dimas Pratama',        email: 'dimas.pratama@sutomo.sch.id', dept: 'IT Operations' },
  };

  // ---------------- CAMPUSES & DEPARTMENTS ----------------
  const CAMPUSES = [
    { id: 'sd',  name: 'Sutomo Elementary',     short: 'SD',  city: 'Medan' },
    { id: 'smp', name: 'Sutomo Junior High',    short: 'SMP', city: 'Medan' },
    { id: 'sma', name: 'Sutomo Senior High',    short: 'SMA', city: 'Medan' },
    { id: 'int', name: 'Sutomo International',  short: 'INT', city: 'Medan' },
  ];
  const DEPTS = ['Mathematics','Science','English','Bahasa Indonesia','Mandarin','Social Studies','Arts','PE & Health','ICT','Religious Studies','Counseling'];

  // ---------------- VACANCIES ----------------
  const VACANCIES = [
    { id: 'V-2026-014', title: 'Mathematics Teacher', dept: 'Mathematics', campus: 'sma', type: 'Full-time', level: 'Mid (3–5 yrs)', openings: 2, applicants: 47, posted: '2026-04-22', closes: '2026-06-15', status: 'open',  featured: true,
      summary: 'Teach Algebra II, Pre-Calculus, and IB Math AA SL across Grade 10–12. Lead one extracurricular math olympiad club.',
      responsibilities: ['Plan & deliver 24 teaching periods/week','Assess student progress with formative & summative tools','Mentor Grade 11 homeroom of 28 students','Coach Mathematics Olympiad team weekly','Contribute to curriculum review committee'],
      requirements: ['S1/S2 in Mathematics or Math Education','Min. 3 years teaching at senior high level','English working proficiency (TOEFL ITP ≥ 525)','Familiarity with Cambridge or IB curriculum preferred','Sertifikat Pendidik (Akta IV) preferred'],
      benefits: ['Competitive salary 2x regional UMR','BPJS Kesehatan + Ketenagakerjaan','THR + 13th-month bonus','Annual professional development budget Rp 8 jt','Tuition discount for own children up to 70%'] },
    { id: 'V-2026-015', title: 'English Literature Teacher', dept: 'English', campus: 'sma', type: 'Full-time', level: 'Senior (5+ yrs)', openings: 1, applicants: 34, posted: '2026-04-25', closes: '2026-06-10', status: 'open', featured: true,
      summary: 'IB English A: Literature HL/SL specialist for Grade 11–12 with experience in Extended Essay supervision.' },
    { id: 'V-2026-016', title: 'Mandarin Language Teacher', dept: 'Mandarin', campus: 'smp', type: 'Full-time', level: 'Mid (3–5 yrs)', openings: 1, applicants: 22, posted: '2026-04-30', closes: '2026-06-20', status: 'open',
      summary: 'Native or near-native Mandarin speaker (HSK 6) to teach SMP Grade 7–9 Mandarin curriculum.' },
    { id: 'V-2026-017', title: 'Physics Teacher (IB DP)', dept: 'Science', campus: 'int', type: 'Full-time', level: 'Senior (5+ yrs)', openings: 1, applicants: 18, posted: '2026-05-01', closes: '2026-06-30', status: 'open',
      summary: 'IB Diploma Physics HL/SL teacher with strong lab pedagogy and IA supervision experience.' },
    { id: 'V-2026-018', title: 'Primary Homeroom Teacher (G3)', dept: 'Bahasa Indonesia', campus: 'sd', type: 'Full-time', level: 'Junior (1–2 yrs)', openings: 3, applicants: 61, posted: '2026-05-02', closes: '2026-06-25', status: 'open',
      summary: 'Lead a Grade 3 homeroom of 24 students, integrating thematic learning across core subjects.' },
    { id: 'V-2026-019', title: 'School Counselor (BK)', dept: 'Counseling', campus: 'smp', type: 'Full-time', level: 'Mid (3–5 yrs)', openings: 1, applicants: 12, posted: '2026-05-04', closes: '2026-07-04', status: 'open',
      summary: 'Provide guidance, counseling and parent liaison for SMP Grade 7–9. S1 Bimbingan Konseling required.' },
    { id: 'V-2026-013', title: 'Computer Science Teacher', dept: 'ICT', campus: 'sma', type: 'Full-time', level: 'Mid (3–5 yrs)', openings: 1, applicants: 39, posted: '2026-04-10', closes: '2026-05-31', status: 'closing',
      summary: 'Teach Python, web foundations, and IB Computer Science SL. Closing in 16 days.' },
    { id: 'V-2026-012', title: 'PE & Health Teacher', dept: 'PE & Health', campus: 'sd', type: 'Full-time', level: 'Junior (1–2 yrs)', openings: 1, applicants: 28, posted: '2026-04-02', closes: '2026-05-20', status: 'closing',
      summary: 'Energetic PE teacher for SD G1–G6, also coach inter-school basketball team.' },
    { id: 'V-2026-011', title: 'Visual Arts Teacher', dept: 'Arts', campus: 'sma', type: 'Part-time', level: 'Mid (3–5 yrs)', openings: 1, applicants: 9, posted: '2026-03-20', closes: '2026-05-10', status: 'closed',
      summary: 'Filled — Position closed.' },
  ];

  // helper to fill missing fields of vacancies with sensible defaults
  VACANCIES.forEach(v => {
    if (!v.responsibilities) v.responsibilities = ['Plan & deliver lessons aligned to school curriculum','Assess and report student progress','Participate in faculty meetings & PD','Support school events & extracurricular programs'];
    if (!v.requirements) v.requirements = ['Relevant S1/S2 degree','Min. teaching experience at the indicated level','English working proficiency','Sertifikat Pendidik preferred'];
    if (!v.benefits) v.benefits = ['Competitive salary','BPJS Kesehatan + Ketenagakerjaan','THR + 13th-month bonus','Professional development budget','Tuition discount for own children'];
  });

  // ---------------- CANDIDATES ----------------
  // Stage flow used everywhere
  const STAGES = [
    { id: 'applied',   label: 'Applied' },
    { id: 'screening', label: 'Screening' },
    { id: 'written',   label: 'Written Test' },
    { id: 'interview', label: 'Interview' },
    { id: 'psycho',    label: 'Psycho Test' },
    { id: 'medical',   label: 'Medical' },
    { id: 'yayasan',   label: 'Yayasan Review' },
    { id: 'opl',       label: 'OPL' },
    { id: 'active',    label: 'Active Teacher' },
    { id: 'rejected',  label: 'Rejected' },
  ];

  // small helper
  let cidSeq = 1000;
  const C = (o) => Object.assign({ id: 'C-' + (++cidSeq), gender: 'M', gradeAvg: 3.4, photo: null, source: 'careers.sutomo', priority: 'normal', notes: 0 }, o);

  const CANDIDATES = [
    C({ name: 'Aditya P. Wijaya',   gender:'M', vacancyId:'V-2026-014', stage:'opl',
        email:'aditya.wijaya@gmail.com', phone:'+62 812 3456 7890', city:'Medan', age:29,
        education:'S2 Pendidikan Matematika — Univ. Negeri Yogyakarta (2021)',
        years: 5, lastSchool:'SMA Sutomo 1 (Mengajar Praktik)', subjects:['Mathematics','Statistics'],
        appliedAt:'2026-04-23', priority:'high', score: { written: 87, interview: 88, micro: 90 },
        deposit:{ status:'verified', amount:5000000, paidAt:'2026-04-29', refundEligible:true },
        psycho:{ status:'passed', date:'2026-05-04', counselor:'PsiKlinis Medan' },
        medical:{ status:'passed', date:'2026-05-06', clinic:'Klinik Pratama Sehat' },
        yayasan:{ status:'approved', date:'2026-05-08', by:'Dr. Tanto Halim' },
        opl:{ start:'2026-05-12', sessions: [
          { n:1, date:'2026-05-12', topic:'Linear Functions G10', mentor:'Rini Surya', status:'done',  score:88, notes:'Strong opener. Good board management. Need to slow pace for differentiation.' },
          { n:2, date:'2026-05-13', topic:'Quadratics Intro G10',  mentor:'Rini Surya', status:'done',  score:86, notes:'Good worked-example structure. Q&A engagement improving.' },
          { n:3, date:'2026-05-14', topic:'Quadratic Formula G10', mentor:'Rini Surya', status:'done',  score:90, notes:'Excellent scaffold; students very responsive.' },
          { n:4, date:'2026-05-16', topic:'Sequences G11',         mentor:'Rini Surya', status:'scheduled' },
          { n:5, date:'2026-05-19', topic:'Sequences Practice',    mentor:'Rini Surya', status:'scheduled' },
          { n:6, date:'2026-05-21', topic:'Series Intro',          mentor:'Rini Surya', status:'pending' },
          { n:7, date:'2026-05-23', topic:'Series Applications',   mentor:'Rini Surya', status:'pending' },
          { n:8, date:'2026-05-26', topic:'Probability G11',       mentor:'Rini Surya', status:'pending' },
          { n:9, date:'2026-05-28', topic:'Probability Practice',  mentor:'Rini Surya', status:'pending' },
          { n:10,date:'2026-05-30', topic:'Final Demo Lesson',     mentor:'Rini Surya', status:'pending' }
        ] },
        timeline:[
          { d:'2026-04-23', t:'Application submitted', who:'Aditya P. Wijaya' },
          { d:'2026-04-24', t:'Documents verified',    who:'Sri Lestari (HR)' },
          { d:'2026-04-26', t:'Moved to Screening',    who:'Sri Lestari (HR)' },
          { d:'2026-04-28', t:'Written test scheduled',who:'Sri Lestari (HR)' },
          { d:'2026-04-29', t:'Deposit Rp 5,000,000 verified', who:'Hendra (Finance)' },
          { d:'2026-05-02', t:'Written test passed (87/100)', who:'Maria Hartanto' },
          { d:'2026-05-04', t:'Psycho test passed',    who:'PsiKlinis Medan' },
          { d:'2026-05-06', t:'Medical clearance',     who:'Klinik Pratama' },
          { d:'2026-05-08', t:'Yayasan approval',      who:'Dr. Tanto Halim' },
          { d:'2026-05-12', t:'OPL Session 1 completed', who:'Rini Surya (Mentor)' },
          { d:'2026-05-14', t:'OPL Session 3 completed (90/100)', who:'Rini Surya (Mentor)' },
        ]
    }),
    C({ name:'Citra Maharani',     gender:'F', vacancyId:'V-2026-015', stage:'yayasan',
        email:'citra.maharani@gmail.com', phone:'+62 813 7777 1212', age:32, city:'Jakarta',
        education:'S2 Sastra Inggris — Univ. Indonesia (2019)', years: 7,
        subjects:['English Literature','Creative Writing'],
        appliedAt:'2026-04-19', priority:'high', score: { written: 91, interview: 89, micro: 88 },
        deposit:{ status:'verified', amount:5000000, paidAt:'2026-04-26', refundEligible:true },
        psycho:{ status:'passed', date:'2026-04-30' },
        medical:{ status:'passed', date:'2026-05-03' },
        yayasan:{ status:'pending', queuedAt:'2026-05-05' } }),
    C({ name:'Bayu Pratama',       gender:'M', vacancyId:'V-2026-016', stage:'medical',
        education:'S1 Sastra Mandarin — Univ. Bina Nusantara (2022)', years: 3,
        subjects:['Mandarin'], appliedAt:'2026-04-21',
        score:{written:79,interview:84}, deposit:{status:'verified',amount:5000000,paidAt:'2026-04-28'},
        psycho:{status:'passed',date:'2026-05-05'}, medical:{status:'pending',scheduledAt:'2026-05-18'} }),
    C({ name:'Dewi Anggraini',     gender:'F', vacancyId:'V-2026-014', stage:'psycho',
        education:'S2 Pendidikan Matematika — UPI Bandung (2020)', years: 4,
        subjects:['Mathematics'], appliedAt:'2026-04-25',
        score:{written:84,interview:81,micro:83},
        deposit:{status:'verified',amount:5000000,paidAt:'2026-05-01'},
        psycho:{status:'pending',scheduledAt:'2026-05-17'} }),
    C({ name:'Eka Pranata',        gender:'M', vacancyId:'V-2026-017', stage:'interview',
        education:'S1 Fisika — ITB (2018)', years: 6,
        subjects:['Physics','Astronomy'], appliedAt:'2026-04-26',
        score:{written:88}, deposit:{status:'verified',amount:5000000,paidAt:'2026-05-02'} }),
    C({ name:'Fani Kurnia',        gender:'F', vacancyId:'V-2026-018', stage:'written',
        education:'S1 PGSD — UNJ (2023)', years: 1,
        subjects:['Bahasa Indonesia','Tematik'], appliedAt:'2026-04-29',
        deposit:{status:'pending', amount:5000000, dueDate:'2026-05-19'} }),
    C({ name:'Galih Saputra',      gender:'M', vacancyId:'V-2026-013', stage:'screening',
        education:'S1 Teknik Informatika — Univ. Sumatera Utara (2021)', years: 3,
        subjects:['Computer Science','Web Development'], appliedAt:'2026-05-01' }),
    C({ name:'Hana Larasati',      gender:'F', vacancyId:'V-2026-019', stage:'screening',
        education:'S1 Bimbingan Konseling — UNESA (2020)', years: 4,
        subjects:['Counseling'], appliedAt:'2026-05-02' }),
    C({ name:'Indra Maulana',      gender:'M', vacancyId:'V-2026-014', stage:'applied',
        education:'S1 Matematika — Univ. Andalas (2024)', years: 1,
        subjects:['Mathematics'], appliedAt:'2026-05-09' }),
    C({ name:'Jihan Salsabila',    gender:'F', vacancyId:'V-2026-015', stage:'applied',
        education:'S1 Sastra Inggris — UGM (2023)', years: 2,
        subjects:['English'], appliedAt:'2026-05-10' }),
    C({ name:'Kevin Halim',        gender:'M', vacancyId:'V-2026-013', stage:'applied',
        education:'S1 Sistem Informasi — BINUS (2024)', years: 1,
        subjects:['ICT'], appliedAt:'2026-05-11' }),
    C({ name:'Lina Sari',          gender:'F', vacancyId:'V-2026-018', stage:'rejected',
        education:'D3 PGSD — Univ. Terbuka (2022)', years: 1,
        subjects:['Tematik'], appliedAt:'2026-04-15', rejectedReason:'Did not meet minimum S1 requirement.' }),
    C({ name:'Muhammad Reza',      gender:'M', vacancyId:'V-2026-017', stage:'active',
        education:'S2 Fisika — Univ. Padjadjaran (2017)', years: 9,
        subjects:['Physics'], appliedAt:'2025-08-12', activatedAt:'2026-01-10', teacherId:'TCH-2026-001' }),
    C({ name:'Novita Putri',       gender:'F', vacancyId:'V-2026-016', stage:'active',
        education:'S1 Sastra Mandarin — UI (2018)', years: 5,
        subjects:['Mandarin'], appliedAt:'2025-09-04', activatedAt:'2026-02-01', teacherId:'TCH-2026-002' }),
  ];

  // ---------------- TEACHERS (Master Database — Buku Induk Guru) ----------------
  // Status: permanent | contract | opl | probation | leave | alumni
  // Employment: full-time | part-time | contract | guest
  const TEACHERS = [
    { id:'TCH-2024-018', employeeNo:'EMP-2018-014', nik:'3174012345670001',
      name:'Rini Surya, M.Pd', gender:'F', dob:'1985-03-12',
      email:'rini.surya@sutomo.sch.id', phone:'+62 812-3456-7890',
      subject:'Mathematics', dept:'Mathematics', campus:'sma',
      status:'permanent', employment:'full-time',
      joined:'2018-07-01', tenure:'7y 10m',
      contract:'Permanent (Guru Tetap)', contractEnd:null,
      education:'M.Pd Mathematics — Universitas Indonesia',
      certifications:['Sertifikasi Pendidik (Serdik)','Cambridge IGCSE Examiner'],
      languages:['Indonesian','English'], city:'Jakarta',
      mentorOf:['C-1001'], rating:4.7, lastReview:'2025-12-15' },

    { id:'TCH-2025-006', employeeNo:'EMP-2015-009', nik:'3174015678900002',
      name:'Maria Hartanto, M.Pd', gender:'F', dob:'1980-09-22',
      email:'maria.hartanto@sutomo.sch.id', phone:'+62 813-2222-1111',
      subject:'Mathematics', dept:'Mathematics', campus:'sma',
      status:'permanent', employment:'full-time',
      joined:'2015-07-01', tenure:'10y 10m',
      contract:'Permanent — Dept. Head', contractEnd:null,
      education:'M.Pd Mathematics Education — UNESA',
      certifications:['Serdik','School Leadership Cert.'],
      languages:['Indonesian','English'], city:'Jakarta',
      rating:4.9, lastReview:'2025-12-15' },

    { id:'TCH-2026-001', employeeNo:'EMP-2026-001', nik:'3174011122330003',
      name:'Muhammad Reza, M.Si', gender:'M', dob:'1990-11-08',
      email:'m.reza@sutomo.sch.id', phone:'+62 811-9876-5432',
      subject:'Physics', dept:'Science', campus:'int',
      status:'contract', employment:'full-time',
      joined:'2026-01-10', tenure:'4m',
      contract:'1-yr Contract', contractEnd:'2027-01-09',
      education:'M.Si Physics — ITB',
      certifications:['IBDP Physics Workshop Cat. 1'],
      languages:['Indonesian','English'], city:'Bandung',
      rating:4.4, lastReview:'2026-04-01' },

    { id:'TCH-2026-002', employeeNo:'EMP-2026-002', nik:'3174014455660004',
      name:'Novita Putri, S.S', gender:'F', dob:'1992-06-15',
      email:'novita.putri@sutomo.sch.id', phone:'+62 815-3344-5566',
      subject:'Mandarin', dept:'Languages', campus:'smp',
      status:'contract', employment:'full-time',
      joined:'2026-02-01', tenure:'3m',
      contract:'1-yr Contract', contractEnd:'2027-01-31',
      education:'S.S Chinese Literature — Universitas Indonesia',
      certifications:['HSK Level 6','TCSOL Certificate'],
      languages:['Indonesian','English','Mandarin'], city:'Jakarta',
      rating:4.5, lastReview:'2026-04-12' },

    { id:'TCH-2024-027', employeeNo:'EMP-2019-021', nik:'3174017788990005',
      name:'Hendra Wijoyo, S.Pd', gender:'M', dob:'1987-02-25',
      email:'hendra.w@sutomo.sch.id', phone:'+62 812-7777-8888',
      subject:'PE & Health', dept:'Physical Education', campus:'sd',
      status:'permanent', employment:'full-time',
      joined:'2019-07-01', tenure:'6y 10m',
      contract:'Permanent', contractEnd:null,
      education:'S.Pd Physical Education — UNJ',
      certifications:['First Aid (PMI)','Coaching Cert. Level 2'],
      languages:['Indonesian','English'], city:'Jakarta',
      rating:4.6, lastReview:'2025-11-30' },

    { id:'TCH-2023-014', employeeNo:'EMP-2014-007', nik:'3174012233440006',
      name:'Sri Mulyani, M.Pd', gender:'F', dob:'1978-12-03',
      email:'sri.mulyani@sutomo.sch.id', phone:'+62 819-1010-2020',
      subject:'Bahasa Indonesia', dept:'Languages', campus:'smp',
      status:'permanent', employment:'full-time',
      joined:'2014-07-01', tenure:'11y 10m',
      contract:'Permanent — Senior', contractEnd:null,
      education:'M.Pd Bahasa Indonesia — UNJ',
      certifications:['Serdik','Kurikulum Merdeka Trainer'],
      languages:['Indonesian','English'], city:'Jakarta',
      rating:4.8, lastReview:'2025-12-10' },

    { id:'TCH-2020-009', employeeNo:'EMP-2010-003', nik:'3174015544330007',
      name:'Drs. Budi Santoso', gender:'M', dob:'1972-04-18',
      email:'budi.santoso@sutomo.sch.id', phone:'+62 812-4040-5050',
      subject:'School Leadership', dept:'Administration', campus:'sma',
      status:'permanent', employment:'full-time',
      joined:'2010-07-01', tenure:'15y 10m',
      contract:'Permanent — Principal SMA', contractEnd:null,
      education:'Drs. Education Management — UPI',
      certifications:['Cambridge School Leader','Asesor BAN-SM'],
      languages:['Indonesian','English'], city:'Jakarta',
      rating:4.9, lastReview:'2025-12-20' },

    { id:'TCH-2022-011', employeeNo:'EMP-2017-018', nik:'3174019988770008',
      name:'Lia Kartika, S.Pd', gender:'F', dob:'1989-08-30',
      email:'lia.kartika@sutomo.sch.id', phone:'+62 813-6060-7070',
      subject:'English', dept:'Languages', campus:'smp',
      status:'permanent', employment:'full-time',
      joined:'2017-07-01', tenure:'8y 10m',
      contract:'Permanent', contractEnd:null,
      education:'S.Pd English Education — UNJ',
      certifications:['TEFL','Cambridge CELTA'],
      languages:['Indonesian','English','French (basic)'], city:'Jakarta',
      rating:4.7, lastReview:'2025-12-05' },

    { id:'TCH-2021-004', employeeNo:'EMP-2016-011', nik:'3174011357920009',
      name:'Agus Pranoto, S.Si', gender:'M', dob:'1986-01-09',
      email:'agus.pranoto@sutomo.sch.id', phone:'+62 811-3535-2424',
      subject:'Biology', dept:'Science', campus:'sma',
      status:'permanent', employment:'full-time',
      joined:'2016-07-01', tenure:'9y 10m',
      contract:'Permanent', contractEnd:null,
      education:'S.Si Biology — UGM',
      certifications:['Serdik','BNSP Lab Assessor'],
      languages:['Indonesian','English'], city:'Yogyakarta',
      rating:4.6, lastReview:'2025-12-12' },

    { id:'TCH-2025-018', employeeNo:'EMP-2025-018', nik:'3174012468100010',
      name:'Felicia Tan, B.Ed', gender:'F', dob:'1995-05-21',
      email:'felicia.tan@sutomo.sch.id', phone:'+62 812-9090-1212',
      subject:'Primary Class Teacher', dept:'Primary', campus:'sd',
      status:'probation', employment:'full-time',
      joined:'2025-08-01', tenure:'9m',
      contract:'Probation (90 days extended)', contractEnd:'2026-08-01',
      education:'B.Ed Primary — NIE Singapore',
      certifications:['IB PYP Cat. 1'],
      languages:['Indonesian','English','Mandarin'], city:'Jakarta',
      rating:4.3, lastReview:'2026-04-20' },

    { id:'TCH-2024-031', employeeNo:'EMP-2020-024', nik:'3174013579240011',
      name:'Tono Hermawan, S.Pd', gender:'M', dob:'1988-10-14',
      email:'tono.h@sutomo.sch.id', phone:'+62 815-1717-1818',
      subject:'History', dept:'Social Studies', campus:'sma',
      status:'leave', employment:'full-time',
      joined:'2020-07-01', tenure:'5y 10m',
      contract:'Permanent · on Sabbatical', contractEnd:null,
      leaveReason:'Postgraduate study leave (returns 2026-08-01)',
      education:'S.Pd History — UNJ',
      certifications:['Serdik'],
      languages:['Indonesian','English'], city:'Jakarta',
      rating:4.5, lastReview:'2025-06-01' },

    { id:'TCH-2023-022', employeeNo:'EMP-2018-022', nik:'3174014681350012',
      name:'Citra Anggraini, S.Sn', gender:'F', dob:'1991-07-07',
      email:'citra.a@sutomo.sch.id', phone:'+62 819-2424-3636',
      subject:'Visual Arts', dept:'Arts', campus:'smp',
      status:'permanent', employment:'part-time',
      joined:'2018-07-01', tenure:'7y 10m',
      contract:'Permanent · Part-time (3 days/week)', contractEnd:null,
      education:'S.Sn Fine Arts — ISI Yogyakarta',
      certifications:['Adobe Certified Educator'],
      languages:['Indonesian','English'], city:'Jakarta',
      rating:4.7, lastReview:'2025-11-25' },

    { id:'TCH-2026-PRB-A', employeeNo:'EMP-2026-OPL-A', nik:'3174015792460013',
      name:'Aditya P. Wijaya, S.Pd', gender:'M', dob:'1994-09-18',
      email:'aditya.w@sutomo.sch.id', phone:'+62 812-1111-2222',
      subject:'Mathematics', dept:'Mathematics', campus:'sma',
      status:'opl', employment:'full-time',
      joined:'2026-05-12', tenure:'<1m',
      contract:'OPL Probation (10 sessions / 90 days)', contractEnd:'2026-08-10',
      education:'S.Pd Mathematics — Universitas Indonesia',
      certifications:[],
      languages:['Indonesian','English'], city:'Jakarta',
      rating:null },

    { id:'TCH-2019-007', employeeNo:'EMP-2014-005', nik:'3174016802570014',
      name:'Ratna Dewi, M.Sc', gender:'F', dob:'1976-11-29',
      email:'ratna.dewi@alumni.sutomo.sch.id', phone:'+62 813-5050-6060',
      subject:'Chemistry', dept:'Science', campus:'sma',
      status:'alumni', employment:'full-time',
      joined:'2014-07-01', tenure:'10y 6m',
      contract:'Resigned — Jan 2025 (joined int. school)', contractEnd:'2025-01-15',
      education:'M.Sc Chemistry — NTU Singapore',
      certifications:['IBDP Chemistry'],
      languages:['Indonesian','English'], city:'Singapore',
      rating:4.8, lastReview:'2024-12-10' },

    { id:'TCH-2022-019', employeeNo:'EMP-2019-013', nik:'3174017913680015',
      name:'Pdt. Yohanes Tanu, M.Th', gender:'M', dob:'1981-02-11',
      email:'yohanes.tanu@sutomo.sch.id', phone:'+62 812-7878-9090',
      subject:'Religion (Christian)', dept:'Religion & Character', campus:'sma',
      status:'permanent', employment:'full-time',
      joined:'2019-01-15', tenure:'7y 4m',
      contract:'Permanent — Chaplain', contractEnd:null,
      education:'M.Th Theology — STT Jakarta',
      certifications:['Ordained Pastor'],
      languages:['Indonesian','English','Hebrew (basic)'], city:'Jakarta',
      rating:4.8, lastReview:'2025-12-08' },

    { id:'TCH-2025-002', employeeNo:'EMP-2025-002', nik:'3174018024790016',
      name:'Alia Rahman, S.Pd', gender:'F', dob:'1996-12-02',
      email:'alia.rahman@sutomo.sch.id', phone:'+62 819-8181-1919',
      subject:'Primary Class Teacher', dept:'Primary', campus:'sd',
      status:'permanent', employment:'full-time',
      joined:'2025-07-01', tenure:'10m',
      contract:'Permanent (post-probation)', contractEnd:null,
      education:'S.Pd Primary Education — UPI',
      certifications:['Serdik (in process)'],
      languages:['Indonesian','English','Arabic (basic)'], city:'Bandung',
      rating:4.5, lastReview:'2026-04-25' },
  ];

  // ---------------- DEPOSITS (Finance queue) ----------------
  const DEPOSITS = CANDIDATES.filter(c => c.deposit).map(c => ({
    candidateId: c.id, candidateName: c.name, amount: c.deposit.amount,
    status: c.deposit.status, paidAt: c.deposit.paidAt || null,
    dueDate: c.deposit.dueDate || null, refundEligible: !!c.deposit.refundEligible,
    receipt: c.deposit.status !== 'pending' ? `RCPT-${c.id}.pdf` : null,
    bank: 'BCA · 0123-456-789'
  }));

  // ---------------- INTERVIEWS / EVALUATIONS ----------------
  const INTERVIEWS = [
    { id:'INT-301', candidateId:'C-1005', date:'2026-05-16', time:'09:30', room:'Meeting Room A',
      panel:['Drs. Budi Santoso','Maria Hartanto','Sri Lestari'], type:'Panel Interview', status:'scheduled' },
    { id:'INT-302', candidateId:'C-1004', date:'2026-05-17', time:'11:00', room:'Meeting Room B',
      panel:['Drs. Budi Santoso','Maria Hartanto'], type:'Micro-Teaching', status:'scheduled' },
    { id:'INT-303', candidateId:'C-1002', date:'2026-05-04', time:'10:00', room:'Meeting Room A',
      panel:['Drs. Budi Santoso','HoD English'], type:'Final Panel', status:'completed', recommendation:'Strong Hire' },
  ];
  const EVAL_RUBRIC = [
    { id:'mastery',   label:'Subject Mastery',          weight: 25 },
    { id:'mgmt',      label:'Classroom Management',     weight: 20 },
    { id:'comm',      label:'Communication & Clarity',  weight: 15 },
    { id:'engage',    label:'Student Engagement',       weight: 15 },
    { id:'method',    label:'Teaching Methodology',     weight: 15 },
    { id:'language',  label:'English / Mandarin Fluency', weight: 10 },
  ];

  // ---------------- AUDIT LOG ----------------
  const AUDIT = [
    { ts:'2026-05-14 16:42', user:'Drs. Budi Santoso', role:'Principal',  action:'override.score',  target:'C-1001', from:'87', to:'90', note:'Adjusted micro-teaching score after panel review.' },
    { ts:'2026-05-14 14:08', user:'Sri Lestari',       role:'HR',         action:'stage.move',      target:'C-1003', from:'psycho', to:'medical', note:'Psycho cleared — moved forward.' },
    { ts:'2026-05-14 11:30', user:'Hendra Wijaya',     role:'Finance',    action:'deposit.verify',  target:'C-1004', from:'pending', to:'verified', note:'Bank slip matched.' },
    { ts:'2026-05-13 17:20', user:'Dr. Tanto Halim',   role:'Yayasan',    action:'approval.grant',  target:'C-1001', from:'pending', to:'approved', note:'OPL track authorized.' },
    { ts:'2026-05-13 09:15', user:'Sri Lestari',       role:'HR',         action:'vacancy.publish', target:'V-2026-019', from:'draft', to:'open', note:'Counselor role posted to public portal.' },
    { ts:'2026-05-12 18:02', user:'Rini Surya',        role:'Mentor',     action:'opl.session',     target:'C-1001/S1', from:'-', to:'completed (88)', note:'Strong opener.' },
    { ts:'2026-05-12 13:44', user:'Maria Hartanto',    role:'Dept Head',  action:'eval.submit',     target:'C-1001', from:'-', to:'Strong Hire', note:'Evaluation finalized.' },
    { ts:'2026-05-11 10:11', user:'Dimas Pratama',     role:'IT',         action:'role.assign',     target:'TCH-2026-001', from:'candidate', to:'teacher.contract', note:'Auto-provisioned.' },
    { ts:'2026-05-10 09:55', user:'Hendra Wijaya',     role:'Finance',    action:'refund.eligible', target:'C-9988',  from:'verified', to:'refund-eligible', note:'Withdrawn by candidate.' },
    { ts:'2026-05-09 16:00', user:'Sri Lestari',       role:'HR',         action:'reject',          target:'C-1012', from:'screening', to:'rejected', note:'Did not meet minimum S1 requirement.' },
  ];

  // ---------------- NOTIFICATIONS ----------------
  const NOTIFICATIONS = [
    { id:'N1', icon:'calendar-clock',   title:'Interview reminder', body:'Eka Pranata · Tomorrow 09:30 · Meeting Room A', time:'2 h ago', read:false, role:['hr','principal','depthead'] },
    { id:'N2', icon:'wallet',           title:'Deposit pending',    body:'Fani Kurnia (Rp 5,000,000) · Due in 4 days',     time:'5 h ago', read:false, role:['hr','finance'] },
    { id:'N3', icon:'shield-check',     title:'Yayasan approval',   body:'Citra Maharani awaiting board decision',         time:'1 d ago', read:false, role:['hr','yayasan'] },
    { id:'N4', icon:'graduation-cap',   title:'OPL milestone',      body:'Aditya Wijaya · 3 of 10 sessions complete',     time:'1 d ago', read:true,  role:['hr','mentor','principal'] },
    { id:'N5', icon:'file-warning',     title:'Contract expiring',  body:'Muhammad Reza · contract renewal in 240 days',  time:'2 d ago', read:true,  role:['hr','principal'] },
    { id:'N6', icon:'check-circle-2',   title:'Application received',body:'Vacancy V-2026-014 · Mathematics Teacher',     time:'3 d ago', read:true,  role:['applicant'] },
    { id:'N7', icon:'mail',             title:'Written test invite',body:'You are invited for written test on May 21',    time:'1 d ago', read:false, role:['applicant'] },
  ];

  // ---------------- HR ANALYTICS ----------------
  const ANALYTICS = {
    funnel: [
      { stage:'Applied',        n: 287 },
      { stage:'Screening',      n: 164 },
      { stage:'Written Test',   n:  98 },
      { stage:'Interview',      n:  62 },
      { stage:'Psycho Test',    n:  41 },
      { stage:'Medical',        n:  34 },
      { stage:'Yayasan Review', n:  23 },
      { stage:'OPL',            n:  15 },
      { stage:'Active Teacher', n:  11 },
    ],
    timeToHire: { median: 42, p90: 71 }, // days
    sources:   [{k:'Careers Portal', v:62}, {k:'Referral', v:18}, {k:'JobStreet', v:12}, {k:'LinkedIn', v:8}],
    deptHires: [{k:'Math', v:4}, {k:'Science', v:3}, {k:'English', v:2}, {k:'Mandarin', v:1}, {k:'PE', v:1}],
    monthly:   [12, 15, 18, 14, 19, 22, 20, 17, 21, 24, 26, 23],
    retention: { y1: 0.93, y2: 0.86, y3: 0.81 },
    probation: { passed: 22, failed: 3, active: 4 },
  };

  // ---------------- SETTINGS / PERMISSIONS MATRIX ----------------
  const PERMISSIONS = [
    { module:'Public Portal',         applicant:'edit', hr:'edit',   principal:'view', depthead:'view', mentor:'-',    finance:'-',    yayasan:'-',    it:'edit' },
    { module:'Vacancy Management',    applicant:'-',    hr:'edit',   principal:'edit', depthead:'edit', mentor:'-',    finance:'-',    yayasan:'view', it:'view' },
    { module:'ATS Pipeline',          applicant:'-',    hr:'edit',   principal:'edit', depthead:'edit', mentor:'view', finance:'view', yayasan:'view', it:'view' },
    { module:'Written Tests',         applicant:'-',    hr:'edit',   principal:'edit', depthead:'edit', mentor:'-',    finance:'-',    yayasan:'view', it:'view' },
    { module:'Interviews',            applicant:'-',    hr:'edit',   principal:'edit', depthead:'edit', mentor:'-',    finance:'-',    yayasan:'view', it:'view' },
    { module:'Deposit Management',    applicant:'view', hr:'view',   principal:'-',    depthead:'-',    mentor:'-',    finance:'edit', yayasan:'view', it:'-' },
    { module:'Psycho Verification',   applicant:'-',    hr:'edit',   principal:'view', depthead:'-',    mentor:'-',    finance:'-',    yayasan:'view', it:'-' },
    { module:'Medical Verification',  applicant:'-',    hr:'edit',   principal:'view', depthead:'-',    mentor:'-',    finance:'-',    yayasan:'view', it:'-' },
    { module:'Yayasan Approval',      applicant:'-',    hr:'view',   principal:'view', depthead:'-',    mentor:'-',    finance:'view', yayasan:'edit', it:'-' },
    { module:'OPL Tracker',           applicant:'-',    hr:'edit',   principal:'edit', depthead:'view', mentor:'edit', finance:'-',    yayasan:'view', it:'view' },
    { module:'Buku Induk Guru',       applicant:'-',    hr:'edit',   principal:'view', depthead:'view', mentor:'view', finance:'view', yayasan:'view', it:'view' },
    { module:'Audit Log',             applicant:'-',    hr:'view',   principal:'view', depthead:'-',    mentor:'-',    finance:'-',    yayasan:'view', it:'edit' },
    { module:'HR Analytics',          applicant:'-',    hr:'view',   principal:'view', depthead:'view', mentor:'-',    finance:'view', yayasan:'view', it:'view' },
  ];

  // ---------------- SIDEBAR / ROUTE GROUPS PER ROLE ----------------
  const ROUTE_GROUPS = {
    applicant: [
      { label:'Careers', items: [
        { id:'careers',   label:'Open Vacancies',   icon:'briefcase' },
        { id:'apply',     label:'Apply Now',        icon:'send' },
      ]},
      { label:'My Application', items: [
        { id:'tracker',   label:'Application Status', icon:'list-checks', badge: 1 },
        { id:'mydocs',    label:'My Documents',     icon:'folder' },
        { id:'inbox',     label:'Notifications',    icon:'bell', badge: 2 },
      ]},
    ],
    hr: [
      { label:'Overview', items: [
        { id:'dashboard',     label:'Dashboard',        icon:'layout-dashboard' },
        { id:'analytics',     label:'Analytics',        icon:'bar-chart-3' },
      ]},
      { label:'Hiring', items: [
        { id:'recruitment',   label:'Recruitment',      icon:'kanban' },
        { id:'assessments',   label:'Assessments',      icon:'clipboard-check' },
        { id:'verifications', label:'Verifications',    icon:'shield-check' },
        { id:'yayasan',       label:'Yayasan Approval', icon:'landmark' },
      ]},
      { label:'People', items: [
        { id:'master',        label:'Master Database',  icon:'database' },
        { id:'onboarding',    label:'Onboarding',       icon:'graduation-cap' },
        { id:'workforce',     label:'Workforce',        icon:'users-round' },
      ]},
      { label:'Admin', items: [
        { id:'governance',    label:'Governance',       icon:'settings' },
        { id:'inbox',         label:'Inbox',            icon:'bell', badge: 3 },
      ]},
    ],
    principal: [
      { label:'Overview', items: [
        { id:'dashboard',     label:'Dashboard',        icon:'layout-dashboard' },
        { id:'analytics',     label:'Analytics',        icon:'bar-chart-3' },
      ]},
      { label:'Hiring', items: [
        { id:'recruitment',   label:'Recruitment',      icon:'kanban' },
        { id:'assessments',   label:'Assessments',      icon:'clipboard-check' },
      ]},
      { label:'People', items: [
        { id:'master',        label:'Master Database',  icon:'database' },
        { id:'onboarding',    label:'Onboarding',       icon:'graduation-cap' },
        { id:'workforce',     label:'Workforce',        icon:'users-round' },
      ]},
      { label:'Admin', items: [
        { id:'inbox',         label:'Inbox',            icon:'bell', badge:2 },
      ]},
    ],
    depthead: [
      { label:'Workspace', items: [
        { id:'dashboard',     label:'Dashboard',        icon:'layout-dashboard' },
        { id:'analytics',     label:'Analytics',        icon:'bar-chart-3' },
        { id:'recruitment',   label:'My Department',    icon:'kanban' },
        { id:'assessments',   label:'Assessments',      icon:'clipboard-check' },
        { id:'inbox',         label:'Inbox',            icon:'bell', badge:1 },
      ]},
    ],
    mentor: [
      { label:'Mentoring', items: [
        { id:'dashboard',     label:'My Mentees',       icon:'layout-dashboard' },
        { id:'onboarding',    label:'Onboarding',       icon:'graduation-cap' },
        { id:'inbox',         label:'Inbox',            icon:'bell' },
      ]},
    ],
    finance: [
      { label:'Finance', items: [
        { id:'dashboard',     label:'Dashboard',        icon:'layout-dashboard' },
        { id:'verifications', label:'Deposits',         icon:'wallet', badge:2 },
        { id:'analytics',     label:'Reports',          icon:'bar-chart-3' },
        { id:'governance',    label:'Audit Log',        icon:'shield-check' },
        { id:'inbox',         label:'Inbox',            icon:'bell' },
      ]},
    ],
    yayasan: [
      { label:'Board', items: [
        { id:'dashboard',     label:'Executive View',   icon:'layout-dashboard' },
        { id:'yayasan',       label:'Approval Center',  icon:'landmark', badge:1 },
        { id:'master',        label:'Master Database',  icon:'database' },
        { id:'workforce',     label:'Workforce',        icon:'users-round' },
        { id:'analytics',     label:'Analytics',        icon:'bar-chart-3' },
        { id:'governance',    label:'Audit Log',        icon:'shield-check' },
      ]},
    ],
    it: [
      { label:'Operations', items: [
        { id:'dashboard',     label:'Dashboard',        icon:'layout-dashboard' },
        { id:'governance',    label:'Governance',       icon:'settings' },
      ]},
    ],
  };

  // ---------------- ROUTES ALLOWED PER ROLE ----------------
  // Hubs: recruitment, assessments, verifications, onboarding, workforce, governance
  // Leaf routes remain reachable via deep-linking (notifications, dashboard cards).
  const ROUTES = {
    applicant: ['careers','vacancy','apply','tracker','mydocs','inbox'],
    hr:        ['dashboard','recruitment','assessments','verifications','onboarding','workforce','governance','analytics','yayasan','inbox',
                'pipeline','candidates','candidate','vacancies','vacancy','tests','interviews','deposits','psycho','medical','opl','probation','contracts','master','bukuinduk','teacher','audit','settings'],
    principal: ['dashboard','recruitment','assessments','onboarding','workforce','analytics','inbox',
                'pipeline','candidate','interviews','tests','opl','probation','master','bukuinduk','teacher'],
    depthead:  ['dashboard','recruitment','assessments','analytics','inbox',
                'pipeline','candidate','interviews','tests'],
    mentor:    ['dashboard','onboarding','inbox',
                'opl','probation','candidate'],
    finance:   ['dashboard','verifications','analytics','governance','inbox',
                'deposits','audit','candidate'],
    yayasan:   ['dashboard','yayasan','workforce','analytics','governance',
                'candidate','audit','master','bukuinduk','teacher'],
    it:        ['dashboard','governance',
                'audit','settings'],
  };

  // ---------------- STATE ----------------
  const STATE = {
    role: 'hr',
    route: 'dashboard',
    routeParam: null,
    sidebarOpen: false,
    candidateTab: 'overview',
    activeCandidateId: 'C-1001',
    activeVacancyId:   'V-2026-014',
    activeTeacherId:   'TCH-2024-018',
    applyStep: 1,
    applyData: {},
    pipelineFilter: { dept: 'all', campus: 'all', q: '' },
    masterFilter:   { q:'', dept:'all', campus:'all', status:'all', employment:'all', sort:'name' },
    notifOpen: false,
    // active tab inside each hub page
    hubTab: {
      recruitment:   'pipeline',
      assessments:   'tests',
      verifications: 'deposits',
      onboarding:    'opl',
      workforce:     'master',
      governance:    'audit',
    },
  };

  // expose
  window.DB = {
    ROLES, USERS, CAMPUSES, DEPTS, VACANCIES, CANDIDATES, TEACHERS,
    DEPOSITS, INTERVIEWS, EVAL_RUBRIC, STAGES, AUDIT, NOTIFICATIONS,
    ANALYTICS, PERMISSIONS, ROUTE_GROUPS, ROUTES,
  };
  window.STATE = STATE;
  window.HELPERS = {
    today, ymd,
    fmtIDR: (n) => 'Rp ' + n.toLocaleString('id-ID'),
    fmtDate: (d) => {
      if (!d) return '—';
      const dt = new Date(d);
      return dt.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
    },
    daysBetween: (a, b) => Math.round((new Date(b) - new Date(a)) / 86400000),
    candidate: (id) => CANDIDATES.find(c => c.id === id),
    vacancy:   (id) => VACANCIES.find(v => v.id === id),
    teacher:   (id) => TEACHERS.find(t => t.id === id),
    campus:    (id) => CAMPUSES.find(c => c.id === id),
    stageLabel:(id) => (STAGES.find(s => s.id === id) || {}).label || id,
    initials:  (n) => n.split(' ').filter(Boolean).slice(0,2).map(s=>s[0]).join('').toUpperCase(),
    countByStage: () => {
      const o = {}; STAGES.forEach(s => o[s.id] = 0);
      CANDIDATES.forEach(c => { o[c.stage] = (o[c.stage]||0)+1; });
      return o;
    }
  };
})();
