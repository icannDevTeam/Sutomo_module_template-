<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Deposit;
use App\Models\Interview;
use App\Models\Teacher;
use App\Models\Vacancy;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedVacancies();
        $this->seedCandidates();
        $this->seedTeachers();
        $this->seedInterviews();
        $this->seedAudit();
    }

    private function seedVacancies(): void
    {
        $rows = [
            ['code'=>'V-2026-014','title'=>'Mathematics Teacher','dept'=>'Mathematics','campus'=>'sma','type'=>'Full-time','level'=>'Mid (3–5 yrs)','openings'=>2,'applicants'=>47,'posted_at'=>'2026-04-22','closes_at'=>'2026-06-15','status'=>'open','featured'=>true,'summary'=>'Teach Algebra II, Pre-Calculus, and IB Math AA SL across Grade 10–12. Lead one extracurricular math olympiad club.'],
            ['code'=>'V-2026-015','title'=>'English Literature Teacher','dept'=>'English','campus'=>'sma','type'=>'Full-time','level'=>'Senior (5+ yrs)','openings'=>1,'applicants'=>34,'posted_at'=>'2026-04-25','closes_at'=>'2026-06-10','status'=>'open','featured'=>true,'summary'=>'IB English A: Literature HL/SL specialist for Grade 11–12 with experience in Extended Essay supervision.'],
            ['code'=>'V-2026-016','title'=>'Mandarin Language Teacher','dept'=>'Mandarin','campus'=>'smp','type'=>'Full-time','level'=>'Mid (3–5 yrs)','openings'=>1,'applicants'=>22,'posted_at'=>'2026-04-30','closes_at'=>'2026-06-20','status'=>'open','featured'=>false,'summary'=>'Native or near-native Mandarin speaker (HSK 6) to teach SMP Grade 7–9 Mandarin curriculum.'],
            ['code'=>'V-2026-017','title'=>'Physics Teacher (IB DP)','dept'=>'Science','campus'=>'int','type'=>'Full-time','level'=>'Senior (5+ yrs)','openings'=>1,'applicants'=>18,'posted_at'=>'2026-05-01','closes_at'=>'2026-06-30','status'=>'open','featured'=>false,'summary'=>'IB Diploma Physics HL/SL teacher with strong lab pedagogy and IA supervision experience.'],
            ['code'=>'V-2026-018','title'=>'Primary Homeroom Teacher (G3)','dept'=>'Bahasa Indonesia','campus'=>'sd','type'=>'Full-time','level'=>'Junior (1–2 yrs)','openings'=>3,'applicants'=>61,'posted_at'=>'2026-05-02','closes_at'=>'2026-06-25','status'=>'open','featured'=>false,'summary'=>'Lead a Grade 3 homeroom of 24 students, integrating thematic learning across core subjects.'],
            ['code'=>'V-2026-019','title'=>'School Counselor (BK)','dept'=>'Counseling','campus'=>'smp','type'=>'Full-time','level'=>'Mid (3–5 yrs)','openings'=>1,'applicants'=>12,'posted_at'=>'2026-05-04','closes_at'=>'2026-07-04','status'=>'open','featured'=>false,'summary'=>'Provide guidance, counseling and parent liaison for SMP Grade 7–9.'],
            ['code'=>'V-2026-013','title'=>'Computer Science Teacher','dept'=>'ICT','campus'=>'sma','type'=>'Full-time','level'=>'Mid (3–5 yrs)','openings'=>1,'applicants'=>39,'posted_at'=>'2026-04-10','closes_at'=>'2026-05-31','status'=>'closing','featured'=>false,'summary'=>'Teach Python, web foundations, and IB Computer Science SL. Closing in 16 days.'],
            ['code'=>'V-2026-012','title'=>'PE & Health Teacher','dept'=>'PE & Health','campus'=>'sd','type'=>'Full-time','level'=>'Junior (1–2 yrs)','openings'=>1,'applicants'=>28,'posted_at'=>'2026-04-02','closes_at'=>'2026-05-20','status'=>'closing','featured'=>false,'summary'=>'Energetic PE teacher for SD G1–G6.'],
            ['code'=>'V-2026-011','title'=>'Visual Arts Teacher','dept'=>'Arts','campus'=>'sma','type'=>'Part-time','level'=>'Mid (3–5 yrs)','openings'=>1,'applicants'=>9,'posted_at'=>'2026-03-20','closes_at'=>'2026-05-10','status'=>'closed','featured'=>false,'summary'=>'Filled — Position closed.'],
        ];
        foreach ($rows as $r) Vacancy::create($r);
    }

    private function seedCandidates(): void
    {
        $v = fn($c) => Vacancy::where('code',$c)->value('id');

        $rows = [
            ['code'=>'C-1001','name'=>'Aditya P. Wijaya','gender'=>'M','vacancy_id'=>$v('V-2026-014'),'stage'=>'opl','email'=>'aditya.wijaya@gmail.com','phone'=>'+62 812 3456 7890','city'=>'Medan','age'=>29,'education'=>'S2 Pendidikan Matematika — UNY (2021)','years'=>5,'subjects'=>['Mathematics','Statistics'],'applied_at'=>'2026-04-23','priority'=>'high','score_written'=>87,'score_interview'=>88,'score_micro'=>90,'meta'=>['psycho'=>['status'=>'passed','date'=>'2026-05-04'],'medical'=>['status'=>'passed','date'=>'2026-05-06'],'yayasan'=>['status'=>'approved','date'=>'2026-05-08','by'=>'Dr. Tanto Halim']]],
            ['code'=>'C-1002','name'=>'Citra Maharani','gender'=>'F','vacancy_id'=>$v('V-2026-015'),'stage'=>'yayasan','education'=>'S2 Sastra Inggris — UI (2019)','years'=>7,'subjects'=>['English Literature'],'applied_at'=>'2026-04-19','priority'=>'high','score_written'=>91,'score_interview'=>89],
            ['code'=>'C-1003','name'=>'Bayu Pratama','gender'=>'M','vacancy_id'=>$v('V-2026-016'),'stage'=>'medical','education'=>'S1 Sastra Mandarin — BINUS (2022)','years'=>3,'subjects'=>['Mandarin'],'applied_at'=>'2026-04-21','score_written'=>79,'score_interview'=>84],
            ['code'=>'C-1004','name'=>'Dewi Anggraini','gender'=>'F','vacancy_id'=>$v('V-2026-014'),'stage'=>'psycho','education'=>'S2 Pendidikan Matematika — UPI (2020)','years'=>4,'subjects'=>['Mathematics'],'applied_at'=>'2026-04-25','score_written'=>84,'score_interview'=>81],
            ['code'=>'C-1005','name'=>'Eka Pranata','gender'=>'M','vacancy_id'=>$v('V-2026-017'),'stage'=>'interview','education'=>'S1 Fisika — ITB (2018)','years'=>6,'subjects'=>['Physics'],'applied_at'=>'2026-04-26','score_written'=>88],
            ['code'=>'C-1006','name'=>'Fani Kurnia','gender'=>'F','vacancy_id'=>$v('V-2026-018'),'stage'=>'written','education'=>'S1 PGSD — UNJ (2023)','years'=>1,'subjects'=>['Bahasa Indonesia'],'applied_at'=>'2026-04-29'],
            ['code'=>'C-1007','name'=>'Galih Saputra','gender'=>'M','vacancy_id'=>$v('V-2026-013'),'stage'=>'screening','education'=>'S1 Teknik Informatika — USU (2021)','years'=>3,'subjects'=>['Computer Science'],'applied_at'=>'2026-05-01'],
            ['code'=>'C-1008','name'=>'Hana Larasati','gender'=>'F','vacancy_id'=>$v('V-2026-019'),'stage'=>'screening','education'=>'S1 BK — UNESA (2020)','years'=>4,'subjects'=>['Counseling'],'applied_at'=>'2026-05-02'],
            ['code'=>'C-1009','name'=>'Indra Maulana','gender'=>'M','vacancy_id'=>$v('V-2026-014'),'stage'=>'applied','education'=>'S1 Matematika — UNAND (2024)','years'=>1,'subjects'=>['Mathematics'],'applied_at'=>'2026-05-09'],
            ['code'=>'C-1010','name'=>'Jihan Salsabila','gender'=>'F','vacancy_id'=>$v('V-2026-015'),'stage'=>'applied','education'=>'S1 Sastra Inggris — UGM (2023)','years'=>2,'subjects'=>['English'],'applied_at'=>'2026-05-10'],
            ['code'=>'C-1011','name'=>'Kevin Halim','gender'=>'M','vacancy_id'=>$v('V-2026-013'),'stage'=>'applied','education'=>'S1 SI — BINUS (2024)','years'=>1,'subjects'=>['ICT'],'applied_at'=>'2026-05-11'],
            ['code'=>'C-1012','name'=>'Lina Sari','gender'=>'F','vacancy_id'=>$v('V-2026-018'),'stage'=>'rejected','education'=>'D3 PGSD — UT (2022)','years'=>1,'subjects'=>['Tematik'],'applied_at'=>'2026-04-15'],
            ['code'=>'C-1013','name'=>'Muhammad Reza','gender'=>'M','vacancy_id'=>$v('V-2026-017'),'stage'=>'active','education'=>'S2 Fisika — UNPAD (2017)','years'=>9,'subjects'=>['Physics'],'applied_at'=>'2025-08-12'],
            ['code'=>'C-1014','name'=>'Novita Putri','gender'=>'F','vacancy_id'=>$v('V-2026-016'),'stage'=>'active','education'=>'S1 Sastra Mandarin — UI (2018)','years'=>5,'subjects'=>['Mandarin'],'applied_at'=>'2025-09-04'],
        ];
        foreach ($rows as $r) Candidate::create($r);

        $deposits = [
            ['code'=>'C-1001','amount'=>5000000,'status'=>'verified','paid_at'=>'2026-04-29','refund_eligible'=>true,'receipt'=>'RCPT-C-1001.pdf','bank'=>'BCA · 0123-456-789'],
            ['code'=>'C-1002','amount'=>5000000,'status'=>'verified','paid_at'=>'2026-04-26','refund_eligible'=>false,'receipt'=>'RCPT-C-1002.pdf','bank'=>'BCA · 0123-456-789'],
            ['code'=>'C-1003','amount'=>5000000,'status'=>'verified','paid_at'=>'2026-04-27','refund_eligible'=>false,'receipt'=>'RCPT-C-1003.pdf','bank'=>'BCA · 0123-456-789'],
            ['code'=>'C-1006','amount'=>5000000,'status'=>'pending','due_date'=>'2026-05-18','refund_eligible'=>false,'bank'=>'BCA · 0123-456-789'],
        ];
        foreach ($deposits as $d) {
            $cid = Candidate::where('code',$d['code'])->value('id');
            unset($d['code']);
            Deposit::create(['candidate_id'=>$cid] + $d);
        }
    }

    private function seedTeachers(): void
    {
        $rows = [
            ['code'=>'TCH-2024-018','employee_no'=>'EMP-2018-014','name'=>'Rini Surya, M.Pd','gender'=>'F','dob'=>'1985-03-12','email'=>'rini.surya@sutomo.sch.id','phone'=>'+62 812-3456-7890','subject'=>'Mathematics','dept'=>'Mathematics','campus'=>'sma','status'=>'permanent','employment'=>'full-time','joined_at'=>'2018-07-01','tenure'=>'7y 10m','contract'=>'Permanent (Guru Tetap)','education'=>'M.Pd Mathematics — Universitas Indonesia','certifications'=>['Sertifikasi Pendidik','Cambridge IGCSE Examiner'],'languages'=>['Indonesian','English'],'city'=>'Jakarta','rating'=>4.7,'last_review'=>'2025-12-15'],
            ['code'=>'TCH-2025-006','employee_no'=>'EMP-2015-009','name'=>'Maria Hartanto, M.Pd','gender'=>'F','dob'=>'1980-09-22','email'=>'maria.hartanto@sutomo.sch.id','phone'=>'+62 813-2222-1111','subject'=>'Mathematics','dept'=>'Mathematics','campus'=>'sma','status'=>'permanent','employment'=>'full-time','joined_at'=>'2015-07-01','tenure'=>'10y 10m','contract'=>'Permanent — Dept. Head','education'=>'M.Pd Mathematics Education — UNESA','certifications'=>['Serdik','School Leadership Cert.'],'languages'=>['Indonesian','English'],'city'=>'Jakarta','rating'=>4.9,'last_review'=>'2025-12-15'],
            ['code'=>'TCH-2026-001','employee_no'=>'EMP-2026-001','name'=>'Muhammad Reza, M.Si','gender'=>'M','dob'=>'1990-11-08','email'=>'m.reza@sutomo.sch.id','phone'=>'+62 811-9876-5432','subject'=>'Physics','dept'=>'Science','campus'=>'int','status'=>'contract','employment'=>'full-time','joined_at'=>'2026-01-10','tenure'=>'4m','contract'=>'1-yr Contract','contract_end'=>'2027-01-09','education'=>'M.Si Physics — ITB','certifications'=>['IBDP Physics Cat. 1'],'languages'=>['Indonesian','English'],'city'=>'Bandung','rating'=>4.4,'last_review'=>'2026-04-01'],
            ['code'=>'TCH-2026-002','employee_no'=>'EMP-2026-002','name'=>'Novita Putri, S.S','gender'=>'F','dob'=>'1992-06-15','email'=>'novita.putri@sutomo.sch.id','phone'=>'+62 815-3344-5566','subject'=>'Mandarin','dept'=>'Languages','campus'=>'smp','status'=>'contract','employment'=>'full-time','joined_at'=>'2026-02-01','tenure'=>'3m','contract'=>'1-yr Contract','contract_end'=>'2027-01-31','education'=>'S.S Chinese Literature — UI','certifications'=>['HSK 6','TCSOL'],'languages'=>['Indonesian','English','Mandarin'],'city'=>'Jakarta','rating'=>4.5,'last_review'=>'2026-04-12'],
            ['code'=>'TCH-2024-027','employee_no'=>'EMP-2019-021','name'=>'Hendra Wijoyo, S.Pd','gender'=>'M','dob'=>'1987-02-25','email'=>'hendra.w@sutomo.sch.id','phone'=>'+62 812-7777-8888','subject'=>'PE & Health','dept'=>'Physical Education','campus'=>'sd','status'=>'permanent','employment'=>'full-time','joined_at'=>'2019-07-01','tenure'=>'6y 10m','contract'=>'Permanent','education'=>'S.Pd Physical Education — UNJ','certifications'=>['First Aid','Coaching Cert.'],'languages'=>['Indonesian','English'],'city'=>'Jakarta','rating'=>4.6,'last_review'=>'2025-11-30'],
            ['code'=>'TCH-2023-014','employee_no'=>'EMP-2014-007','name'=>'Sri Mulyani, M.Pd','gender'=>'F','dob'=>'1978-12-03','email'=>'sri.mulyani@sutomo.sch.id','phone'=>'+62 819-1010-2020','subject'=>'Bahasa Indonesia','dept'=>'Languages','campus'=>'smp','status'=>'permanent','employment'=>'full-time','joined_at'=>'2014-07-01','tenure'=>'11y 10m','contract'=>'Permanent — Senior','education'=>'M.Pd Bahasa Indonesia — UNJ','certifications'=>['Serdik','Kurikulum Merdeka Trainer'],'languages'=>['Indonesian','English'],'city'=>'Jakarta','rating'=>4.8,'last_review'=>'2025-12-10'],
            ['code'=>'TCH-2020-009','employee_no'=>'EMP-2010-003','name'=>'Drs. Budi Santoso','gender'=>'M','dob'=>'1972-04-18','email'=>'budi.santoso@sutomo.sch.id','phone'=>'+62 812-4040-5050','subject'=>'School Leadership','dept'=>'Administration','campus'=>'sma','status'=>'permanent','employment'=>'full-time','joined_at'=>'2010-07-01','tenure'=>'15y 10m','contract'=>'Permanent — Principal SMA','education'=>'Drs. Education Management — UPI','certifications'=>['Cambridge School Leader','Asesor BAN-SM'],'languages'=>['Indonesian','English'],'city'=>'Jakarta','rating'=>4.9,'last_review'=>'2025-12-20'],
            ['code'=>'TCH-2022-011','employee_no'=>'EMP-2017-018','name'=>'Lia Kartika, S.Pd','gender'=>'F','dob'=>'1989-08-30','email'=>'lia.kartika@sutomo.sch.id','phone'=>'+62 813-6060-7070','subject'=>'English','dept'=>'Languages','campus'=>'smp','status'=>'permanent','employment'=>'full-time','joined_at'=>'2017-07-01','tenure'=>'8y 10m','contract'=>'Permanent','education'=>'S.Pd English Education — UNJ','certifications'=>['TEFL','CELTA'],'languages'=>['Indonesian','English'],'city'=>'Jakarta','rating'=>4.7,'last_review'=>'2025-12-05'],
            ['code'=>'TCH-2021-004','employee_no'=>'EMP-2016-011','name'=>'Agus Pranoto, S.Si','gender'=>'M','dob'=>'1986-01-09','email'=>'agus.pranoto@sutomo.sch.id','phone'=>'+62 811-3535-2424','subject'=>'Biology','dept'=>'Science','campus'=>'sma','status'=>'permanent','employment'=>'full-time','joined_at'=>'2016-07-01','tenure'=>'9y 10m','contract'=>'Permanent','education'=>'S.Si Biology — UGM','certifications'=>['Serdik','BNSP Lab Assessor'],'languages'=>['Indonesian','English'],'city'=>'Yogyakarta','rating'=>4.6,'last_review'=>'2025-12-12'],
            ['code'=>'TCH-2025-018','employee_no'=>'EMP-2025-018','name'=>'Felicia Tan, B.Ed','gender'=>'F','dob'=>'1995-05-21','email'=>'felicia.tan@sutomo.sch.id','phone'=>'+62 812-9090-1212','subject'=>'Primary Class Teacher','dept'=>'Primary','campus'=>'sd','status'=>'probation','employment'=>'full-time','joined_at'=>'2025-08-01','tenure'=>'9m','contract'=>'Probation (90 days extended)','contract_end'=>'2026-08-01','education'=>'B.Ed Primary — NIE Singapore','certifications'=>['IB PYP Cat. 1'],'languages'=>['Indonesian','English','Mandarin'],'city'=>'Jakarta','rating'=>4.3,'last_review'=>'2026-04-20'],
            ['code'=>'TCH-2024-031','employee_no'=>'EMP-2020-024','name'=>'Tono Hermawan, S.Pd','gender'=>'M','dob'=>'1988-10-14','email'=>'tono.h@sutomo.sch.id','phone'=>'+62 815-1717-1818','subject'=>'History','dept'=>'Social Studies','campus'=>'sma','status'=>'leave','employment'=>'full-time','joined_at'=>'2020-07-01','tenure'=>'5y 10m','contract'=>'Permanent · on Sabbatical','education'=>'S.Pd History — UNJ','certifications'=>['Serdik'],'languages'=>['Indonesian','English'],'city'=>'Jakarta','rating'=>4.5,'last_review'=>'2025-06-01'],
            ['code'=>'TCH-2023-022','employee_no'=>'EMP-2018-022','name'=>'Citra Anggraini, S.Sn','gender'=>'F','dob'=>'1991-07-07','email'=>'citra.a@sutomo.sch.id','phone'=>'+62 819-2424-3636','subject'=>'Visual Arts','dept'=>'Arts','campus'=>'smp','status'=>'permanent','employment'=>'part-time','joined_at'=>'2018-07-01','tenure'=>'7y 10m','contract'=>'Permanent · Part-time','education'=>'S.Sn Fine Arts — ISI Yogyakarta','certifications'=>['Adobe Certified Educator'],'languages'=>['Indonesian','English'],'city'=>'Jakarta','rating'=>4.7,'last_review'=>'2025-11-25'],
            ['code'=>'TCH-2026-PRB-A','employee_no'=>'EMP-2026-OPL-A','name'=>'Aditya P. Wijaya, S.Pd','gender'=>'M','dob'=>'1994-09-18','email'=>'aditya.w@sutomo.sch.id','phone'=>'+62 812-1111-2222','subject'=>'Mathematics','dept'=>'Mathematics','campus'=>'sma','status'=>'opl','employment'=>'full-time','joined_at'=>'2026-05-12','tenure'=>'<1m','contract'=>'OPL Probation (10 sessions / 90 days)','contract_end'=>'2026-08-10','education'=>'S.Pd Mathematics — UI','certifications'=>[],'languages'=>['Indonesian','English'],'city'=>'Jakarta','rating'=>null],
            ['code'=>'TCH-2019-007','employee_no'=>'EMP-2014-005','name'=>'Ratna Dewi, M.Sc','gender'=>'F','dob'=>'1976-11-29','email'=>'ratna.dewi@alumni.sutomo.sch.id','phone'=>'+62 813-5050-6060','subject'=>'Chemistry','dept'=>'Science','campus'=>'sma','status'=>'alumni','employment'=>'full-time','joined_at'=>'2014-07-01','tenure'=>'10y 6m','contract'=>'Resigned — Jan 2025','contract_end'=>'2025-01-15','education'=>'M.Sc Chemistry — NTU Singapore','certifications'=>['IBDP Chemistry'],'languages'=>['Indonesian','English'],'city'=>'Singapore','rating'=>4.8,'last_review'=>'2024-12-10'],
            ['code'=>'TCH-2022-019','employee_no'=>'EMP-2019-013','name'=>'Pdt. Yohanes Tanu, M.Th','gender'=>'M','dob'=>'1981-02-11','email'=>'yohanes.tanu@sutomo.sch.id','phone'=>'+62 812-7878-9090','subject'=>'Religion (Christian)','dept'=>'Religion & Character','campus'=>'sma','status'=>'permanent','employment'=>'full-time','joined_at'=>'2019-01-15','tenure'=>'7y 4m','contract'=>'Permanent — Chaplain','education'=>'M.Th Theology — STT Jakarta','certifications'=>['Ordained Pastor'],'languages'=>['Indonesian','English'],'city'=>'Jakarta','rating'=>4.8,'last_review'=>'2025-12-08'],
            ['code'=>'TCH-2025-002','employee_no'=>'EMP-2025-002','name'=>'Alia Rahman, S.Pd','gender'=>'F','dob'=>'1996-12-02','email'=>'alia.rahman@sutomo.sch.id','phone'=>'+62 819-8181-1919','subject'=>'Primary Class Teacher','dept'=>'Primary','campus'=>'sd','status'=>'permanent','employment'=>'full-time','joined_at'=>'2025-07-01','tenure'=>'10m','contract'=>'Permanent (post-probation)','education'=>'S.Pd Primary — UPI','certifications'=>['Serdik (in process)'],'languages'=>['Indonesian','English','Arabic'],'city'=>'Bandung','rating'=>4.5,'last_review'=>'2026-04-25'],
        ];
        foreach ($rows as $r) Teacher::create($r);
    }

    private function seedInterviews(): void
    {
        $rows = [
            ['code'=>'INT-301','candidate'=>'C-1005','scheduled_date'=>'2026-05-16','scheduled_time'=>'09:30','room'=>'Meeting Room A','panel'=>['Drs. Budi Santoso','Maria Hartanto','Sri Lestari'],'type'=>'Panel Interview','status'=>'scheduled'],
            ['code'=>'INT-302','candidate'=>'C-1004','scheduled_date'=>'2026-05-17','scheduled_time'=>'11:00','room'=>'Meeting Room B','panel'=>['Drs. Budi Santoso','Maria Hartanto'],'type'=>'Micro-Teaching','status'=>'scheduled'],
            ['code'=>'INT-303','candidate'=>'C-1002','scheduled_date'=>'2026-05-04','scheduled_time'=>'10:00','room'=>'Meeting Room A','panel'=>['Drs. Budi Santoso','HoD English'],'type'=>'Final Panel','status'=>'completed','recommendation'=>'Strong Hire'],
        ];
        foreach ($rows as $r) {
            $cid = Candidate::where('code',$r['candidate'])->value('id');
            unset($r['candidate']);
            Interview::create(['candidate_id'=>$cid] + $r);
        }
    }

    private function seedAudit(): void
    {
        $rows = [
            ['occurred_at'=>'2026-05-14 16:42','user_name'=>'Drs. Budi Santoso','role'=>'Principal','action'=>'override.score','target'=>'C-1001','from_value'=>'87','to_value'=>'90','note'=>'Adjusted micro-teaching score after panel review.'],
            ['occurred_at'=>'2026-05-14 14:08','user_name'=>'Sri Lestari','role'=>'HR','action'=>'stage.move','target'=>'C-1003','from_value'=>'psycho','to_value'=>'medical','note'=>'Psycho cleared — moved forward.'],
            ['occurred_at'=>'2026-05-14 11:30','user_name'=>'Hendra Wijaya','role'=>'Finance','action'=>'deposit.verify','target'=>'C-1004','from_value'=>'pending','to_value'=>'verified','note'=>'Bank slip matched.'],
            ['occurred_at'=>'2026-05-13 17:20','user_name'=>'Dr. Tanto Halim','role'=>'Yayasan','action'=>'approval.grant','target'=>'C-1001','from_value'=>'pending','to_value'=>'approved','note'=>'OPL track authorized.'],
            ['occurred_at'=>'2026-05-13 09:15','user_name'=>'Sri Lestari','role'=>'HR','action'=>'vacancy.publish','target'=>'V-2026-019','from_value'=>'draft','to_value'=>'open','note'=>'Counselor role posted.'],
            ['occurred_at'=>'2026-05-12 18:02','user_name'=>'Rini Surya','role'=>'Mentor','action'=>'opl.session','target'=>'C-1001/S1','from_value'=>'-','to_value'=>'completed (88)','note'=>'Strong opener.'],
            ['occurred_at'=>'2026-05-12 13:44','user_name'=>'Maria Hartanto','role'=>'Dept Head','action'=>'eval.submit','target'=>'C-1001','from_value'=>'-','to_value'=>'Strong Hire','note'=>'Evaluation finalized.'],
            ['occurred_at'=>'2026-05-11 10:11','user_name'=>'Dimas Pratama','role'=>'IT','action'=>'role.assign','target'=>'TCH-2026-001','from_value'=>'candidate','to_value'=>'teacher.contract','note'=>'Auto-provisioned.'],
        ];
        foreach ($rows as $r) AuditLog::create($r);

        \App\Models\User::firstOrCreate(
            ['email' => 'admin@sutomo.sch.id'],
            ['name' => 'Sri Lestari', 'password' => bcrypt('password')]
        );
    }
}
