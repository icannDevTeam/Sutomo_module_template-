<?php

namespace Database\Seeders;

use App\Models\MessageThread;
use App\Models\ThreadMessage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo data for the Principal Messages page — mirrors the Sutomo UI mockup.
 * Idempotent: clears + reseeds the 5 demo threads each run.
 */
class MessagesDemoSeeder extends Seeder
{
    public function run(): void
    {
        ThreadMessage::query()->delete();
        MessageThread::query()->delete();

        $today = Carbon::today();
        $now   = Carbon::now();

        // Anchor today's threads to "minutes ago" so newly sent messages
        // always land at the bottom of the conversation in any demo run.
        $t1c   = $now->copy()->subMinutes(20); // most recent partner reply
        $t1b   = $now->copy()->subMinutes(35); // my reply
        $t1a   = $now->copy()->subMinutes(50); // opening message
        $t2a   = $now->copy()->subHours(2);

        // ---------- Thread 1: Budi Pratiwi (active in screenshot) ----------
        $t1 = MessageThread::create([
            'subject'           => "Regarding Dian's absences",
            'category'          => 'parent',
            'partner_name'      => 'Budi Pratiwi',
            'partner_role'      => 'Parent · Dian Pratiwi (4A)',
            'partner_initials'  => 'BP',
            'partner_color'     => 'emerald',
            'last_message_at'   => $t1c,
            'unread_count'      => 1,
        ]);
        $this->seedMessages($t1, [
            [false, 'Budi Pratiwi',  'BP', $t1a, 'Selamat pagi Bu Sarah, saya ingin menanyakan kondisi Dian. Sudah 4 hari tidak masuk, bagaimana kondisinya di sekolah sebelumnya?'],
            [true,  'Sarah Rahayu',  'SR', $t1b, 'Selamat pagi Pak Budi. Sebelumnya Dian hadir dan aktif. Apakah ada kondisi kesehatan yang perlu kami ketahui?'],
            [false, 'Budi Pratiwi',  'BP', $t1c, 'Dian sedang demam tinggi Bu. Saya rencana bawa ke dokter hari ini. Mohon doanya 🙏'],
        ]);

        // ---------- Thread 2: Andi Laksono (Principal) ----------
        $t2 = MessageThread::create([
            'subject'           => 'Duty assignment review',
            'category'          => 'staff',
            'partner_name'      => 'Andi Laksono',
            'partner_role'      => 'Principal',
            'partner_initials'  => 'AL',
            'partner_color'     => 'blue',
            'last_message_at'   => $t2a,
            'unread_count'      => 1,
        ]);
        $this->seedMessages($t2, [
            [false, 'Andi Laksono', 'AL', $t2a, 'Please review the duty assignment for next week and confirm by EOD.'],
        ]);

        // ---------- Thread 3: Rina Permata (Dec 3) ----------
        $t3 = MessageThread::create([
            'subject'           => 'Terima kasih',
            'category'          => 'parent',
            'partner_name'      => 'Rina Permata',
            'partner_role'      => 'Parent · Fajar Hidayat (4A)',
            'partner_initials'  => 'RP',
            'partner_color'     => 'rose',
            'last_message_at'   => $today->copy()->subDays(6)->setTime(14, 5),
            'unread_count'      => 0,
        ]);
        $this->seedMessages($t3, [
            [true,  'Sarah Rahayu', 'SR', $today->copy()->subDays(6)->setTime(13, 50), 'Halo Bu Rina, Fajar sangat berkembang minggu ini. Hasil ulangan IPA 95.'],
            [false, 'Rina Permata', 'RP', $today->copy()->subDays(6)->setTime(14, 5),  'Terima kasih Bu atas perhatiannya kepada Fajar 🙏'],
        ]);

        // ---------- Thread 4: HR Admin (Dec 2) ----------
        $t4 = MessageThread::create([
            'subject'           => 'Contract renewal',
            'category'          => 'staff',
            'partner_name'      => 'HR Admin',
            'partner_role'      => 'Staff',
            'partner_initials'  => 'HR',
            'partner_color'     => 'violet',
            'last_message_at'   => $today->copy()->subDays(7)->setTime(11, 20),
            'unread_count'      => 0,
        ]);
        $this->seedMessages($t4, [
            [false, 'HR Admin', 'HR', $today->copy()->subDays(7)->setTime(11, 20), 'Your contract renewal documents are ready for review and signature.'],
        ]);

        // ---------- Thread 5: Dewi Susanti (Dec 1) ----------
        $t5 = MessageThread::create([
            'subject'           => 'Pertanyaan nilai',
            'category'          => 'parent',
            'partner_name'      => 'Dewi Susanti',
            'partner_role'      => 'Parent · Yusuf Prakoso (4B)',
            'partner_initials'  => 'DS',
            'partner_color'     => 'amber',
            'last_message_at'   => $today->copy()->subDays(8)->setTime(16, 30),
            'unread_count'      => 0,
        ]);
        $this->seedMessages($t5, [
            [false, 'Dewi Susanti', 'DS', $today->copy()->subDays(8)->setTime(16, 30), 'Bu, boleh saya tanya mengenai nilai quiz Matematika Yusuf minggu lalu?'],
        ]);

        $this->command?->info('Messages demo: 5 threads seeded (' . ThreadMessage::count() . ' messages).');
    }

    private function seedMessages(MessageThread $thread, array $rows): void
    {
        foreach ($rows as [$isMe, $name, $initials, $sentAt, $body]) {
            ThreadMessage::create([
                'thread_id'       => $thread->id,
                'is_me'           => $isMe,
                'sender_name'     => $name,
                'sender_initials' => $initials,
                'body'            => $body,
                'sent_at'         => $sentAt,
            ]);
        }
    }
}
