/* Sutomo HR — Data layer (mock; mirrors hr/data.js).
   Replace with Inertia props from Laravel controllers later. */

export const today = new Date('2026-05-15');

export type Role = 'applicant'|'hr'|'principal'|'depthead'|'mentor'|'finance'|'yayasan'|'it';

export const ROLES = [
  { id: 'applicant', label: 'Public Applicant',  scope: 'public',  icon: 'user-round',     color: 'gray' },
  { id: 'hr',        label: 'HR / Admin',         scope: 'staff',   icon: 'briefcase',      color: 'brown' },
  { id: 'principal', label: 'Principal',          scope: 'staff',   icon: 'school',         color: 'gold' },
  { id: 'depthead',  label: 'Department Head',    scope: 'staff',   icon: 'users-round',    color: 'blue' },
  { id: 'mentor',    label: 'Mentor Teacher',     scope: 'staff',   icon: 'graduation-cap', color: 'green' },
  { id: 'finance',   label: 'Finance',            scope: 'staff',   icon: 'wallet',         color: 'violet' },
  { id: 'yayasan',   label: 'Yayasan Board',      scope: 'exec',    icon: 'landmark',       color: 'maroon' },
  { id: 'it',        label: 'IT Admin',           scope: 'staff',   icon: 'shield-check',   color: 'gray' },
] as const;

export const USERS: Record<Role, { name: string; email: string; dept: string }> = {
  applicant: { name: 'Aditya P. Wijaya',     email: 'aditya.wijaya@gmail.com',     dept: 'Applicant — Mathematics' },
  hr:        { name: 'Sri Lestari, S.Psi',   email: 'sri.lestari@sutomo.sch.id',   dept: 'Human Resources' },
  principal: { name: 'Drs. Budi Santoso',    email: 'budi.santoso@sutomo.sch.id',  dept: 'Principal — Senior High' },
  depthead:  { name: 'Maria Hartanto, M.Pd', email: 'maria.hartanto@sutomo.sch.id',dept: 'Head of Mathematics Dept.' },
  mentor:    { name: 'Rini Surya, M.Pd',     email: 'rini.surya@sutomo.sch.id',    dept: 'Senior Teacher — Mentor' },
  finance:   { name: 'Hendra Wijaya, S.E',   email: 'hendra.wijaya@sutomo.sch.id', dept: 'Finance & Treasury' },
  yayasan:   { name: 'Dr. Tanto Halim',      email: 'tanto.halim@yayasan-sutomo.org', dept: 'Yayasan Sutomo — Vice Chair' },
  it:        { name: 'Dimas Pratama',        email: 'dimas.pratama@sutomo.sch.id', dept: 'IT Operations' },
};

export const CAMPUSES = [
  { id: 'sd',  name: 'Sutomo Elementary',     short: 'SD',  city: 'Medan' },
  { id: 'smp', name: 'Sutomo Junior High',    short: 'SMP', city: 'Medan' },
  { id: 'sma', name: 'Sutomo Senior High',    short: 'SMA', city: 'Medan' },
  { id: 'int', name: 'Sutomo International',  short: 'INT', city: 'Medan' },
];

export const DEPTS = ['Mathematics','Science','English','Bahasa Indonesia','Mandarin','Social Studies','Arts','PE & Health','ICT','Religious Studies','Counseling'];

export const STAGES = [
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

export const VACANCIES = [
  { id: 'V-2026-014', title: 'Mathematics Teacher', dept: 'Mathematics', campus: 'sma', type: 'Full-time', level: 'Mid (3–5 yrs)', openings: 2, applicants: 47, posted: '2026-04-22', closes: '2026-06-15', status: 'open', featured: true,
    summary: 'Teach Algebra II, Pre-Calculus, and IB Math AA SL across Grade 10–12. Lead one extracurricular math olympiad club.' },
  { id: 'V-2026-015', title: 'English Literature Teacher', dept: 'English', campus: 'sma', type: 'Full-time', level: 'Senior (5+ yrs)', openings: 1, applicants: 34, posted: '2026-04-25', closes: '2026-06-10', status: 'open', featured: true,
    summary: 'IB English A: Literature HL/SL specialist for Grade 11–12 with experience in Extended Essay supervision.' },
  { id: 'V-2026-016', title: 'Mandarin Language Teacher', dept: 'Mandarin', campus: 'smp', type: 'Full-time', level: 'Mid (3–5 yrs)', openings: 1, applicants: 22, posted: '2026-04-30', closes: '2026-06-20', status: 'open',
    summary: 'Native or near-native Mandarin speaker (HSK 6) to teach SMP Grade 7–9 Mandarin curriculum.' },
  { id: 'V-2026-017', title: 'Physics Teacher (IB DP)', dept: 'Science', campus: 'int', type: 'Full-time', level: 'Senior (5+ yrs)', openings: 1, applicants: 18, posted: '2026-05-01', closes: '2026-06-30', status: 'open',
    summary: 'IB Diploma Physics HL/SL teacher with strong lab pedagogy and IA supervision experience.' },
  { id: 'V-2026-018', title: 'Primary Homeroom Teacher (G3)', dept: 'Bahasa Indonesia', campus: 'sd', type: 'Full-time', level: 'Junior (1–2 yrs)', openings: 3, applicants: 61, posted: '2026-05-02', closes: '2026-06-25', status: 'open',
    summary: 'Lead a Grade 3 homeroom of 24 students, integrating thematic learning across core subjects.' },
  { id: 'V-2026-019', title: 'School Counselor (BK)', dept: 'Counseling', campus: 'smp', type: 'Full-time', level: 'Mid (3–5 yrs)', openings: 1, applicants: 12, posted: '2026-05-04', closes: '2026-07-04', status: 'open',
    summary: 'Provide guidance, counseling and parent liaison for SMP Grade 7–9.' },
  { id: 'V-2026-013', title: 'Computer Science Teacher', dept: 'ICT', campus: 'sma', type: 'Full-time', level: 'Mid (3–5 yrs)', openings: 1, applicants: 39, posted: '2026-04-10', closes: '2026-05-31', status: 'closing',
    summary: 'Teach Python, web foundations, and IB Computer Science SL. Closing in 16 days.' },
  { id: 'V-2026-012', title: 'PE & Health Teacher', dept: 'PE & Health', campus: 'sd', type: 'Full-time', level: 'Junior (1–2 yrs)', openings: 1, applicants: 28, posted: '2026-04-02', closes: '2026-05-20', status: 'closing',
    summary: 'Energetic PE teacher for SD G1–G6.' },
  { id: 'V-2026-011', title: 'Visual Arts Teacher', dept: 'Arts', campus: 'sma', type: 'Part-time', level: 'Mid (3–5 yrs)', openings: 1, applicants: 9, posted: '2026-03-20', closes: '2026-05-10', status: 'closed',
    summary: 'Filled — Position closed.' },
];

let cidSeq = 1000;
const C = (o: any) => ({ id: 'C-' + (++cidSeq), gender: 'M', photo: null, source: 'careers.sutomo', priority: 'normal', notes: 0, ...o });

export const CANDIDATES = [
  C({ name: 'Aditya P. Wijaya', gender:'M', vacancyId:'V-2026-014', stage:'opl',
      email:'aditya.wijaya@gmail.com', phone:'+62 812 3456 7890', city:'Medan', age:29,
      education:'S2 Pendidikan Matematika — UNY (2021)', years: 5,
      subjects:['Mathematics','Statistics'], appliedAt:'2026-04-23', priority:'high',
      score: { written: 87, interview: 88, micro: 90 },
      deposit:{ status:'verified', amount:5000000, paidAt:'2026-04-29', refundEligible:true },
      psycho:{ status:'passed', date:'2026-05-04' },
      medical:{ status:'passed', date:'2026-05-06' },
      yayasan:{ status:'approved', date:'2026-05-08', by:'Dr. Tanto Halim' },
      opl:{ start:'2026-05-12', sessions: [
        { n:1, date:'2026-05-12', topic:'Linear Functions G10', mentor:'Rini Surya', status:'done',  score:88 },
        { n:2, date:'2026-05-13', topic:'Quadratics Intro G10',  mentor:'Rini Surya', status:'done',  score:86 },
        { n:3, date:'2026-05-14', topic:'Quadratic Formula G10', mentor:'Rini Surya', status:'done',  score:90 },
        { n:4, date:'2026-05-16', topic:'Sequences G11',         mentor:'Rini Surya', status:'scheduled' },
        { n:5, date:'2026-05-19', topic:'Sequences Practice',    mentor:'Rini Surya', status:'scheduled' },
        { n:6, date:'2026-05-21', topic:'Series Intro',          mentor:'Rini Surya', status:'pending' },
        { n:7, date:'2026-05-23', topic:'Series Applications',   mentor:'Rini Surya', status:'pending' },
        { n:8, date:'2026-05-26', topic:'Probability G11',       mentor:'Rini Surya', status:'pending' },
        { n:9, date:'2026-05-28', topic:'Probability Practice',  mentor:'Rini Surya', status:'pending' },
        { n:10,date:'2026-05-30', topic:'Final Demo Lesson',     mentor:'Rini Surya', status:'pending' }
      ]} }),
  C({ name:'Citra Maharani', gender:'F', vacancyId:'V-2026-015', stage:'yayasan',
      education:'S2 Sastra Inggris — UI (2019)', years: 7, subjects:['English Literature'],
      appliedAt:'2026-04-19', priority:'high', score: { written: 91, interview: 89 },
      deposit:{ status:'verified', amount:5000000, paidAt:'2026-04-26' },
      psycho:{ status:'passed' }, medical:{ status:'passed' },
      yayasan:{ status:'pending', queuedAt:'2026-05-05' } }),
  C({ name:'Bayu Pratama', gender:'M', vacancyId:'V-2026-016', stage:'medical',
      education:'S1 Sastra Mandarin — BINUS (2022)', years: 3, subjects:['Mandarin'],
      appliedAt:'2026-04-21', score:{written:79,interview:84},
      deposit:{status:'verified',amount:5000000}, medical:{status:'pending'} }),
  C({ name:'Dewi Anggraini', gender:'F', vacancyId:'V-2026-014', stage:'psycho',
      education:'S2 Pendidikan Matematika — UPI (2020)', years: 4, subjects:['Mathematics'],
      appliedAt:'2026-04-25', score:{written:84,interview:81}, psycho:{status:'pending'} }),
  C({ name:'Eka Pranata', gender:'M', vacancyId:'V-2026-017', stage:'interview',
      education:'S1 Fisika — ITB (2018)', years: 6, subjects:['Physics'],
      appliedAt:'2026-04-26', score:{written:88} }),
  C({ name:'Fani Kurnia', gender:'F', vacancyId:'V-2026-018', stage:'written',
      education:'S1 PGSD — UNJ (2023)', years: 1, subjects:['Bahasa Indonesia'],
      appliedAt:'2026-04-29', deposit:{status:'pending', amount:5000000} }),
  C({ name:'Galih Saputra', gender:'M', vacancyId:'V-2026-013', stage:'screening',
      education:'S1 Teknik Informatika — USU (2021)', years: 3, subjects:['Computer Science'],
      appliedAt:'2026-05-01' }),
  C({ name:'Hana Larasati', gender:'F', vacancyId:'V-2026-019', stage:'screening',
      education:'S1 BK — UNESA (2020)', years: 4, subjects:['Counseling'], appliedAt:'2026-05-02' }),
  C({ name:'Indra Maulana', gender:'M', vacancyId:'V-2026-014', stage:'applied',
      education:'S1 Matematika — UNAND (2024)', years: 1, subjects:['Mathematics'], appliedAt:'2026-05-09' }),
  C({ name:'Jihan Salsabila', gender:'F', vacancyId:'V-2026-015', stage:'applied',
      education:'S1 Sastra Inggris — UGM (2023)', years: 2, subjects:['English'], appliedAt:'2026-05-10' }),
  C({ name:'Kevin Halim', gender:'M', vacancyId:'V-2026-013', stage:'applied',
      education:'S1 SI — BINUS (2024)', years: 1, subjects:['ICT'], appliedAt:'2026-05-11' }),
  C({ name:'Lina Sari', gender:'F', vacancyId:'V-2026-018', stage:'rejected',
      education:'D3 PGSD — UT (2022)', years: 1, subjects:['Tematik'], appliedAt:'2026-04-15' }),
  C({ name:'Muhammad Reza', gender:'M', vacancyId:'V-2026-017', stage:'active',
      education:'S2 Fisika — UNPAD (2017)', years: 9, subjects:['Physics'], appliedAt:'2025-08-12', teacherId:'TCH-2026-001' }),
  C({ name:'Novita Putri', gender:'F', vacancyId:'V-2026-016', stage:'active',
      education:'S1 Sastra Mandarin — UI (2018)', years: 5, subjects:['Mandarin'], appliedAt:'2025-09-04', teacherId:'TCH-2026-002' }),
];

export const TEACHERS = [
  { id:'TCH-2024-018', employeeNo:'EMP-2018-014', name:'Rini Surya, M.Pd', gender:'F', dob:'1985-03-12',
    email:'rini.surya@sutomo.sch.id', phone:'+62 812-3456-7890',
    subject:'Mathematics', dept:'Mathematics', campus:'sma',
    status:'permanent', employment:'full-time', joined:'2018-07-01', tenure:'7y 10m',
    contract:'Permanent (Guru Tetap)', contractEnd:null,
    education:'M.Pd Mathematics — Universitas Indonesia',
    certifications:['Sertifikasi Pendidik','Cambridge IGCSE Examiner'],
    languages:['Indonesian','English'], city:'Jakarta', rating:4.7, lastReview:'2025-12-15' },
  { id:'TCH-2025-006', employeeNo:'EMP-2015-009', name:'Maria Hartanto, M.Pd', gender:'F', dob:'1980-09-22',
    email:'maria.hartanto@sutomo.sch.id', phone:'+62 813-2222-1111',
    subject:'Mathematics', dept:'Mathematics', campus:'sma',
    status:'permanent', employment:'full-time', joined:'2015-07-01', tenure:'10y 10m',
    contract:'Permanent — Dept. Head', contractEnd:null,
    education:'M.Pd Mathematics Education — UNESA',
    certifications:['Serdik','School Leadership Cert.'],
    languages:['Indonesian','English'], city:'Jakarta', rating:4.9, lastReview:'2025-12-15' },
  { id:'TCH-2026-001', employeeNo:'EMP-2026-001', name:'Muhammad Reza, M.Si', gender:'M', dob:'1990-11-08',
    email:'m.reza@sutomo.sch.id', phone:'+62 811-9876-5432',
    subject:'Physics', dept:'Science', campus:'int',
    status:'contract', employment:'full-time', joined:'2026-01-10', tenure:'4m',
    contract:'1-yr Contract', contractEnd:'2027-01-09',
    education:'M.Si Physics — ITB', certifications:['IBDP Physics Cat. 1'],
    languages:['Indonesian','English'], city:'Bandung', rating:4.4, lastReview:'2026-04-01' },
  { id:'TCH-2026-002', employeeNo:'EMP-2026-002', name:'Novita Putri, S.S', gender:'F', dob:'1992-06-15',
    email:'novita.putri@sutomo.sch.id', phone:'+62 815-3344-5566',
    subject:'Mandarin', dept:'Languages', campus:'smp',
    status:'contract', employment:'full-time', joined:'2026-02-01', tenure:'3m',
    contract:'1-yr Contract', contractEnd:'2027-01-31',
    education:'S.S Chinese Literature — UI', certifications:['HSK 6','TCSOL'],
    languages:['Indonesian','English','Mandarin'], city:'Jakarta', rating:4.5, lastReview:'2026-04-12' },
  { id:'TCH-2024-027', employeeNo:'EMP-2019-021', name:'Hendra Wijoyo, S.Pd', gender:'M', dob:'1987-02-25',
    email:'hendra.w@sutomo.sch.id', phone:'+62 812-7777-8888',
    subject:'PE & Health', dept:'Physical Education', campus:'sd',
    status:'permanent', employment:'full-time', joined:'2019-07-01', tenure:'6y 10m',
    contract:'Permanent', contractEnd:null,
    education:'S.Pd Physical Education — UNJ',
    certifications:['First Aid','Coaching Cert.'],
    languages:['Indonesian','English'], city:'Jakarta', rating:4.6, lastReview:'2025-11-30' },
  { id:'TCH-2023-014', employeeNo:'EMP-2014-007', name:'Sri Mulyani, M.Pd', gender:'F', dob:'1978-12-03',
    email:'sri.mulyani@sutomo.sch.id', phone:'+62 819-1010-2020',
    subject:'Bahasa Indonesia', dept:'Languages', campus:'smp',
    status:'permanent', employment:'full-time', joined:'2014-07-01', tenure:'11y 10m',
    contract:'Permanent — Senior', contractEnd:null,
    education:'M.Pd Bahasa Indonesia — UNJ',
    certifications:['Serdik','Kurikulum Merdeka Trainer'],
    languages:['Indonesian','English'], city:'Jakarta', rating:4.8, lastReview:'2025-12-10' },
  { id:'TCH-2020-009', employeeNo:'EMP-2010-003', name:'Drs. Budi Santoso', gender:'M', dob:'1972-04-18',
    email:'budi.santoso@sutomo.sch.id', phone:'+62 812-4040-5050',
    subject:'School Leadership', dept:'Administration', campus:'sma',
    status:'permanent', employment:'full-time', joined:'2010-07-01', tenure:'15y 10m',
    contract:'Permanent — Principal SMA', contractEnd:null,
    education:'Drs. Education Management — UPI',
    certifications:['Cambridge School Leader','Asesor BAN-SM'],
    languages:['Indonesian','English'], city:'Jakarta', rating:4.9, lastReview:'2025-12-20' },
  { id:'TCH-2022-011', employeeNo:'EMP-2017-018', name:'Lia Kartika, S.Pd', gender:'F', dob:'1989-08-30',
    email:'lia.kartika@sutomo.sch.id', phone:'+62 813-6060-7070',
    subject:'English', dept:'Languages', campus:'smp',
    status:'permanent', employment:'full-time', joined:'2017-07-01', tenure:'8y 10m',
    contract:'Permanent', contractEnd:null,
    education:'S.Pd English Education — UNJ', certifications:['TEFL','CELTA'],
    languages:['Indonesian','English'], city:'Jakarta', rating:4.7, lastReview:'2025-12-05' },
  { id:'TCH-2021-004', employeeNo:'EMP-2016-011', name:'Agus Pranoto, S.Si', gender:'M', dob:'1986-01-09',
    email:'agus.pranoto@sutomo.sch.id', phone:'+62 811-3535-2424',
    subject:'Biology', dept:'Science', campus:'sma',
    status:'permanent', employment:'full-time', joined:'2016-07-01', tenure:'9y 10m',
    contract:'Permanent', contractEnd:null,
    education:'S.Si Biology — UGM', certifications:['Serdik','BNSP Lab Assessor'],
    languages:['Indonesian','English'], city:'Yogyakarta', rating:4.6, lastReview:'2025-12-12' },
  { id:'TCH-2025-018', employeeNo:'EMP-2025-018', name:'Felicia Tan, B.Ed', gender:'F', dob:'1995-05-21',
    email:'felicia.tan@sutomo.sch.id', phone:'+62 812-9090-1212',
    subject:'Primary Class Teacher', dept:'Primary', campus:'sd',
    status:'probation', employment:'full-time', joined:'2025-08-01', tenure:'9m',
    contract:'Probation (90 days extended)', contractEnd:'2026-08-01',
    education:'B.Ed Primary — NIE Singapore', certifications:['IB PYP Cat. 1'],
    languages:['Indonesian','English','Mandarin'], city:'Jakarta', rating:4.3, lastReview:'2026-04-20' },
  { id:'TCH-2024-031', employeeNo:'EMP-2020-024', name:'Tono Hermawan, S.Pd', gender:'M', dob:'1988-10-14',
    email:'tono.h@sutomo.sch.id', phone:'+62 815-1717-1818',
    subject:'History', dept:'Social Studies', campus:'sma',
    status:'leave', employment:'full-time', joined:'2020-07-01', tenure:'5y 10m',
    contract:'Permanent · on Sabbatical', contractEnd:null,
    education:'S.Pd History — UNJ', certifications:['Serdik'],
    languages:['Indonesian','English'], city:'Jakarta', rating:4.5, lastReview:'2025-06-01' },
  { id:'TCH-2023-022', employeeNo:'EMP-2018-022', name:'Citra Anggraini, S.Sn', gender:'F', dob:'1991-07-07',
    email:'citra.a@sutomo.sch.id', phone:'+62 819-2424-3636',
    subject:'Visual Arts', dept:'Arts', campus:'smp',
    status:'permanent', employment:'part-time', joined:'2018-07-01', tenure:'7y 10m',
    contract:'Permanent · Part-time', contractEnd:null,
    education:'S.Sn Fine Arts — ISI Yogyakarta',
    certifications:['Adobe Certified Educator'],
    languages:['Indonesian','English'], city:'Jakarta', rating:4.7, lastReview:'2025-11-25' },
  { id:'TCH-2026-PRB-A', employeeNo:'EMP-2026-OPL-A', name:'Aditya P. Wijaya, S.Pd', gender:'M', dob:'1994-09-18',
    email:'aditya.w@sutomo.sch.id', phone:'+62 812-1111-2222',
    subject:'Mathematics', dept:'Mathematics', campus:'sma',
    status:'opl', employment:'full-time', joined:'2026-05-12', tenure:'<1m',
    contract:'OPL Probation (10 sessions / 90 days)', contractEnd:'2026-08-10',
    education:'S.Pd Mathematics — UI', certifications:[],
    languages:['Indonesian','English'], city:'Jakarta', rating:null },
  { id:'TCH-2019-007', employeeNo:'EMP-2014-005', name:'Ratna Dewi, M.Sc', gender:'F', dob:'1976-11-29',
    email:'ratna.dewi@alumni.sutomo.sch.id', phone:'+62 813-5050-6060',
    subject:'Chemistry', dept:'Science', campus:'sma',
    status:'alumni', employment:'full-time', joined:'2014-07-01', tenure:'10y 6m',
    contract:'Resigned — Jan 2025', contractEnd:'2025-01-15',
    education:'M.Sc Chemistry — NTU Singapore', certifications:['IBDP Chemistry'],
    languages:['Indonesian','English'], city:'Singapore', rating:4.8, lastReview:'2024-12-10' },
  { id:'TCH-2022-019', employeeNo:'EMP-2019-013', name:'Pdt. Yohanes Tanu, M.Th', gender:'M', dob:'1981-02-11',
    email:'yohanes.tanu@sutomo.sch.id', phone:'+62 812-7878-9090',
    subject:'Religion (Christian)', dept:'Religion & Character', campus:'sma',
    status:'permanent', employment:'full-time', joined:'2019-01-15', tenure:'7y 4m',
    contract:'Permanent — Chaplain', contractEnd:null,
    education:'M.Th Theology — STT Jakarta', certifications:['Ordained Pastor'],
    languages:['Indonesian','English'], city:'Jakarta', rating:4.8, lastReview:'2025-12-08' },
  { id:'TCH-2025-002', employeeNo:'EMP-2025-002', name:'Alia Rahman, S.Pd', gender:'F', dob:'1996-12-02',
    email:'alia.rahman@sutomo.sch.id', phone:'+62 819-8181-1919',
    subject:'Primary Class Teacher', dept:'Primary', campus:'sd',
    status:'permanent', employment:'full-time', joined:'2025-07-01', tenure:'10m',
    contract:'Permanent (post-probation)', contractEnd:null,
    education:'S.Pd Primary — UPI', certifications:['Serdik (in process)'],
    languages:['Indonesian','English','Arabic'], city:'Bandung', rating:4.5, lastReview:'2026-04-25' },
];

export const DEPOSITS = CANDIDATES.filter((c: any) => c.deposit).map((c: any) => ({
  candidateId: c.id, candidateName: c.name, amount: c.deposit.amount,
  status: c.deposit.status, paidAt: c.deposit.paidAt || null,
  dueDate: c.deposit.dueDate || null, refundEligible: !!c.deposit.refundEligible,
  receipt: c.deposit.status !== 'pending' ? `RCPT-${c.id}.pdf` : null, bank: 'BCA · 0123-456-789'
}));

export const INTERVIEWS = [
  { id:'INT-301', candidateId:'C-1005', date:'2026-05-16', time:'09:30', room:'Meeting Room A',
    panel:['Drs. Budi Santoso','Maria Hartanto','Sri Lestari'], type:'Panel Interview', status:'scheduled' },
  { id:'INT-302', candidateId:'C-1004', date:'2026-05-17', time:'11:00', room:'Meeting Room B',
    panel:['Drs. Budi Santoso','Maria Hartanto'], type:'Micro-Teaching', status:'scheduled' },
  { id:'INT-303', candidateId:'C-1002', date:'2026-05-04', time:'10:00', room:'Meeting Room A',
    panel:['Drs. Budi Santoso','HoD English'], type:'Final Panel', status:'completed', recommendation:'Strong Hire' },
];

export const EVAL_RUBRIC = [
  { id:'mastery',  label:'Subject Mastery',          weight: 25 },
  { id:'mgmt',     label:'Classroom Management',     weight: 20 },
  { id:'comm',     label:'Communication & Clarity',  weight: 15 },
  { id:'engage',   label:'Student Engagement',       weight: 15 },
  { id:'method',   label:'Teaching Methodology',     weight: 15 },
  { id:'language', label:'English / Mandarin Fluency', weight: 10 },
];

export const AUDIT = [
  { ts:'2026-05-14 16:42', user:'Drs. Budi Santoso', role:'Principal',  action:'override.score',  target:'C-1001', from:'87', to:'90', note:'Adjusted micro-teaching score after panel review.' },
  { ts:'2026-05-14 14:08', user:'Sri Lestari',       role:'HR',         action:'stage.move',      target:'C-1003', from:'psycho', to:'medical', note:'Psycho cleared — moved forward.' },
  { ts:'2026-05-14 11:30', user:'Hendra Wijaya',     role:'Finance',    action:'deposit.verify',  target:'C-1004', from:'pending', to:'verified', note:'Bank slip matched.' },
  { ts:'2026-05-13 17:20', user:'Dr. Tanto Halim',   role:'Yayasan',    action:'approval.grant',  target:'C-1001', from:'pending', to:'approved', note:'OPL track authorized.' },
  { ts:'2026-05-13 09:15', user:'Sri Lestari',       role:'HR',         action:'vacancy.publish', target:'V-2026-019', from:'draft', to:'open', note:'Counselor role posted.' },
  { ts:'2026-05-12 18:02', user:'Rini Surya',        role:'Mentor',     action:'opl.session',     target:'C-1001/S1', from:'-', to:'completed (88)', note:'Strong opener.' },
  { ts:'2026-05-12 13:44', user:'Maria Hartanto',    role:'Dept Head',  action:'eval.submit',     target:'C-1001', from:'-', to:'Strong Hire', note:'Evaluation finalized.' },
  { ts:'2026-05-11 10:11', user:'Dimas Pratama',     role:'IT',         action:'role.assign',     target:'TCH-2026-001', from:'candidate', to:'teacher.contract', note:'Auto-provisioned.' },
  { ts:'2026-05-10 09:55', user:'Hendra Wijaya',     role:'Finance',    action:'refund.eligible', target:'C-9988',  from:'verified', to:'refund-eligible', note:'Withdrawn by candidate.' },
  { ts:'2026-05-09 16:00', user:'Sri Lestari',       role:'HR',         action:'reject',          target:'C-1012', from:'screening', to:'rejected', note:'Did not meet minimum S1 requirement.' },
];

export const NOTIFICATIONS = [
  { id:'N1', icon:'calendar-clock', title:'Interview reminder', body:'Eka Pranata · Tomorrow 09:30 · Meeting Room A', time:'2 h ago', read:false, role:['hr','principal','depthead'] },
  { id:'N2', icon:'wallet', title:'Deposit pending', body:'Fani Kurnia (Rp 5,000,000) · Due in 4 days', time:'5 h ago', read:false, role:['hr','finance'] },
  { id:'N3', icon:'shield-check', title:'Yayasan approval', body:'Citra Maharani awaiting board decision', time:'1 d ago', read:false, role:['hr','yayasan'] },
  { id:'N4', icon:'graduation-cap', title:'OPL milestone', body:'Aditya Wijaya · 3 of 10 sessions complete', time:'1 d ago', read:true, role:['hr','mentor','principal'] },
  { id:'N5', icon:'file-warning', title:'Contract expiring', body:'Muhammad Reza · renewal in 240 days', time:'2 d ago', read:true, role:['hr','principal'] },
];

export const ANALYTICS = {
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
  timeToHire: { median: 42, p90: 71 },
  sources:   [{k:'Careers Portal', v:62}, {k:'Referral', v:18}, {k:'JobStreet', v:12}, {k:'LinkedIn', v:8}],
  deptHires: [{k:'Math', v:4}, {k:'Science', v:3}, {k:'English', v:2}, {k:'Mandarin', v:1}, {k:'PE', v:1}],
  monthly:   [12, 15, 18, 14, 19, 22, 20, 17, 21, 24, 26, 23],
  retention: { y1: 0.93, y2: 0.86, y3: 0.81 },
  probation: { passed: 22, failed: 3, active: 4 },
};

export const PERMISSIONS = [
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

// ---------------- Sidebar groups per role (route = URL path) ----------------
export type NavItem = { route: string; label: string; icon: string; badge?: number };
export type NavGroup = { label: string; items: NavItem[] };

export const ROUTE_GROUPS: Record<Role, NavGroup[]> = {
  applicant: [
    { label:'Careers', items: [
      { route:'/careers',  label:'Open Vacancies',     icon:'briefcase' },
      { route:'/apply',    label:'Apply Now',          icon:'send' },
    ]},
    { label:'My Application', items: [
      { route:'/tracker',  label:'Application Status', icon:'list-checks', badge: 1 },
      { route:'/mydocs',   label:'My Documents',       icon:'folder' },
      { route:'/inbox',    label:'Notifications',      icon:'bell', badge: 2 },
    ]},
  ],
  hr: [
    { label:'Overview', items: [
      { route:'/',            label:'Dashboard',         icon:'layout-dashboard' },
      { route:'/analytics',   label:'Analytics',         icon:'bar-chart-3' },
    ]},
    { label:'Hiring', items: [
      { route:'/recruitment', label:'Recruitment',       icon:'kanban' },
      { route:'/assessments', label:'Assessments',       icon:'clipboard-check' },
      { route:'/verifications', label:'Verifications',   icon:'shield-check' },
      { route:'/yayasan',     label:'Yayasan Approval',  icon:'landmark' },
    ]},
    { label:'People', items: [
      { route:'/master',      label:'Master Database',   icon:'database' },
      { route:'/onboarding',  label:'Onboarding',        icon:'graduation-cap' },
      { route:'/workforce',   label:'Workforce',         icon:'users-round' },
    ]},
    { label:'Admin', items: [
      { route:'/governance',  label:'Governance',        icon:'settings' },
      { route:'/inbox',       label:'Inbox',             icon:'bell', badge: 3 },
    ]},
  ],
  principal: [
    { label:'Overview', items: [
      { route:'/',          label:'Dashboard', icon:'layout-dashboard' },
      { route:'/analytics', label:'Analytics', icon:'bar-chart-3' },
    ]},
    { label:'Hiring', items: [
      { route:'/recruitment', label:'Recruitment', icon:'kanban' },
      { route:'/assessments', label:'Assessments', icon:'clipboard-check' },
    ]},
    { label:'People', items: [
      { route:'/master',     label:'Master Database', icon:'database' },
      { route:'/onboarding', label:'Onboarding',      icon:'graduation-cap' },
      { route:'/workforce',  label:'Workforce',       icon:'users-round' },
    ]},
    { label:'Admin', items: [{ route:'/inbox', label:'Inbox', icon:'bell', badge:2 }] },
  ],
  depthead: [
    { label:'Workspace', items: [
      { route:'/',            label:'Dashboard',     icon:'layout-dashboard' },
      { route:'/analytics',   label:'Analytics',     icon:'bar-chart-3' },
      { route:'/recruitment', label:'My Department', icon:'kanban' },
      { route:'/assessments', label:'Assessments',   icon:'clipboard-check' },
      { route:'/inbox',       label:'Inbox',         icon:'bell', badge:1 },
    ]},
  ],
  mentor: [
    { label:'Mentoring', items: [
      { route:'/',           label:'My Mentees', icon:'layout-dashboard' },
      { route:'/onboarding', label:'Onboarding', icon:'graduation-cap' },
      { route:'/inbox',      label:'Inbox',      icon:'bell' },
    ]},
  ],
  finance: [
    { label:'Finance', items: [
      { route:'/',              label:'Dashboard', icon:'layout-dashboard' },
      { route:'/verifications', label:'Deposits',  icon:'wallet', badge:2 },
      { route:'/analytics',     label:'Reports',   icon:'bar-chart-3' },
      { route:'/governance',    label:'Audit Log', icon:'shield-check' },
      { route:'/inbox',         label:'Inbox',     icon:'bell' },
    ]},
  ],
  yayasan: [
    { label:'Board', items: [
      { route:'/',            label:'Executive View',  icon:'layout-dashboard' },
      { route:'/yayasan',     label:'Approval Center', icon:'landmark', badge:1 },
      { route:'/master',      label:'Master Database', icon:'database' },
      { route:'/workforce',   label:'Workforce',       icon:'users-round' },
      { route:'/analytics',   label:'Analytics',       icon:'bar-chart-3' },
      { route:'/governance',  label:'Audit Log',       icon:'shield-check' },
    ]},
  ],
  it: [
    { label:'Operations', items: [
      { route:'/',           label:'Dashboard',  icon:'layout-dashboard' },
      { route:'/governance', label:'Governance', icon:'settings' },
    ]},
  ],
};

// ---------------- Helpers ----------------
export const fmtIDR = (n: number) => 'Rp ' + n.toLocaleString('id-ID');
export const fmtDate = (d?: string | Date | null) => {
  if (!d) return '—';
  const dt = new Date(d);
  return dt.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
};
export const daysBetween = (a: string | Date, b: string | Date) =>
  Math.round((new Date(b).getTime() - new Date(a).getTime()) / 86400000);
export const initials = (n: string) =>
  n.split(' ').filter(Boolean).slice(0,2).map(s => s[0]).join('').toUpperCase();

export const findCandidate = (id: string) => CANDIDATES.find((c: any) => c.id === id);
export const findVacancy   = (id: string) => VACANCIES.find(v => v.id === id);
export const findTeacher   = (id: string) => TEACHERS.find(t => t.id === id);
export const findCampus    = (id: string) => CAMPUSES.find(c => c.id === id);
export const stageLabel    = (id: string) => STAGES.find(s => s.id === id)?.label || id;
export const countByStage = () => {
  const o: Record<string, number> = {};
  STAGES.forEach(s => (o[s.id] = 0));
  CANDIDATES.forEach((c: any) => { o[c.stage] = (o[c.stage] || 0) + 1; });
  return o;
};
