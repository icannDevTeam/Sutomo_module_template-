<?php

namespace Database\Seeders;

use App\Models\UnitPlan;
use App\Models\UnitPlanCollaborator;
use App\Models\UnitPlanComment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Simple Cambridge / Indonesian-curriculum unit & topic planner.
 * No IB jargon — just overview, learning objectives, lessons, assessment, resources.
 */
class UnitPlanSeeder extends Seeder
{
    public function run(): void
    {
        if (UnitPlan::query()->exists()) {
            return;
        }

        $now = Carbon::now();

        $units = [
            [
                'plan' => [
                    'title'          => 'Matematika — Pecahan & Desimal',
                    'grade'          => 'Grade 4',
                    'campus'         => 'sd',
                    'theme'          => 'Mathematics',
                    'central_idea'   => 'Pecahan dan desimal adalah dua cara berbeda untuk menulis bagian dari satu kesatuan.',
                    'subjects'       => ['Matematika'],
                    'cover_emoji'    => '➗',
                    'cover_color'    => 'sky',
                    'status'         => 'active',
                    'completion_pct' => 55,
                    'starts_on'      => $now->copy()->subDays(10)->toDateString(),
                    'ends_on'        => $now->copy()->addDays(18)->toDateString(),
                    'owner_name'     => 'Ms. Andini Putri',
                    'owner_role'     => 'Grade 4 — Matematika',
                    'last_activity_at' => $now->copy()->subMinutes(15),
                    'sections' => [
                        'overview'      => "Siswa mengenal pecahan sebagai bagian dari keseluruhan, lalu menghubungkannya dengan bentuk desimal.\n\nFokus pada pemahaman visual sebelum prosedur — gunakan bar model, garis bilangan, dan benda nyata.",
                        'objectives'    => [
                            'Membaca, menulis, dan membandingkan pecahan sederhana',
                            'Mengubah pecahan ke desimal (persepuluhan & perseratusan)',
                            'Menyelesaikan soal cerita sehari-hari menggunakan pecahan',
                        ],
                        'topics'        => ['Pecahan biasa', 'Pecahan senilai', 'Desimal persepuluhan', 'Desimal perseratusan', 'Soal cerita'],
                        'prerequisites' => 'Operasi dasar bilangan bulat (kelas 3). Konsep "bagian dari" melalui pembagian benda.',
                        'assessment'    => 'Kuis mingguan + proyek akhir: membuat menu kantin pecah-pecah (harga & porsi dalam pecahan/desimal).',
                        'resources'     => ['Buku Erlangga Matematika 4', 'Kartu pecahan (set kelas)', 'Worksheet PDF — Garis Bilangan'],
                        'lessons' => [
                            ['week' => 1, 'title' => 'Apa itu pecahan? (bar model)',        'status' => 'done'],
                            ['week' => 1, 'title' => 'Pecahan senilai dengan kertas lipat', 'status' => 'done'],
                            ['week' => 2, 'title' => 'Membandingkan pecahan',               'status' => 'done'],
                            ['week' => 2, 'title' => 'Pengantar desimal',                   'status' => 'in_progress'],
                            ['week' => 3, 'title' => 'Mengubah pecahan ke desimal',         'status' => 'planned'],
                            ['week' => 4, 'title' => 'Proyek menu kantin',                  'status' => 'planned'],
                        ],
                    ],
                ],
                'collaborators' => [
                    ['name' => 'Ms. Andini Putri', 'role' => 'owner',    'color' => '#0ea5e9', 'online_now' => true,  'last_seen_at' => $now->copy()->subMinutes(1)],
                    ['name' => 'Mr. Tama Hartono', 'role' => 'editor',   'color' => '#6366f1', 'online_now' => true,  'last_seen_at' => $now->copy()->subMinutes(3)],
                    ['name' => 'Pak Dwi',          'role' => 'reviewer', 'color' => '#f59e0b', 'online_now' => false, 'last_seen_at' => $now->copy()->subDay()],
                ],
                'comments' => [
                    ['section_key' => 'objectives', 'author' => 'Pak Dwi',          'color' => '#f59e0b', 'body' => 'Bagus, tujuannya sudah jelas dan terukur.',         'created_at' => $now->copy()->subHours(4)],
                    ['section_key' => 'lessons',    'author' => 'Mr. Tama Hartono', 'color' => '#6366f1', 'body' => 'Saya bantu siapkan worksheet untuk minggu 3.',     'created_at' => $now->copy()->subMinutes(30)],
                ],
            ],

            [
                'plan' => [
                    'title'          => 'IPA — Siklus Air',
                    'grade'          => 'Grade 5',
                    'campus'         => 'sd',
                    'theme'          => 'Science',
                    'central_idea'   => 'Air bergerak dalam siklus terus-menerus antara bumi dan atmosfer.',
                    'subjects'       => ['IPA'],
                    'cover_emoji'    => '💧',
                    'cover_color'    => 'sky',
                    'status'         => 'active',
                    'completion_pct' => 30,
                    'starts_on'      => $now->copy()->subDays(4)->toDateString(),
                    'ends_on'        => $now->copy()->addDays(24)->toDateString(),
                    'owner_name'     => 'Ms. Lestari',
                    'owner_role'     => 'Grade 5 — IPA',
                    'last_activity_at' => $now->copy()->subHours(2),
                    'sections' => [
                        'overview'      => 'Siswa mempelajari evaporasi, kondensasi, presipitasi, dan infiltrasi melalui pengamatan dan eksperimen sederhana.',
                        'objectives'    => [
                            'Menjelaskan tahapan siklus air',
                            'Mengidentifikasi peran matahari dalam siklus air',
                            'Menghubungkan siklus air dengan ketersediaan air bersih di sekitar',
                        ],
                        'topics'        => ['Evaporasi', 'Kondensasi', 'Presipitasi', 'Infiltrasi', 'Konservasi air'],
                        'prerequisites' => 'Wujud zat (padat, cair, gas) — sudah dipelajari di kelas 4.',
                        'assessment'    => 'Poster siklus air + presentasi 3 menit per kelompok.',
                        'resources'     => ['Buku Tematik 5 Tema 8', 'Video animasi siklus air', 'Alat eksperimen: gelas, plastik, lampu'],
                        'lessons' => [
                            ['week' => 1, 'title' => 'Pengamatan: dari mana datangnya hujan?', 'status' => 'done'],
                            ['week' => 2, 'title' => 'Eksperimen evaporasi',                   'status' => 'in_progress'],
                            ['week' => 2, 'title' => 'Modeling siklus air',                    'status' => 'planned'],
                            ['week' => 3, 'title' => 'Diskusi: konservasi air di sekolah',     'status' => 'planned'],
                        ],
                    ],
                ],
                'collaborators' => [
                    ['name' => 'Ms. Lestari', 'role' => 'owner',  'color' => '#0ea5e9', 'online_now' => true,  'last_seen_at' => $now->copy()->subMinutes(5)],
                    ['name' => 'Mr. Reza',    'role' => 'editor', 'color' => '#dc2626', 'online_now' => false, 'last_seen_at' => $now->copy()->subHours(5)],
                ],
                'comments' => [
                    ['section_key' => 'lessons', 'author' => 'Mr. Reza', 'color' => '#dc2626', 'body' => 'Saya punya kontak petugas PDAM untuk sesi tamu minggu 3.', 'created_at' => $now->copy()->subHours(4)],
                ],
            ],

            [
                'plan' => [
                    'title'          => 'Bahasa Indonesia — Teks Deskripsi',
                    'grade'          => 'Grade 7',
                    'campus'         => 'smp',
                    'theme'          => 'Bahasa Indonesia',
                    'central_idea'   => 'Teks deskripsi membantu pembaca membayangkan objek, tempat, atau orang dengan jelas.',
                    'subjects'       => ['Bahasa Indonesia'],
                    'cover_emoji'    => '📝',
                    'cover_color'    => 'amber',
                    'status'         => 'draft',
                    'completion_pct' => 15,
                    'starts_on'      => $now->copy()->addDays(10)->toDateString(),
                    'ends_on'        => $now->copy()->addDays(38)->toDateString(),
                    'owner_name'     => 'Ms. Sari Wulan',
                    'owner_role'     => 'SMP — Bahasa Indonesia',
                    'last_activity_at' => $now->copy()->subDays(2),
                    'sections' => [
                        'overview'      => 'Mengenal struktur dan ciri kebahasaan teks deskripsi, lalu menulis teks deskripsi tentang tempat favorit.',
                        'objectives'    => [
                            'Mengidentifikasi struktur teks deskripsi',
                            'Menggunakan kata sifat dan majas perbandingan',
                            'Menulis teks deskripsi 200–300 kata',
                        ],
                        'topics'        => ['Struktur teks', 'Ciri kebahasaan', 'Kata sifat', 'Majas', 'Menulis draft'],
                        'lessons' => [
                            ['week' => 1, 'title' => 'Membaca contoh teks deskripsi', 'status' => 'planned'],
                            ['week' => 1, 'title' => 'Analisis struktur',             'status' => 'planned'],
                        ],
                    ],
                ],
                'collaborators' => [
                    ['name' => 'Ms. Sari Wulan', 'role' => 'owner', 'color' => '#f59e0b', 'online_now' => false, 'last_seen_at' => $now->copy()->subDays(2)],
                ],
                'comments' => [],
            ],

            [
                'plan' => [
                    'title'          => 'English — Cambridge Checkpoint Prep (Reading)',
                    'grade'          => 'Grade 9',
                    'campus'         => 'smp',
                    'theme'          => 'English',
                    'central_idea'   => 'Active reading strategies improve comprehension under timed conditions.',
                    'subjects'       => ['English'],
                    'cover_emoji'    => '📖',
                    'cover_color'    => 'indigo',
                    'status'         => 'active',
                    'completion_pct' => 70,
                    'starts_on'      => $now->copy()->subDays(28)->toDateString(),
                    'ends_on'        => $now->copy()->addDays(20)->toDateString(),
                    'owner_name'     => 'Mr. Bagus Pratama',
                    'owner_role'     => 'SMP — English',
                    'last_activity_at' => $now->copy()->subHours(6),
                    'sections' => [
                        'overview'      => 'Targeted Cambridge Lower Secondary Checkpoint reading prep: skimming, scanning, inference, and vocabulary in context.',
                        'objectives'    => [
                            'Apply skimming & scanning to locate key information',
                            'Infer meaning from context',
                            'Answer short and extended response questions accurately',
                        ],
                        'topics'        => ['Skim & scan', 'Inference', 'Vocabulary in context', 'Short answers', 'Extended responses'],
                        'assessment'    => 'Weekly past-paper reading section under timed conditions + diagnostic at week 4.',
                        'resources'     => ['Cambridge Checkpoint past papers (2019–2024)', 'Class vocabulary tracker (Google Sheet)'],
                        'lessons' => [
                            ['week' => 1, 'title' => 'Skim & scan techniques',     'status' => 'done'],
                            ['week' => 2, 'title' => 'Inference workshop',         'status' => 'done'],
                            ['week' => 3, 'title' => 'Vocabulary in context',      'status' => 'done'],
                            ['week' => 4, 'title' => 'Timed past paper + review',  'status' => 'in_progress'],
                            ['week' => 5, 'title' => 'Extended response strategy', 'status' => 'planned'],
                        ],
                    ],
                ],
                'collaborators' => [
                    ['name' => 'Mr. Bagus Pratama', 'role' => 'owner',  'color' => '#4338ca', 'online_now' => false, 'last_seen_at' => $now->copy()->subHours(6)],
                    ['name' => 'Ms. Rina',          'role' => 'editor', 'color' => '#f97316', 'online_now' => true,  'last_seen_at' => $now->copy()->subMinutes(7)],
                ],
                'comments' => [],
            ],

            [
                'plan' => [
                    'title'          => 'IPS — Keragaman Budaya Indonesia',
                    'grade'          => 'Grade 8',
                    'campus'         => 'smp',
                    'theme'          => 'IPS',
                    'central_idea'   => 'Indonesia kaya akan keragaman suku, bahasa, dan budaya yang membentuk identitas bangsa.',
                    'subjects'       => ['IPS', 'PPKn'],
                    'cover_emoji'    => '🇮🇩',
                    'cover_color'    => 'emerald',
                    'status'         => 'completed',
                    'completion_pct' => 100,
                    'starts_on'      => $now->copy()->subDays(60)->toDateString(),
                    'ends_on'        => $now->copy()->subDays(12)->toDateString(),
                    'owner_name'     => 'Ms. Putri Anggraini',
                    'owner_role'     => 'SMP Coordinator',
                    'last_activity_at' => $now->copy()->subDays(10),
                    'sections' => [
                        'overview'   => 'Eksplorasi keragaman budaya nusantara dan nilai persatuan dalam keberagaman (Bhinneka Tunggal Ika).',
                        'objectives' => [
                            'Menyebutkan contoh keragaman budaya tiap pulau besar',
                            'Menjelaskan makna Bhinneka Tunggal Ika',
                            'Menghargai perbedaan dalam kehidupan sehari-hari',
                        ],
                        'lessons' => [
                            ['week' => 1, 'title' => 'Peta budaya nusantara', 'status' => 'done'],
                            ['week' => 2, 'title' => 'Riset suku kelompok',   'status' => 'done'],
                            ['week' => 3, 'title' => 'Pameran budaya kelas',  'status' => 'done'],
                        ],
                    ],
                ],
                'collaborators' => [
                    ['name' => 'Ms. Putri Anggraini', 'role' => 'owner', 'color' => '#10b981', 'online_now' => false, 'last_seen_at' => $now->copy()->subDays(10)],
                ],
                'comments' => [],
            ],
        ];

        foreach ($units as $row) {
            $plan = UnitPlan::create(array_merge($row['plan'], [
                'sections' => $row['plan']['sections'] ?? null,
            ]));

            foreach ($row['collaborators'] as $c) {
                UnitPlanCollaborator::create([
                    'unit_plan_id' => $plan->id,
                    'name'         => $c['name'],
                    'role'         => $c['role'],
                    'color'        => $c['color'],
                    'initials'     => collect(explode(' ', preg_replace('/^(Mr\.|Ms\.|Mrs\.|Drs\.)\s*/u', '', $c['name'])))
                        ->map(fn ($p) => mb_substr($p, 0, 1))
                        ->take(2)
                        ->implode(''),
                    'online_now'   => $c['online_now'] ?? false,
                    'last_seen_at' => $c['last_seen_at'] ?? null,
                ]);
            }

            foreach ($row['comments'] as $cm) {
                UnitPlanComment::create([
                    'unit_plan_id'     => $plan->id,
                    'section_key'      => $cm['section_key'] ?? null,
                    'author_name'      => $cm['author'],
                    'author_initials'  => collect(explode(' ', preg_replace('/^(Mr\.|Ms\.|Mrs\.|Drs\.)\s*/u', '', $cm['author'])))
                        ->map(fn ($p) => mb_substr($p, 0, 1))
                        ->take(2)
                        ->implode(''),
                    'author_color'     => $cm['color'],
                    'body'             => $cm['body'],
                    'created_at'       => $cm['created_at'] ?? $now,
                    'updated_at'       => $cm['created_at'] ?? $now,
                ]);
            }
        }
    }
}
