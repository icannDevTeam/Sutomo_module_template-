<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        if (Announcement::query()->exists()) {
            return;
        }

        $now = Carbon::now();

        $rows = [
            [
                'title'           => 'Mid-Semester Parent–Teacher Conference · 28–30 May',
                'body'            => 'Block schedule has been published. Homeroom teachers will confirm 15-minute slots through the Parent Portal by Friday. Please ensure progress reports are uploaded 48 hours prior to each session.',
                'author_name'     => 'Pak Dwi',
                'author_role'     => 'Principal',
                'category'        => 'academic',
                'status'          => 'sent',
                'pinned'          => true,
                'audiences'       => ['all_parents', 'teachers'],
                'channels'        => ['app', 'email', 'whatsapp'],
                'sent_at'         => $now->copy()->subHours(3),
                'recipient_count' => 842,
                'read_count'      => 311,
            ],
            [
                'title'           => 'Sistem absensi akan dimatikan untuk pemeliharaan, Sabtu 22:00–23:30',
                'body'            => 'Jendela pemeliharaan singkat untuk peningkatan basis data. Petugas keamanan akan menggunakan formulir cadangan kertas selama periode tersebut.',
                'author_name'     => 'IT Operations',
                'author_role'     => 'Operations',
                'category'        => 'urgent',
                'status'          => 'sent',
                'pinned'          => false,
                'audiences'       => ['staff', 'teachers'],
                'channels'        => ['app', 'email'],
                'sent_at'         => $now->copy()->subHours(8),
                'recipient_count' => 96,
                'read_count'      => 71,
            ],
            [
                'title'           => 'Sutomo Cultural Night · Tickets Open Tomorrow',
                'body'            => 'The annual cultural night returns on 14 June with student showcases from SD, SMP, and SMA. Early-bird family tickets open at 08:00 in the Parent Portal.',
                'author_name'     => 'Ms. Andini Putri',
                'author_role'     => 'Student Affairs',
                'category'        => 'event',
                'status'          => 'sent',
                'pinned'          => false,
                'audiences'       => ['all_parents', 'staff'],
                'channels'        => ['app', 'whatsapp'],
                'sent_at'         => $now->copy()->subDay(),
                'recipient_count' => 920,
                'read_count'      => 612,
            ],
            [
                'title'           => 'Final reminder: SMA Grade 12 Mock UTBK forms due Friday',
                'body'            => 'Please submit signed consent forms via homeroom by 17:00 Friday. Late submissions will not be accepted into the May 28 mock exam slot.',
                'author_name'     => 'Mr. Bagus Pratama',
                'author_role'     => 'Academic Coordinator',
                'category'        => 'reminder',
                'status'          => 'sent',
                'pinned'          => false,
                'audiences'       => ['grade_12'],
                'channels'        => ['app', 'email'],
                'sent_at'         => $now->copy()->subDays(2),
                'recipient_count' => 184,
                'read_count'      => 151,
            ],
            [
                'title'           => 'New library hours start Monday',
                'body'            => 'The library will open 07:15 daily to accommodate quiet study before homeroom. Closing time remains 17:30 on weekdays.',
                'author_name'     => 'Mrs. Lestari',
                'author_role'     => 'Librarian',
                'category'        => 'general',
                'status'          => 'sent',
                'pinned'          => false,
                'audiences'       => ['all_parents', 'staff', 'teachers'],
                'channels'        => ['app'],
                'sent_at'         => $now->copy()->subDays(3),
                'recipient_count' => 1080,
                'read_count'      => 540,
            ],
            [
                'title'           => 'Scheduled: Foundation visit briefing · 02 June 09:00',
                'body'            => 'Coordinators please prepare unit dashboards. Briefing pack will be circulated 24h prior. Dress code: formal.',
                'author_name'     => 'Pak Dwi',
                'author_role'     => 'Principal',
                'category'        => 'academic',
                'status'          => 'scheduled',
                'pinned'          => false,
                'audiences'       => ['staff'],
                'channels'        => ['app', 'email'],
                'scheduled_at'    => $now->copy()->addDays(4),
                'sent_at'         => null,
                'recipient_count' => 42,
                'read_count'      => 0,
            ],
        ];

        foreach ($rows as $row) {
            Announcement::create($row);
        }
    }
}
