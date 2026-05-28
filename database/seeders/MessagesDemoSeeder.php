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
        \App\Models\ThreadParticipant::query()->delete();
        ThreadMessage::query()->delete();
        MessageThread::query()->delete();

        $today = Carbon::today();
        $now   = Carbon::now();

        // ---------- Thread 1: Budi Pratiwi — parent, recent ----------
        $t1a = $now->copy()->subMinutes(50);
        $t1b = $now->copy()->subMinutes(35);
        $t1c = $now->copy()->subMinutes(20);
        $t1 = MessageThread::create([
            'subject'           => "Dian's absences",
            'category'          => 'parent',
            'partner_name'      => 'Budi Pratiwi',
            'partner_role'      => 'Parent · Dian Pratiwi (4A)',
            'partner_initials'  => 'BP',
            'partner_color'     => 'emerald',
            'last_message_at'   => $t1c,
            'unread_count'      => 1,
        ]);
        $this->seedMessages($t1, [
            [false, 'Budi Pratiwi', 'BP', $t1a, 'Good morning Ms. Sarah, I wanted to ask about Dian. She has been absent for four days — how was she at school before that?'],
            [true,  'Sarah Rahayu', 'SR', $t1b, 'Good morning Mr. Budi. She was present and engaged all of last week. Is there a health concern we should know about?'],
            [false, 'Budi Pratiwi', 'BP', $t1c, 'She has a high fever. I am taking her to the doctor today. Thank you for checking in 🙏'],
        ]);

        // ---------- Thread 2: Andi Laksono — staff, today ----------
        $t2a = $now->copy()->subHours(2);
        $t2 = MessageThread::create([
            'subject'           => 'Duty assignment review',
            'category'          => 'staff',
            'partner_name'      => 'Andi Laksono',
            'partner_role'      => 'Vice Principal',
            'partner_initials'  => 'AL',
            'partner_color'     => 'blue',
            'last_message_at'   => $t2a,
            'unread_count'      => 1,
        ]);
        $this->seedMessages($t2, [
            [false, 'Andi Laksono', 'AL', $t2a, 'Please review the duty assignment for next week and confirm by end of day.'],
        ]);

        // ---------- Thread 3: Rina Permata — parent, past week ----------
        $t3 = MessageThread::create([
            'subject'           => 'Thank you note',
            'category'          => 'parent',
            'partner_name'      => 'Rina Permata',
            'partner_role'      => 'Parent · Fajar Hidayat (4A)',
            'partner_initials'  => 'RP',
            'partner_color'     => 'rose',
            'last_message_at'   => $today->copy()->subDays(2)->setTime(14, 5),
            'unread_count'      => 0,
        ]);
        $this->seedMessages($t3, [
            [true,  'Sarah Rahayu', 'SR', $today->copy()->subDays(2)->setTime(13, 50), 'Hi Ms. Rina, Fajar made great progress this week. He scored 95 on the science quiz.'],
            [false, 'Rina Permata', 'RP', $today->copy()->subDays(2)->setTime(14, 5),  'Thank you so much for letting me know 🙏'],
        ]);

        // ---------- Thread 4: HR Admin — staff, past week ----------
        $t4 = MessageThread::create([
            'subject'           => 'Contract renewal',
            'category'          => 'staff',
            'partner_name'      => 'HR Admin',
            'partner_role'      => 'Staff',
            'partner_initials'  => 'HR',
            'partner_color'     => 'violet',
            'last_message_at'   => $today->copy()->subDays(3)->setTime(11, 20),
            'unread_count'      => 0,
        ]);
        $this->seedMessages($t4, [
            [false, 'HR Admin', 'HR', $today->copy()->subDays(3)->setTime(11, 20), 'Your contract renewal documents are ready for review and signature.'],
        ]);

        // ---------- Thread 5: Dewi Susanti — parent, last week ----------
        $t5 = MessageThread::create([
            'subject'           => 'Math quiz grade',
            'category'          => 'parent',
            'partner_name'      => 'Dewi Susanti',
            'partner_role'      => 'Parent · Yusuf Prakoso (4B)',
            'partner_initials'  => 'DS',
            'partner_color'     => 'amber',
            'last_message_at'   => $today->copy()->subDays(5)->setTime(16, 30),
            'unread_count'      => 0,
        ]);
        $this->seedMessages($t5, [
            [false, 'Dewi Susanti', 'DS', $today->copy()->subDays(5)->setTime(16, 30), 'Hi Ms. Sarah, may I ask about Yusuf\'s math quiz score from last week?'],
        ]);

        // ---------- Thread 6: Indah Lestari — staff, today ----------
        $t7a = $now->copy()->subMinutes(95);
        $t7b = $now->copy()->subMinutes(82);
        $t7 = MessageThread::create([
            'subject'           => 'Field trip permission slips',
            'category'          => 'staff',
            'partner_name'      => 'Indah Lestari',
            'partner_role'      => 'Math Teacher · 4A',
            'partner_initials'  => 'IL',
            'partner_color'     => 'emerald',
            'last_message_at'   => $t7b,
            'unread_count'      => 0,
        ]);
        $this->seedMessages($t7, [
            [false, 'Indah Lestari', 'IL', $t7a, 'I collected 22 of 28 permission slips. Should I send a reminder to the remaining parents?'],
            [true,  'Sarah Rahayu',  'SR', $t7b, 'Yes please — send a friendly reminder and CC me. Deadline is Friday.'],
        ]);

        // ---------- Thread 7: 4A Parent Group (group chat + @mentions) ----------
        $g0 = $now->copy()->subMinutes(45);
        $g1 = $now->copy()->subMinutes(22);
        $g2 = $now->copy()->subMinutes(10);
        $t6 = MessageThread::create([
            'subject'           => '4A Parent Group',
            'category'          => 'parent',
            'is_group'          => true,
            'group_name'        => '4A Parent Group',
            'partner_name'      => '4A Parent Group',
            'partner_role'      => '4 members',
            'partner_initials'  => '4A',
            'partner_color'     => 'violet',
            'last_message_at'   => $g2,
            'unread_count'      => 2,
        ]);
        $this->seedMessages($t6, [
            [true,  'Sarah Rahayu',  'SR', $g0, 'Good morning everyone, @everyone please confirm your attendance for the parent meeting this Saturday at 10 AM.'],
            [false, 'Budi Pratiwi',  'BP', $g1, 'I will be there, thank you @Sarah Rahayu.'],
            [false, 'Rina Permata',  'RP', $g2, 'Confirmed — see you Saturday 🙏'],
        ]);

        // ---------- Thread 8: Leadership Team (staff group) ----------
        $l0 = $now->copy()->subHours(5);
        $l1 = $now->copy()->subHours(4)->subMinutes(30);
        $l2 = $now->copy()->subHours(3);
        $t8 = MessageThread::create([
            'subject'           => 'Leadership Team',
            'category'          => 'staff',
            'is_group'          => true,
            'group_name'        => 'Leadership Team',
            'partner_name'      => 'Leadership Team',
            'partner_role'      => '4 members',
            'partner_initials'  => 'LT',
            'partner_color'     => 'blue',
            'last_message_at'   => $l2,
            'unread_count'      => 1,
        ]);
        $this->seedMessages($t8, [
            [true,  'Sarah Rahayu',  'SR', $l0, '@everyone reminder: weekly leadership stand-up tomorrow at 8 AM in the principal\'s office.'],
            [false, 'Andi Laksono',  'AL', $l1, 'Noted. I will bring the enrollment dashboard summary.'],
            [false, 'Maya Sari',     'MS', $l2, 'I will share the HR pipeline update. @Sarah Rahayu do we cover the new uniform policy as well?'],
        ]);

        // ---------- Participants ----------
        $this->seedParticipants($t6, [
            ['Sarah Rahayu',  'SR', 'slate',   'Principal',                       true],
            ['Budi Pratiwi',  'BP', 'emerald', 'Parent · Dian Pratiwi (4A)',      false],
            ['Rina Permata',  'RP', 'rose',    'Parent · Fajar Hidayat (4A)',     false],
            ['Hendra Wijaya', 'HW', 'blue',    'Parent · Lestari Wijaya (5A)',    false],
        ]);
        $this->seedParticipants($t8, [
            ['Sarah Rahayu',  'SR', 'slate',   'Principal',      true],
            ['Andi Laksono',  'AL', 'blue',    'Vice Principal', false],
            ['Maya Sari',     'MS', 'blue',    'Vice Principal', false],
            ['Indah Lestari', 'IL', 'emerald', 'Math Teacher',   false],
        ]);
        $this->seedParticipants($t1, [
            ['Sarah Rahayu', 'SR', 'slate',   'Principal',                  true],
            ['Budi Pratiwi', 'BP', 'emerald', 'Parent · Dian Pratiwi (4A)', false],
        ]);
        $this->seedParticipants($t2, [
            ['Sarah Rahayu', 'SR', 'slate', 'Principal',      true],
            ['Andi Laksono', 'AL', 'blue',  'Vice Principal', false],
        ]);
        $this->seedParticipants($t7, [
            ['Sarah Rahayu',  'SR', 'slate',   'Principal',          true],
            ['Indah Lestari', 'IL', 'emerald', 'Math Teacher · 4A',  false],
        ]);

        $this->command?->info('Messages demo: ' . MessageThread::count() . ' threads seeded (' . ThreadMessage::count() . ' messages).');
    }

    private function seedParticipants(MessageThread $thread, array $rows): void
    {
        foreach ($rows as [$name, $initials, $color, $role, $isMe]) {
            \App\Models\ThreadParticipant::create([
                'thread_id' => $thread->id,
                'name'      => $name,
                'initials'  => $initials,
                'color'     => $color,
                'role'      => $role,
                'is_me'     => $isMe,
            ]);
        }
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
