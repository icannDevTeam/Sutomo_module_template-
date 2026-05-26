<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\BehaviorLog;
use App\Models\BookPackage;
use App\Models\Candidate;
use App\Models\Deposit;
use App\Models\EbookPack;
use App\Models\EbookPlatform;
use App\Models\EnrollmentPeriod;
use App\Models\Interview;
use App\Models\PaymentAccount;
use App\Models\ProcurementRequest;
use App\Models\SchoolClass;
use App\Models\SchoolEvent;
use App\Models\SscRequest;
use App\Models\Student;
use App\Models\SupervisiEvaluation;
use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Models\Vacancy;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedVacancies();
        $this->seedCandidates();
        $this->seedTalentPool();
        $this->seedTeachers();
        $this->seedInterviews();
        $this->seedAudit();
        $this->seedPrincipalModule();
        $this->seedEnrollmentCatalog();
        $this->call(TimetableSeeder::class);
        $this->call(AnnouncementSeeder::class);
        $this->call(UnitPlanSeeder::class);
        $this->call(TeacherApprovalsSeeder::class);
        $this->call(TeacherProfileSeeder::class);
        $this->call(ObservationCriteriaSeeder::class);
        $this->call(PrincipalDemoSeeder::class);
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

    private function seedTalentPool(): void
    {
        // Unsolicited CVs and walk-ins — not tied to a current vacancy.
        // These power the Master Database / Talent Pool so HR can pick from
        // existing applicants instead of opening a new vacancy.
        $rows = [
            ['code'=>'TP-2001','name'=>'Arif Hidayat, M.Pd','gender'=>'M','email'=>'arif.hidayat@gmail.com','phone'=>'+62 812-3300-4400','city'=>'Jakarta','age'=>34,'education'=>'M.Pd Mathematics — UPI (2018)','qualification'=>'S2','years'=>9,'subjects'=>['Mathematics','Statistics'],'applied_at'=>'2026-02-14','source'=>'website','current_school'=>'SMA Pelita Harapan, Jakarta','past_schools'=>['SMA Pelita Harapan (2020–now)','SMP Tarakanita (2016–2020)'],'certifications'=>['Cambridge IGCSE Maths','Serdik'],'languages'=>['Indonesian','English'],'cv_url'=>'/cv/arif-hidayat.pdf','availability'=>'1 month','desired_salary'=>15000000,'preferred_campus'=>'sma','notes'=>'Excellent referral from Maria Hartanto.'],
            ['code'=>'TP-2002','name'=>'Bella Permata, S.S','gender'=>'F','email'=>'bella.permata@yahoo.com','phone'=>'+62 813-4040-1212','city'=>'Bandung','age'=>27,'education'=>'S.S English Literature — UNPAD (2021)','qualification'=>'S1','years'=>3,'subjects'=>['English','English Literature'],'applied_at'=>'2026-03-02','source'=>'website','current_school'=>'British School Bandung','past_schools'=>['British School Bandung (2022–now)'],'certifications'=>['CELTA','TOEFL iBT 112'],'languages'=>['Indonesian','English'],'cv_url'=>'/cv/bella-permata.pdf','availability'=>'immediate','desired_salary'=>12000000,'preferred_campus'=>'sma'],
            ['code'=>'TP-2003','name'=>'Christian Sutanto, B.Sc','gender'=>'M','email'=>'christian.s@outlook.com','phone'=>'+62 811-7799-8800','city'=>'Surabaya','age'=>31,'education'=>'B.Sc Physics — NUS Singapore (2017)','qualification'=>'S1','years'=>7,'subjects'=>['Physics','Mathematics'],'applied_at'=>'2026-03-18','source'=>'referral','current_school'=>'Singapore International School Surabaya','past_schools'=>['SIS Surabaya (2019–now)','Raffles Institution SG (2017–2019)'],'certifications'=>['IBDP Physics Cat. 2','A-Levels Examiner'],'languages'=>['Indonesian','English','Mandarin'],'cv_url'=>'/cv/christian-sutanto.pdf','availability'=>'2 months','desired_salary'=>20000000,'preferred_campus'=>'int','notes'=>'Strong IB DP background; flag for next Physics opening.'],
            ['code'=>'TP-2004','name'=>'Dinda Ayu, S.Psi','gender'=>'F','email'=>'dinda.ayu@gmail.com','phone'=>'+62 819-2200-3300','city'=>'Jakarta','age'=>28,'education'=>'S.Psi Psychology — UI (2021)','qualification'=>'S1','years'=>4,'subjects'=>['Counseling','BK'],'applied_at'=>'2026-03-25','source'=>'walk-in','current_school'=>'Sekolah Cikal Setu','past_schools'=>['Sekolah Cikal Setu (2022–now)','Yayasan Pendidikan Cita Buana (2021–2022)'],'certifications'=>['HIMPSI member'],'languages'=>['Indonesian','English'],'availability'=>'1 month','preferred_campus'=>'smp'],
            ['code'=>'TP-2005','name'=>'Edwin Tanaka, M.Sc','gender'=>'M','email'=>'edwin.tanaka@protonmail.com','phone'=>'+62 812-5050-6060','city'=>'Jakarta','age'=>38,'education'=>'M.Sc Computer Science — Monash AU (2014)','qualification'=>'S2','years'=>12,'subjects'=>['Computer Science','ICT','Python'],'applied_at'=>'2026-04-01','source'=>'website','current_school'=>'ACG School Jakarta','past_schools'=>['ACG School Jakarta (2018–now)','BINUS School Simprug (2014–2018)'],'certifications'=>['Google Certified Educator L2','Microsoft Innovative Educator'],'languages'=>['Indonesian','English'],'cv_url'=>'/cv/edwin-tanaka.pdf','availability'=>'June 2026','desired_salary'=>25000000,'preferred_campus'=>'sma'],
            ['code'=>'TP-2006','name'=>'Fatimah Az-Zahra, S.Pd','gender'=>'F','email'=>'fatimah.az@gmail.com','phone'=>'+62 813-7878-1212','city'=>'Bogor','age'=>26,'education'=>'S.Pd Bahasa Indonesia — UNJ (2022)','qualification'=>'S1','years'=>2,'subjects'=>['Bahasa Indonesia'],'applied_at'=>'2026-04-05','source'=>'website','current_school'=>'SDIT Al-Hikmah Bogor','past_schools'=>['SDIT Al-Hikmah (2023–now)'],'languages'=>['Indonesian','Arabic'],'availability'=>'immediate','desired_salary'=>9000000,'preferred_campus'=>'sd'],
            ['code'=>'TP-2007','name'=>'Grace Lim, M.Ed','gender'=>'F','email'=>'grace.lim@gmail.com','phone'=>'+65 9100-2200','city'=>'Singapore','age'=>36,'education'=>'M.Ed Early Childhood — NIE Singapore (2016)','qualification'=>'S2','years'=>10,'subjects'=>['Primary Class Teacher','IB PYP'],'applied_at'=>'2026-04-10','source'=>'agency','current_school'=>'Stamford American Singapore','past_schools'=>['Stamford American SG (2019–now)','GEMS World Academy SG (2016–2019)'],'certifications'=>['IB PYP Cat. 3','Orton-Gillingham L1'],'languages'=>['English','Mandarin'],'cv_url'=>'/cv/grace-lim.pdf','availability'=>'2026-08-01','desired_salary'=>28000000,'preferred_campus'=>'int','notes'=>'Relocating to Jakarta in August.'],
            ['code'=>'TP-2008','name'=>'Haris Pramudya, S.Pd','gender'=>'M','email'=>'haris.p@gmail.com','phone'=>'+62 815-9090-1010','city'=>'Tangerang','age'=>30,'education'=>'S.Pd PJOK — UPI (2018)','qualification'=>'S1','years'=>6,'subjects'=>['PE & Health'],'applied_at'=>'2026-04-12','source'=>'walk-in','current_school'=>'SMA Santa Ursula BSD','past_schools'=>['SMA Santa Ursula (2020–now)','SMP Strada (2018–2020)'],'certifications'=>['Coaching Cert (Basketball)','First Aid'],'languages'=>['Indonesian','English'],'availability'=>'1 month','desired_salary'=>10000000,'preferred_campus'=>'sd'],
            ['code'=>'TP-2009','name'=>'Ira Wulandari, M.Si','gender'=>'F','email'=>'ira.wulan@gmail.com','phone'=>'+62 812-1212-3434','city'=>'Yogyakarta','age'=>33,'education'=>'M.Si Chemistry — UGM (2017)','qualification'=>'S2','years'=>8,'subjects'=>['Chemistry'],'applied_at'=>'2026-04-15','source'=>'website','current_school'=>'SMA Stella Duce 1 Yogyakarta','past_schools'=>['SMA Stella Duce 1 (2019–now)','SMA Kolese De Britto (2017–2019)'],'certifications'=>['Serdik','BNSP Lab Assessor'],'languages'=>['Indonesian','English'],'cv_url'=>'/cv/ira-wulandari.pdf','availability'=>'2026-07-01','desired_salary'=>14000000,'preferred_campus'=>'sma','notes'=>'Open to relocation to Jakarta.'],
            ['code'=>'TP-2010','name'=>'Joko Triyono, S.Sn','gender'=>'M','email'=>'joko.tri@gmail.com','phone'=>'+62 819-5555-6666','city'=>'Jakarta','age'=>29,'education'=>'S.Sn Fine Arts — ISI Yogyakarta (2020)','qualification'=>'S1','years'=>5,'subjects'=>['Visual Arts','Design'],'applied_at'=>'2026-04-20','source'=>'website','current_school'=>'High Scope Indonesia','past_schools'=>['High Scope (2021–now)'],'certifications'=>['Adobe Certified Educator'],'languages'=>['Indonesian','English'],'availability'=>'immediate','desired_salary'=>11000000,'preferred_campus'=>'smp'],
            ['code'=>'TP-2011','name'=>'Kenny Wijaya, B.Ed','gender'=>'M','email'=>'kenny.wijaya@gmail.com','phone'=>'+62 813-1111-9999','city'=>'Jakarta','age'=>32,'education'=>'B.Ed Mandarin — Beijing Normal University (2016)','qualification'=>'S1','years'=>8,'subjects'=>['Mandarin','Chinese Literature'],'applied_at'=>'2026-04-22','source'=>'referral','current_school'=>'Bina Bangsa School Pluit','past_schools'=>['Bina Bangsa Pluit (2019–now)','Sekolah Tunas Bangsa (2016–2019)'],'certifications'=>['HSK 6','TCSOL'],'languages'=>['Indonesian','English','Mandarin'],'cv_url'=>'/cv/kenny-wijaya.pdf','availability'=>'1 month','desired_salary'=>16000000,'preferred_campus'=>'sma','notes'=>'Bilingual native — keep on file for Mandarin Coordinator role.'],
            ['code'=>'TP-2012','name'=>'Larasati Putri, S.Pd','gender'=>'F','email'=>'larasati.p@gmail.com','phone'=>'+62 812-8888-7777','city'=>'Depok','age'=>25,'education'=>'S.Pd Biology — UNJ (2023)','qualification'=>'S1','years'=>1,'subjects'=>['Biology','Science'],'applied_at'=>'2026-04-28','source'=>'fair','current_school'=>'Fresh graduate / SMA Negeri 1 Depok (internship)','past_schools'=>['SMAN 1 Depok PPL (2022–2023)'],'languages'=>['Indonesian','English'],'availability'=>'immediate','desired_salary'=>7000000,'preferred_campus'=>'smp','notes'=>'Career-fair contact, strong potential.'],
            ['code'=>'TP-2013','name'=>'Marcellino Tan, M.A','gender'=>'M','email'=>'marcell.tan@outlook.com','phone'=>'+62 811-2222-9090','city'=>'Jakarta','age'=>40,'education'=>'M.A History — University of Melbourne (2012)','qualification'=>'S2','years'=>14,'subjects'=>['History','Social Studies','TOK'],'applied_at'=>'2026-05-01','source'=>'website','current_school'=>'JIS — Jakarta Intercultural School','past_schools'=>['JIS (2018–now)','ACG School (2014–2018)','SMA Cita Buana (2012–2014)'],'certifications'=>['IBDP History HL','IBDP TOK'],'languages'=>['Indonesian','English','French'],'cv_url'=>'/cv/marcellino-tan.pdf','availability'=>'2026-08-01','desired_salary'=>32000000,'preferred_campus'=>'int','notes'=>'Premier candidate — flag for any IB Humanities opening.'],
            ['code'=>'TP-2014','name'=>'Nadia Salim, S.Pd','gender'=>'F','email'=>'nadia.salim@gmail.com','phone'=>'+62 815-3030-4040','city'=>'Jakarta','age'=>27,'education'=>'S.Pd Primary Education — UPI (2021)','qualification'=>'S1','years'=>3,'subjects'=>['Primary Class Teacher','Tematik'],'applied_at'=>'2026-05-05','source'=>'website','current_school'=>'Sekolah Mentari Bilingual','past_schools'=>['Sekolah Mentari (2021–now)'],'languages'=>['Indonesian','English'],'availability'=>'1 month','desired_salary'=>8500000,'preferred_campus'=>'sd'],
            ['code'=>'TP-2015','name'=>'Omar Faridzi, S.Pd','gender'=>'M','email'=>'omar.faridzi@gmail.com','phone'=>'+62 812-6060-7070','city'=>'Jakarta','age'=>31,'education'=>'S.Pd Pendidikan Agama Islam — UIN (2018)','qualification'=>'S1','years'=>6,'subjects'=>['Religion (Islam)','Arabic'],'applied_at'=>'2026-05-08','source'=>'walk-in','current_school'=>'SMP Al-Azhar 1 Jakarta','past_schools'=>['SMP Al-Azhar 1 (2020–now)','MTs Negeri 4 (2018–2020)'],'certifications'=>['Serdik','Tahfiz 30 Juz'],'languages'=>['Indonesian','Arabic','English'],'availability'=>'immediate','desired_salary'=>9500000,'preferred_campus'=>'smp'],
        ];

        foreach ($rows as $r) {
            \App\Models\Candidate::create($r + [
                'stage'        => 'applied',
                'priority'     => 'normal',
                'talent_pool'  => true,
                'shortlisted'  => false,
                'vacancy_id'   => null,
            ]);
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
            ['code'=>'TCH-2020-009','employee_no'=>'EMP-2010-003','name'=>'Pak Dwi','gender'=>'M','dob'=>'1972-04-18','email'=>'dwi@sutomo.sch.id','phone'=>'+62 812-4040-5050','subject'=>'School Leadership','dept'=>'Administration','campus'=>'sma','status'=>'permanent','employment'=>'full-time','joined_at'=>'2010-07-01','tenure'=>'15y 10m','contract'=>'Permanent — Principal SMA','education'=>'Drs. Education Management — UPI','certifications'=>['Cambridge School Leader','Asesor BAN-SM'],'languages'=>['Indonesian','English'],'city'=>'Jakarta','rating'=>4.9,'last_review'=>'2025-12-20'],
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
            ['code'=>'INT-301','candidate'=>'C-1005','scheduled_date'=>'2026-05-16','scheduled_time'=>'09:30','room'=>'Meeting Room A','panel'=>['Pak Dwi','Maria Hartanto','Sri Lestari'],'type'=>'Panel Interview','status'=>'scheduled'],
            ['code'=>'INT-302','candidate'=>'C-1004','scheduled_date'=>'2026-05-17','scheduled_time'=>'11:00','room'=>'Meeting Room B','panel'=>['Pak Dwi','Maria Hartanto'],'type'=>'Micro-Teaching','status'=>'scheduled'],
            ['code'=>'INT-303','candidate'=>'C-1002','scheduled_date'=>'2026-05-04','scheduled_time'=>'10:00','room'=>'Meeting Room A','panel'=>['Pak Dwi','HoD English'],'type'=>'Final Panel','status'=>'completed','recommendation'=>'Strong Hire'],
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
            ['occurred_at'=>'2026-05-14 16:42','user_name'=>'Pak Dwi','role'=>'Principal','action'=>'override.score','target'=>'C-1001','from_value'=>'87','to_value'=>'90','note'=>'Adjusted micro-teaching score after panel review.'],
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

    private function seedPrincipalModule(): void
    {
        \App\Models\User::firstOrCreate(
            ['email' => 'principal@sutomo.sch.id'],
            ['name' => 'Pak Dwi', 'password' => bcrypt('password'), 'role' => 'principal', 'campus' => 'sma']
        );

        EnrollmentPeriod::firstOrCreate(['name' => 'TA 2026/2027 — SMA'], [
            'campus' => 'sma', 'unit' => 'SMA', 'opens_at' => '2026-04-01', 'closes_at' => '2026-07-15', 'status' => 'open', 'quota' => 120,
            'pass_threshold' => 70, 'fail_threshold' => 50,
            'exam_starts_at' => now()->addDays(20)->setTime(8,0), 'exam_venue' => 'Aula Sutomo 1 — Lt. 3',
            'exam_instructions' => 'Bawa kartu ujian, alat tulis, dan kalkulator. Datang 30 menit sebelum mulai.',
            'observation_start_date' => now()->subDays(2)->toDateString(), 'observation_days' => 5,
            'application_fee' => 300000, 'payment_expiry_hours' => 24,
        ]);
        EnrollmentPeriod::firstOrCreate(['name' => 'TA 2026/2027 — SMP'], [
            'campus' => 'smp', 'unit' => 'SMP', 'opens_at' => '2026-04-01', 'closes_at' => '2026-07-15', 'status' => 'open', 'quota' => 140,
            'pass_threshold' => 68, 'fail_threshold' => 48,
            'exam_starts_at' => now()->addDays(22)->setTime(8,0), 'exam_venue' => 'Aula Sutomo 2 — Lt. 2',
            'exam_instructions' => 'Bawa kartu ujian dan alat tulis. Datang 30 menit lebih awal.',
            'observation_start_date' => now()->subDays(3)->toDateString(), 'observation_days' => 5,
            'application_fee' => 300000, 'payment_expiry_hours' => 24,
        ]);
        EnrollmentPeriod::firstOrCreate(['name' => 'TA 2026/2027 — SD'], [
            'campus' => 'sd', 'unit' => 'SD', 'opens_at' => '2026-03-15', 'closes_at' => '2026-07-31', 'status' => 'open', 'quota' => 180,
            'pass_threshold' => 65, 'fail_threshold' => 45,
            'exam_starts_at' => now()->addDays(15)->setTime(9,0), 'exam_venue' => 'Ruang Asesmen SD',
            'exam_instructions' => 'Wawancara singkat dan tes psikomotor. Wajib didampingi orang tua.',
            'observation_start_date' => now()->subDays(4)->toDateString(), 'observation_days' => 5,
            'application_fee' => 300000, 'payment_expiry_hours' => 24,
        ]);

        $classDefs = [
            ['SMA-10-IPA-A','X IPA-A','sma','10','ipa','A.201',28],
            ['SMA-10-IPA-B','X IPA-B','sma','10','ipa','A.202',28],
            ['SMA-10-IPS-A','X IPS-A','sma','10','ips','A.203',28],
            ['SMA-11-IPA-A','XI IPA-A','sma','11','ipa','A.301',28],
            ['SMA-12-IPA-A','XII IPA-A','sma','12','ipa','A.401',26],
            ['SMP-7-A','VII-A','smp','7','umum','B.101',30],
            ['SMP-7-B','VII-B','smp','7','umum','B.102',30],
            ['SMP-8-A','VIII-A','smp','8','umum','B.201',30],
            ['SMP-9-A','IX-A','smp','9','umum','B.301',30],
            ['SD-3-A','3-A','sd','3','umum','C.103',24],
            ['SD-4-A','4-A','sd','4','umum','C.104',24],
            ['SD-5-A','5-A','sd','5','umum','C.105',24],
        ];
        $teachers = Teacher::pluck('id')->all();
        foreach ($classDefs as $i => [$code,$name,$campus,$grade,$stream,$room,$cap]) {
            SchoolClass::firstOrCreate(['code' => $code], [
                'name' => $name, 'campus' => $campus, 'grade' => $grade, 'stream' => $stream,
                'room' => $room, 'capacity' => $cap, 'homeroom_teacher_id' => $teachers[$i % max(1,count($teachers))] ?? null,
            ]);
        }

        $classes = SchoolClass::all();
        $firstNames = ['Ahmad','Putri','Kevin','Anissa','Rio','Citra','Bayu','Dewi','Eko','Sari','Joko','Maya','Nanda','Oki','Putu','Rahma','Sinta','Tomi','Umi','Vino','Wahyu','Yanti','Zaki','Beni','Hana','Indra','Kiki','Lulu','Mira','Naufal','Olive','Pandu','Qori','Rangga','Sigit','Tika','Ulil','Vera','Wanda','Yoga'];
        $lastNames  = ['Wijaya','Hartanto','Lestari','Pratama','Santoso','Halim','Suryadi','Anggraini','Saputra','Kusuma','Rahman','Setiawan','Putra','Putri','Sari','Nugraha','Permadi','Yuwono','Mahendra'];
        $cities = ['Jakarta','Bekasi','Tangerang','Depok','Bogor'];
        $religions = ['Islam','Kristen','Katolik','Buddha','Hindu'];

        $studentN = 0;
        foreach ($classes as $c) {
            $count = (int) round($c->capacity * fake()->randomFloat(2, 0.65, 0.95));
            for ($i = 0; $i < $count; $i++) {
                $studentN++;
                $g = fake()->randomElement(['M','F']);
                $attendance = fake()->numberBetween(70, 100);
                $gpa = fake()->randomFloat(2, 60, 95);
                Student::create([
                    'nis' => 'S' . str_pad((string)$studentN, 5, '0', STR_PAD_LEFT),
                    'name' => $firstNames[array_rand($firstNames)].' '.$lastNames[array_rand($lastNames)],
                    'gender' => $g, 'dob' => now()->subYears(fake()->numberBetween(7,18))->subDays(fake()->numberBetween(1,360))->toDateString(),
                    'religion' => fake()->randomElement($religions), 'ethnicity' => fake()->randomElement(['Jawa','Sunda','Tionghoa','Batak','Minang']),
                    'city' => fake()->randomElement($cities), 'campus' => $c->campus, 'unit' => strtoupper($c->campus),
                    'grade' => $c->grade, 'stream' => $c->stream, 'school_class_id' => $c->id,
                    'status' => fake()->randomElement(['active','active','active','active','active','observation']),
                    'enrolled_at' => now()->subMonths(fake()->numberBetween(6,36))->toDateString(),
                    'parent_name' => 'Bpk./Ibu '.$lastNames[array_rand($lastNames)],
                    'parent_phone' => '+62 81' . fake()->numerify('########'),
                    'parent_email' => fake()->safeEmail(),
                    'attendance_rate' => $attendance, 'gpa' => $gpa,
                    'fee_status' => $attendance < 80 ? fake()->randomElement(['paid','arrears','arrears']) : fake()->randomElement(['paid','paid','paid','paid','arrears']),
                ]);
            }
        }

        $pipeline = Application::PIPELINE;
        $extra = ['failed','waitlisted','withdrawn','declined'];
        $methods = array_keys(Application::PAYMENT_METHODS);
        $religionList = $religions;
        $ethnicityList = ['Jawa','Sunda','Tionghoa','Batak','Minang','Melayu','Bali'];
        $periodsByUnit = [
            'sma' => EnrollmentPeriod::where('campus','sma')->first()?->id,
            'smp' => EnrollmentPeriod::where('campus','smp')->first()?->id,
            'sd'  => EnrollmentPeriod::where('campus','sd')->first()?->id,
            'tk'  => EnrollmentPeriod::where('campus','sd')->first()?->id,
            'playgroup'   => EnrollmentPeriod::where('campus','sd')->first()?->id,
            'pre_nursery' => EnrollmentPeriod::where('campus','sd')->first()?->id,
        ];
        // Sutomo demo distribution: realistic mix across the 3 physical campuses & 6 units.
        // Sutomo 1 (CMP-001 Thamrin, CMP-002 Bintang) runs the full Pre-Nursery..SMA spectrum.
        // Sutomo 2 (CMP-003 Brayan) is younger — Pre-Nursery..SMP only.
        $sutomoMix = [
            // [unit slug, physical campus code]
            ['sd', 'CMP-001'], ['sd', 'CMP-001'], ['sd', 'CMP-002'], ['sd', 'CMP-003'],
            ['smp','CMP-001'], ['smp','CMP-002'], ['smp','CMP-003'],
            ['sma','CMP-001'], ['sma','CMP-002'],
            ['tk', 'CMP-001'], ['tk', 'CMP-002'], ['tk', 'CMP-003'],
            ['playgroup',   'CMP-002'], ['playgroup',   'CMP-003'],
            ['pre_nursery', 'CMP-001'], ['pre_nursery', 'CMP-003'],
        ];
        $gradeByUnit = [
            'pre_nursery' => 'PN', 'playgroup' => 'PG', 'tk' => fn() => 'TK-' . fake()->numberBetween(1, 2),
            'sd' => fn() => (string) fake()->numberBetween(1, 6),
            'smp' => fn() => (string) fake()->numberBetween(7, 9),
            'sma' => fn() => (string) fake()->numberBetween(10, 12),
        ];
        for ($i = 1; $i <= 35; $i++) {
            $status = $i <= 28 ? $pipeline[array_rand($pipeline)] : $extra[array_rand($extra)];
            [$unitSlug, $campusCode] = $sutomoMix[array_rand($sutomoMix)];
            $grade = $gradeByUnit[$unitSlug];
            if (is_callable($grade)) $grade = $grade();
            $paid = fake()->boolean(70);
            $paymentStatus = $paid ? 'paid' : 'pending';
            $hasReceipt = $paid && fake()->boolean(70);
            // Age range matched to unit so the dossier looks coherent.
            $ageRange = match ($unitSlug) {
                'pre_nursery' => [2, 3], 'playgroup' => [3, 4], 'tk' => [4, 6],
                'sd' => [6, 12], 'smp' => [12, 15], 'sma' => [15, 18],
            };
            Application::create([
                'code' => 'APP-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT),
                'name' => $firstNames[array_rand($firstNames)].' '.$lastNames[array_rand($lastNames)],
                'gender' => fake()->randomElement(['M','F']),
                'dob' => now()->subYears(fake()->numberBetween($ageRange[0], $ageRange[1]))->toDateString(),
                'birthplace' => fake()->randomElement(['Medan','Jakarta','Surabaya','Bandung','Pematangsiantar']),
                'nisn' => fake()->numerify('##########'),
                'religion' => fake()->randomElement($religionList),
                'ethnicity' => fake()->randomElement($ethnicityList),
                'address' => fake()->streetAddress(),
                'city' => fake()->randomElement($cities),
                'parent_name' => 'Bpk./Ibu '.$lastNames[array_rand($lastNames)],
                'parent_phone' => '+62 81'.fake()->numerify('########'),
                'parent_whatsapp' => fake()->boolean(60) ? '+62 81'.fake()->numerify('########') : null,
                'parent_email' => fake()->safeEmail(),
                'parent_occupation' => fake()->randomElement(['Wiraswasta','PNS','Karyawan Swasta','Dokter','Guru','Ibu Rumah Tangga']),
                'current_school' => fake()->randomElement(['SD Tarakanita','SMP Pelita','SMA Cita Buana','SD Mardi Yuana','SMP Kanisius','SD IPEKA']),
                // `campus` column stores the UNIT slug (matches existing app semantics + FeeSchedule).
                // `unit` column stores the PHYSICAL campus code (CMP-001..003).
                'campus' => $unitSlug,
                'unit'   => $campusCode,
                'grade' => $grade,
                'stream' => $unitSlug === 'sma' ? fake()->randomElement(['ipa','ips']) : null,
                'enrollment_period_id' => $periodsByUnit[$unitSlug] ?? null,
                'applicant_type' => fake()->randomElement(['new','new','new','new','existing','existing','returning']),
                'is_teacher_child' => fake()->boolean(10),
                'is_existing_student' => fake()->boolean(25),
                'existing_unit' => fake()->boolean(25) ? fake()->randomElement(['tk','sd','smp','sma']) : null,
                'orphan_status' => fake()->randomElement(['none','none','none','none','none','yatim','piatu']),
                'status' => $status,
                'applied_at' => now()->subDays(fake()->numberBetween(1, 55))->toDateString(),
                'exam_date' => in_array($status, ['exam_scheduled','passed','failed']) ? now()->addDays(fake()->numberBetween(-10, 14))->toDateString() : null,
                'placement_score' => in_array($status, ['passed','failed','accepted','dev_fee','class_assigned','activated']) ? fake()->numberBetween(45, 95) : null,
                'placement_recommendation' => in_array($status, ['passed','accepted']) ? fake()->randomElement(['Grade per request','One grade up','Extra support recommended']) : null,
                'payment_status' => $paymentStatus,
                'payment_method' => $paid ? fake()->randomElement($methods) : null,
                'payment_amount' => 300000,
                'payment_paid_at' => $paid ? now()->subDays(fake()->numberBetween(0, 40)) : null,
                'invoice_no' => $paid ? 'INV-2026-' . str_pad((string)$i, 5, '0', STR_PAD_LEFT) : null,
                'receipt_file' => $hasReceipt ? 'receipts/sample-receipt.pdf' : null,
                'waitlisted' => $status === 'waitlisted',
                'meta' => [
                    'documents' => fake()->randomElements([
                        'applications/docs/akta-lahir.pdf',
                        'applications/docs/kartu-keluarga.pdf',
                        'applications/docs/rapor-terakhir.pdf',
                        'applications/docs/pas-foto.jpg',
                        'applications/docs/surat-keterangan-sekolah.pdf',
                        'applications/docs/ijazah.pdf',
                    ], fake()->numberBetween(2, 5)),
                ],
            ]);
        }

        // Onboarding-stage demo data — give the Onboarding Flow page rich content.
        // Each accepted/observing app gets devfee+books+class meta; observing apps
        // also get a partially-filled 5-day attendance grid marked by Sutomo teachers.
        $teacherNames = ['Ibu Lisa Wijaya', 'Pak Hendra Tan', 'Ibu Mei Chen', 'Pak Bagus Saputra'];
        $onboardingApps = Application::whereIn('status', ['accepted','dev_fee','books','class_assigned','observing','id_issued','tuition','activated'])->get();
        foreach ($onboardingApps as $oa) {
            $m = $oa->meta ?? [];
            $unitLabel = strtoupper($oa->campus ?? 'X');
            $m['devfee'] = ['paid_at' => now()->subDays(fake()->numberBetween(10, 25))->toIso8601String(), 'amount' => 5_000_000];
            $m['books']  = ['bought_at' => now()->subDays(fake()->numberBetween(7, 15))->toIso8601String(), 'amount' => 1_500_000];
            $m['class']  = [
                'label'   => "{$unitLabel}-{$oa->grade}-A",
                'teacher' => $teacherNames[array_rand($teacherNames)],
                'assigned_at' => now()->subDays(fake()->numberBetween(5, 10))->toIso8601String(),
            ];

            // Observation: use the period's window so the cohort shares dates.
            $seedObs = in_array($oa->status, ['observing','id_issued','tuition','activated'], true)
                || ($oa->status === 'class_assigned' && fake()->boolean(60));

            if ($seedObs) {
                $teacher = $teacherNames[array_rand($teacherNames)];
                $period = $oa->enrollment_period_id ? EnrollmentPeriod::find($oa->enrollment_period_id) : null;
                if ($period && $period->observation_start_date) {
                    $cursor = \Illuminate\Support\Carbon::parse($period->observation_start_date);
                    $totalDays = max(3, (int) ($period->observation_days ?? 5));
                } else {
                    $cursor = now()->subDays(fake()->numberBetween(2, 6))->startOfDay();
                    while ($cursor->isWeekend()) $cursor->subDay();
                    $totalDays = 5;
                }

                $days = [];
                $daysMarked = match ($oa->status) {
                    'observing'     => fake()->numberBetween(2, 4), // in progress
                    default         => $totalDays,                   // complete
                };
                for ($d = 0; $d < $totalDays; $d++) {
                    while ($cursor->isWeekend()) $cursor->addDay();
                    $dateKey = $cursor->toDateString();
                    if ($d < $daysMarked) {
                        $present = fake()->boolean(82);
                        $days[$dateKey] = [
                            'present' => $present,
                            'by'      => $teacher,
                            'at'      => $cursor->copy()->setTime(8, fake()->numberBetween(0, 30))->toIso8601String(),
                        ];
                    } else {
                        $days[$dateKey] = ['present' => null, 'by' => null, 'at' => null];
                    }
                    $cursor->addDay();
                }
                $m['observation'] = [
                    'start_date' => array_key_first($days),
                    'days'       => $days,
                ];
            }
            $oa->meta = $m;
            $oa->save();
        }
        $allStudents = Student::all();
        for ($i = 1; $i <= 22; $i++) {
            $student = $allStudents->random();
            $severity = fake()->randomElement(['low','low','medium','high','critical']);
            $status = fake()->randomElement(['open','open','unit_review','vp_review','principal_action','closed']);
            BehaviorLog::create([
                'student_id' => $student->id,
                'teacher_id' => $teachers ? $teachers[array_rand($teachers)] : null,
                'occurred_at' => now()->subDays(fake()->numberBetween(0, 60))->toDateString(),
                'category' => fake()->randomElement(array_keys(BehaviorLog::CATEGORIES)),
                'severity' => $severity,
                'title' => fake()->randomElement(['Tardiness repeated','Disruptive in class','Bullying report','Uniform violation','Excellent leadership','Cheating suspected','Mobile phone violation','Late submission']),
                'notes' => fake()->sentence(12),
                'status' => $status,
                'action_taken' => $status === 'closed' ? fake()->randomElement(['Counseled','Detention','Parent meeting','Warning letter']) : null,
            ]);
        }

        for ($i = 1; $i <= 12; $i++) {
            $tid = $teachers[array_rand($teachers)];
            $starts = now()->addDays(fake()->numberBetween(-30, 30));
            TeacherLeave::create([
                'teacher_id' => $tid,
                'type' => fake()->randomElement(array_keys(TeacherLeave::TYPES)),
                'starts_at' => $starts->toDateString(),
                'ends_at' => $starts->copy()->addDays(fake()->numberBetween(1, 5))->toDateString(),
                'reason' => fake()->sentence(8),
                'status' => fake()->randomElement(['pending','pending','approved','approved','rejected']),
                'substitute_teacher_id' => fake()->boolean(60) ? $teachers[array_rand($teachers)] : null,
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            $s = $allStudents->random();
            SscRequest::create([
                'code' => 'SSC-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT),
                'student_id' => $s->id,
                'type' => fake()->randomElement(array_keys(SscRequest::TYPES)),
                'priority' => fake()->randomElement(['low','normal','normal','high']),
                'status' => fake()->randomElement(['pending','clearance','approved','issued']),
                'requested_at' => now()->subDays(fake()->numberBetween(0, 40))->toDateString(),
                'notes' => fake()->sentence(6),
            ]);
        }

        $procRows = [
            ['PR-2026-001','books','New SMA Physics lab textbook set (Grade 11)','sma',8500000,'pending'],
            ['PR-2026-002','equipment','Smart whiteboard for Class XI IPA-A','sma',12500000,'principal_review'],
            ['PR-2026-003','uniforms','Olympiad team uniforms (Mathematics)','sma',4250000,'approved'],
            ['PR-2026-004','classroom','SD furniture refresh — 3 classrooms','sd',24000000,'yayasan_review'],
            ['PR-2026-005','event','Graduation ceremony decorations','sma',6500000,'pending'],
            ['PR-2026-006','equipment','Mandarin language lab headphones','smp',3800000,'principal_review'],
        ];
        foreach ($procRows as [$code,$cat,$title,$campus,$amt,$status]) {
            ProcurementRequest::firstOrCreate(['code' => $code], [
                'category' => $cat, 'title' => $title, 'campus' => $campus,
                'amount' => $amt, 'status' => $status,
                'requested_by' => 'Maria Hartanto', 'needed_by' => now()->addDays(rand(20,60))->toDateString(),
                'description' => 'Operational request submitted via principal workflow.',
            ]);
        }

        $eventRows = [
            ['EV-001','Olimpiade Matematika SMA','competition','sma',now()->addDays(14),now()->addDays(15),'approved',80],
            ['EV-002','Field Trip ke Taman Mini','fieldtrip','sd',now()->addDays(20),now()->addDays(20),'approved',120],
            ['EV-003','Graduation Ceremony XII','graduation','sma',now()->addDays(45),now()->addDays(45),'pending',300],
            ['EV-004','PTA Meeting — Q3','pta','smp',now()->addDays(7),now()->addDays(7),'approved',60],
            ['EV-005','Robotics Club Showcase','cca','sma',now()->addDays(28),now()->addDays(28),'approved',45],
            ['EV-006','Bahasa Indonesia Speech Competition','competition','smp',now()->addDays(35),now()->addDays(35),'draft',55],
            ['EV-007','Art Exhibition — SD','cca','sd',now()->addDays(10),now()->addDays(11),'approved',90],
            ['EV-008','Career Day SMA','cca','sma',now()->addDays(50),now()->addDays(50),'pending',200],
        ];
        foreach ($eventRows as [$code,$title,$cat,$campus,$start,$end,$status,$part]) {
            SchoolEvent::firstOrCreate(['code' => $code], [
                'title' => $title, 'category' => $cat, 'campus' => $campus,
                'starts_at' => $start->toDateString(), 'ends_at' => $end->toDateString(),
                'pic' => fake()->randomElement(['Maria Hartanto','Rini Surya','Dimas Pratama']),
                'status' => $status, 'participants' => $part,
                'description' => fake()->sentence(14),
            ]);
        }

        for ($i = 0; $i < 18; $i++) {
            $tid = $teachers[array_rand($teachers)];
            $status = fake()->randomElement(['scheduled','scheduled','completed','completed','training']);
            $score = $status === 'completed' || $status === 'training' ? fake()->numberBetween(60, 95) : null;
            SupervisiEvaluation::create([
                'teacher_id' => $tid,
                'scheduled_at' => now()->subDays(fake()->numberBetween(-30, 60))->toDateString(),
                'evaluator' => fake()->randomElement(['Pak Dwi','Maria Hartanto']),
                'round' => 'Q' . fake()->numberBetween(1, 4),
                'score' => $score,
                'status' => $status,
                'strengths' => $score ? 'Strong subject knowledge, clear delivery.' : null,
                'improvements' => $score && $score < 80 ? 'Improve classroom management and pacing.' : null,
                'training_recommended' => $status === 'training' ? fake()->randomElement(['Classroom management','Differentiated instruction','Tech integration']) : null,
            ]);
        }
    }

    /* ----------------------------------------------------------------
     | Book + e-Book catalog (Phase Y)
     | Seeds packages, payment accounts, ebook platforms and packs so
     | Principal → Onboarding modals have real data to pick from.
     ---------------------------------------------------------------- */
    private function seedEnrollmentCatalog(): void
    {
        $year = '2026/2027';

        // ---- Payment accounts: one per campus, books purpose ----
        $accounts = [
            ['campus'=>'CMP-001','bank_name'=>'BCA',    'account_no'=>'0123456789','account_name'=>'Yayasan Sutomo — Buku Pelajaran (Thamrin)','va_prefix'=>'88810'],
            ['campus'=>'CMP-002','bank_name'=>'Mandiri','account_no'=>'1230099887','account_name'=>'Yayasan Sutomo — Buku Pelajaran (Sutomo 2)','va_prefix'=>'88820'],
            ['campus'=>'CMP-003','bank_name'=>'BNI',    'account_no'=>'9988776655','account_name'=>'Yayasan Sutomo — Buku Pelajaran (Brayan)','va_prefix'=>'88830'],
        ];
        foreach ($accounts as $row) {
            PaymentAccount::firstOrCreate(
                ['campus' => $row['campus'], 'purpose' => 'books'],
                array_merge($row, ['purpose' => 'books', 'is_active' => true])
            );
        }

        // ---- Book packages: one per (unit, representative grade) ----
        $packages = [
            ['unit'=>'pre_nursery','grade'=>'PN',  'name'=>'Pre-Nursery — Paket Aktivitas', 'items'=>[
                ['title'=>'Activity Book Level A','qty'=>1,'unit_price'=>120000],
                ['title'=>'Crayons & Tracing Pack','qty'=>1,'unit_price'=>85000],
            ]],
            ['unit'=>'playgroup','grade'=>'PG',    'name'=>'Playgroup — Paket Belajar', 'items'=>[
                ['title'=>'Playgroup Workbook','qty'=>1,'unit_price'=>140000],
                ['title'=>'Story Kit (5 buku)','qty'=>1,'unit_price'=>175000],
            ]],
            ['unit'=>'tk','grade'=>'TK-1',          'name'=>'TK-A — Paket Buku 2026/2027', 'items'=>[
                ['title'=>'Tematik TK-A','qty'=>1,'unit_price'=>180000],
                ['title'=>'Bahasa & Angka','qty'=>1,'unit_price'=>165000],
                ['title'=>'Buku Mewarnai','qty'=>1,'unit_price'=>75000],
            ]],
            ['unit'=>'tk','grade'=>'TK-2',          'name'=>'TK-B — Paket Buku 2026/2027', 'items'=>[
                ['title'=>'Tematik TK-B','qty'=>1,'unit_price'=>195000],
                ['title'=>'Pra-Membaca & Pra-Menulis','qty'=>1,'unit_price'=>170000],
                ['title'=>'Buku Mewarnai Advanced','qty'=>1,'unit_price'=>85000],
            ]],
            ['unit'=>'sd','grade'=>'1',             'name'=>'SD Grade 1 — Paket Buku 2026/2027', 'items'=>[
                ['title'=>'Matematika Kelas 1 — Erlangga','qty'=>1,'unit_price'=>95000],
                ['title'=>'Bahasa Indonesia Kelas 1','qty'=>1,'unit_price'=>87000],
                ['title'=>'English Time 1','qty'=>1,'unit_price'=>165000],
                ['title'=>'Tematik Terpadu Kelas 1','qty'=>1,'unit_price'=>140000],
            ]],
            ['unit'=>'sd','grade'=>'4',             'name'=>'SD Grade 4 — Paket Buku 2026/2027', 'items'=>[
                ['title'=>'Matematika Kelas 4','qty'=>1,'unit_price'=>105000],
                ['title'=>'IPA Kelas 4','qty'=>1,'unit_price'=>110000],
                ['title'=>'IPS Kelas 4','qty'=>1,'unit_price'=>98000],
                ['title'=>'English Time 4','qty'=>1,'unit_price'=>180000],
            ]],
            ['unit'=>'sd','grade'=>'6',             'name'=>'SD Grade 6 — Paket Buku 2026/2027', 'items'=>[
                ['title'=>'Matematika Kelas 6','qty'=>1,'unit_price'=>120000],
                ['title'=>'IPA Kelas 6','qty'=>1,'unit_price'=>118000],
                ['title'=>'IPS Kelas 6','qty'=>1,'unit_price'=>108000],
                ['title'=>'English Time 6','qty'=>1,'unit_price'=>195000],
                ['title'=>'Persiapan US/USP','qty'=>1,'unit_price'=>145000],
            ]],
            ['unit'=>'smp','grade'=>'7',            'name'=>'SMP Grade 7 — Paket Buku 2026/2027', 'items'=>[
                ['title'=>'Matematika Kelas 7','qty'=>1,'unit_price'=>135000],
                ['title'=>'IPA Terpadu Kelas 7','qty'=>1,'unit_price'=>140000],
                ['title'=>'IPS Terpadu Kelas 7','qty'=>1,'unit_price'=>128000],
                ['title'=>'Mandarin 入门','qty'=>1,'unit_price'=>165000],
                ['title'=>'English Headway 1','qty'=>1,'unit_price'=>210000],
            ]],
            ['unit'=>'smp','grade'=>'9',            'name'=>'SMP Grade 9 — Paket Buku 2026/2027', 'items'=>[
                ['title'=>'Matematika Kelas 9','qty'=>1,'unit_price'=>148000],
                ['title'=>'IPA Kelas 9','qty'=>1,'unit_price'=>152000],
                ['title'=>'IPS Kelas 9','qty'=>1,'unit_price'=>138000],
                ['title'=>'Mandarin HSK 2','qty'=>1,'unit_price'=>178000],
                ['title'=>'Persiapan UN/UNBK','qty'=>1,'unit_price'=>175000],
            ]],
            ['unit'=>'sma','grade'=>'10',           'name'=>'SMA Grade 10 — Paket Buku 2026/2027', 'items'=>[
                ['title'=>'Matematika Wajib Kelas 10','qty'=>1,'unit_price'=>165000],
                ['title'=>'Fisika Kelas 10','qty'=>1,'unit_price'=>168000],
                ['title'=>'Kimia Kelas 10','qty'=>1,'unit_price'=>168000],
                ['title'=>'Biologi Kelas 10','qty'=>1,'unit_price'=>162000],
                ['title'=>'English Cambridge IGCSE Y10','qty'=>1,'unit_price'=>295000],
            ]],
            ['unit'=>'sma','grade'=>'11',           'name'=>'SMA Grade 11 — Paket Buku 2026/2027', 'items'=>[
                ['title'=>'Matematika Peminatan Kelas 11','qty'=>1,'unit_price'=>178000],
                ['title'=>'Fisika Kelas 11','qty'=>1,'unit_price'=>182000],
                ['title'=>'Kimia Kelas 11','qty'=>1,'unit_price'=>178000],
                ['title'=>'IB Math AA SL Y1','qty'=>1,'unit_price'=>340000],
            ]],
            ['unit'=>'sma','grade'=>'12',           'name'=>'SMA Grade 12 — Paket Buku 2026/2027', 'items'=>[
                ['title'=>'Matematika Peminatan Kelas 12','qty'=>1,'unit_price'=>190000],
                ['title'=>'Fisika Kelas 12','qty'=>1,'unit_price'=>192000],
                ['title'=>'Persiapan SNBT','qty'=>1,'unit_price'=>225000],
                ['title'=>'IB Math AA SL Y2','qty'=>1,'unit_price'=>360000],
            ]],
        ];
        foreach ($packages as $p) {
            BookPackage::firstOrCreate(
                ['unit' => $p['unit'], 'grade' => $p['grade'], 'school_year' => $year],
                ['name' => $p['name'], 'items' => $p['items'], 'is_active' => true]
            );
        }

        // ---- e-Book platforms ----
        $platforms = [
            ['name'=>'Quipper',          'base_url'=>'https://learn.quipper.com'],
            ['name'=>'Ruangguru',        'base_url'=>'https://ruangguru.com'],
            ['name'=>'Pijar Sekolah',    'base_url'=>'https://pijarsekolah.id'],
            ['name'=>'Google Classroom', 'base_url'=>'https://classroom.google.com'],
            ['name'=>'Khan Academy',     'base_url'=>'https://khanacademy.org'],
            ['name'=>'Cambridge GO',     'base_url'=>'https://cambridge.org/go'],
        ];
        $platformIds = [];
        foreach ($platforms as $pl) {
            $platformIds[$pl['name']] = EbookPlatform::firstOrCreate(
                ['name' => $pl['name']],
                array_merge($pl, ['is_active' => true])
            )->id;
        }

        // ---- e-Book packs per cohort ----
        $packs = [
            ['unit'=>'pre_nursery','grade'=>'PN','default'=>'Google Classroom','name'=>'Pre-Nursery — Digital Library', 'items'=>[
                ['title'=>'Story Time Videos','platform_id'=>$platformIds['Google Classroom'],'url'=>'https://classroom.google.com/c/pn-stories','login_hint'=>'class code: pn26'],
            ]],
            ['unit'=>'playgroup','grade'=>'PG','default'=>'Google Classroom','name'=>'Playgroup — Digital Library', 'items'=>[
                ['title'=>'Phonics Songs','platform_id'=>$platformIds['Google Classroom'],'url'=>'https://classroom.google.com/c/pg-phonics','login_hint'=>'class code: pg26'],
                ['title'=>'Number Play','platform_id'=>$platformIds['Khan Academy'],'url'=>'https://khanacademy.org/kids','login_hint'=>'shared login'],
            ]],
            ['unit'=>'tk','grade'=>'TK-1','default'=>'Pijar Sekolah','name'=>'TK-A — Digital Library', 'items'=>[
                ['title'=>'Tematik TK-A (digital)','platform_id'=>$platformIds['Pijar Sekolah'],'url'=>'https://pijarsekolah.id/tk-a','login_hint'=>'parent account'],
            ]],
            ['unit'=>'tk','grade'=>'TK-2','default'=>'Pijar Sekolah','name'=>'TK-B — Digital Library', 'items'=>[
                ['title'=>'Tematik TK-B (digital)','platform_id'=>$platformIds['Pijar Sekolah'],'url'=>'https://pijarsekolah.id/tk-b','login_hint'=>'parent account'],
            ]],
            ['unit'=>'sd','grade'=>'1','default'=>'Quipper','name'=>'SD Grade 1 — Digital Library', 'items'=>[
                ['title'=>'Matematika 1 — Quipper','platform_id'=>$platformIds['Quipper'],'url'=>'https://learn.quipper.com/sd1-mat','login_hint'=>'NIS sebagai username'],
                ['title'=>'Bahasa Indonesia 1','platform_id'=>$platformIds['Ruangguru'],'url'=>'https://ruangguru.com/sd1-bind','login_hint'=>null],
            ]],
            ['unit'=>'sd','grade'=>'4','default'=>'Quipper','name'=>'SD Grade 4 — Digital Library', 'items'=>[
                ['title'=>'Matematika 4','platform_id'=>$platformIds['Quipper'],'url'=>'https://learn.quipper.com/sd4-mat','login_hint'=>'NIS'],
                ['title'=>'IPA 4','platform_id'=>$platformIds['Quipper'],'url'=>'https://learn.quipper.com/sd4-ipa','login_hint'=>'NIS'],
                ['title'=>'English Time 4','platform_id'=>$platformIds['Cambridge GO'],'url'=>'https://cambridge.org/go/sd4','login_hint'=>'activation code per buku'],
            ]],
            ['unit'=>'sd','grade'=>'6','default'=>'Quipper','name'=>'SD Grade 6 — Digital Library', 'items'=>[
                ['title'=>'Matematika 6','platform_id'=>$platformIds['Quipper'],'url'=>'https://learn.quipper.com/sd6-mat','login_hint'=>'NIS'],
                ['title'=>'Persiapan US/USP','platform_id'=>$platformIds['Ruangguru'],'url'=>'https://ruangguru.com/sd6-usp','login_hint'=>null],
            ]],
            ['unit'=>'smp','grade'=>'7','default'=>'Quipper','name'=>'SMP Grade 7 — Digital Library', 'items'=>[
                ['title'=>'Matematika 7','platform_id'=>$platformIds['Quipper'],'url'=>'https://learn.quipper.com/smp7-mat','login_hint'=>'NIS'],
                ['title'=>'IPA Terpadu 7','platform_id'=>$platformIds['Quipper'],'url'=>'https://learn.quipper.com/smp7-ipa','login_hint'=>'NIS'],
                ['title'=>'Mandarin 入门','platform_id'=>$platformIds['Ruangguru'],'url'=>'https://ruangguru.com/smp7-mandarin','login_hint'=>null],
            ]],
            ['unit'=>'smp','grade'=>'9','default'=>'Quipper','name'=>'SMP Grade 9 — Digital Library', 'items'=>[
                ['title'=>'Matematika 9','platform_id'=>$platformIds['Quipper'],'url'=>'https://learn.quipper.com/smp9-mat','login_hint'=>'NIS'],
                ['title'=>'Persiapan UN/UNBK','platform_id'=>$platformIds['Ruangguru'],'url'=>'https://ruangguru.com/smp9-un','login_hint'=>null],
            ]],
            ['unit'=>'sma','grade'=>'10','default'=>'Cambridge GO','name'=>'SMA Grade 10 — Digital Library', 'items'=>[
                ['title'=>'IGCSE Maths Y10','platform_id'=>$platformIds['Cambridge GO'],'url'=>'https://cambridge.org/go/sma10-math','login_hint'=>'activation code'],
                ['title'=>'Fisika 10','platform_id'=>$platformIds['Quipper'],'url'=>'https://learn.quipper.com/sma10-fisika','login_hint'=>'NIS'],
                ['title'=>'Kimia 10','platform_id'=>$platformIds['Quipper'],'url'=>'https://learn.quipper.com/sma10-kimia','login_hint'=>'NIS'],
            ]],
            ['unit'=>'sma','grade'=>'11','default'=>'Cambridge GO','name'=>'SMA Grade 11 — Digital Library', 'items'=>[
                ['title'=>'IB Math AA SL Y1','platform_id'=>$platformIds['Cambridge GO'],'url'=>'https://cambridge.org/go/ib-aa-y1','login_hint'=>'activation code'],
                ['title'=>'Fisika 11','platform_id'=>$platformIds['Quipper'],'url'=>'https://learn.quipper.com/sma11-fisika','login_hint'=>'NIS'],
            ]],
            ['unit'=>'sma','grade'=>'12','default'=>'Cambridge GO','name'=>'SMA Grade 12 — Digital Library', 'items'=>[
                ['title'=>'IB Math AA SL Y2','platform_id'=>$platformIds['Cambridge GO'],'url'=>'https://cambridge.org/go/ib-aa-y2','login_hint'=>'activation code'],
                ['title'=>'Persiapan SNBT','platform_id'=>$platformIds['Ruangguru'],'url'=>'https://ruangguru.com/sma12-snbt','login_hint'=>null],
            ]],
        ];
        foreach ($packs as $p) {
            EbookPack::firstOrCreate(
                ['unit' => $p['unit'], 'grade' => $p['grade'], 'school_year' => $year],
                [
                    'name' => $p['name'],
                    'items' => $p['items'],
                    'default_platform_id' => $platformIds[$p['default']] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
