<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Quest;
use App\Models\QuestConfirmation;
use App\Models\Room;
use App\Models\User;
use App\Services\XpService;
use App\Support\Categories;
use App\Support\PhotoStore;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Data contoh untuk mencoba semua layar. Semua nama dan angka di sini adalah
 * contoh: nama akun memakai pola "Mahasiswa Demo 01" dan foto berupa gambar
 * bertuliskan "Foto contoh", supaya tidak tertukar dengan data asli.
 */
class DatabaseSeeder extends Seeder
{
    private XpService $xp;

    /** @var array<int, User> */
    private array $students = [];

    public function run(XpService $xp): void
    {
        $this->xp = $xp;
        $password = 'kampusquest';

        $admin = User::create(['name' => 'Admin Demo', 'identifier' => 'ADM001', 'role' => User::ADMIN, 'password' => $password]);
        $tech = User::create(['name' => 'Teknisi Demo', 'identifier' => 'TKN001', 'role' => User::TEKNISI, 'password' => $password]);

        for ($i = 1; $i <= 16; $i++) {
            $this->students[$i] = User::create([
                'name' => sprintf('Mahasiswa Demo %02d', $i),
                'identifier' => sprintf('2210%03d', $i),
                'role' => User::MAHASISWA,
                'faculty' => $i <= 14 ? 'Fakultas Teknik' : 'Fakultas Ekonomi',
                'password' => $password,
            ]);
        }

        $rooms = $this->rooms();
        $s = $this->students;

        // Raid Boss: AC Lab Komputer 302, 12 konfirmasi dalam 48 menit.
        $raid = $this->quest($rooms['C-302'], $s[3], 'ac', 'AC tidak menyala sejak pagi, ruangan panas', now()->subMinutes(132));
        $this->party($raid, [1, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14], now()->subMinutes(126), 4);
        $raid->forceFill(['is_raid_boss' => true, 'raid_boss_at' => now()->subMinutes(84)])->save();

        // Antrean normal.
        $lamp = $this->quest($rooms['A-105'], $s[1], 'listrik', 'Dua lampu depan mati', now()->subMinutes(190));
        $this->party($lamp, [2, 3, 4, 5, 6], now()->subMinutes(170), 9);
        $wifi = $this->quest($rooms['P-201'], $s[5], 'internet', 'Wi-Fi tidak terhubung di area baca', now()->subMinutes(85));
        $this->party($wifi, [6, 7, 8, 9], now()->subMinutes(80), 6);
        $chair = $this->quest($rooms['B-210'], $s[7], 'furnitur', 'Kursi baris ketiga patah', now()->subMinutes(302));
        $this->party($chair, [8, 9], now()->subMinutes(250), 30);
        $tap = $this->quest($rooms['B-T01'], $s[1], 'air', 'Kran wastafel bocor terus', now()->subMinutes(160));
        $this->party($tap, [10, 11], now()->subMinutes(140), 20);
        $proj = $this->quest($rooms['C-104'], $s[9], 'proyektor', 'Proyektor berkedip saat dipakai', now()->subMinutes(22));
        $this->party($proj, [10], now()->subMinutes(15), 1);

        // Sedang dikerjakan.
        $door = $this->quest($rooms['A-301'], $s[4], 'pintu', 'Pintu tidak bisa dikunci', now()->subMinutes(240));
        $this->party($door, [1, 2, 3], now()->subMinutes(230), 15);
        $door->forceFill(['status' => Quest::IN_PROGRESS, 'technician_id' => $tech->id, 'started_at' => now()->subMinutes(35)])->save();

        // Ditandai untuk moderasi.
        $flag1 = $this->quest($rooms['C-210'], $s[6], 'furnitur', 'Kursi patah di baris belakang', now()->subHours(5));
        $flag1->forceFill(['flagged_reason' => 'Ditandai teknisi: kerusakan tidak ditemukan', 'flagged_at' => now()->subHours(2), 'technician_note' => 'Dicek pukul '.now()->subHours(2)->format('H.i').', semua kursi dalam kondisi baik.'])->save();
        $flag2 = $this->quest($rooms['B-105'], $s[15], 'lainnya', 'Ada yang aneh di ruangan', now()->subHours(3));
        $flag2->forceFill(['flagged_reason' => 'Deskripsi tidak berhubungan dengan fasilitas', 'flagged_at' => now()->subHours(3)])->save();

        // Riwayat selesai, tersebar di 30 hari terakhir supaya statistik terisi.
        $history = [
            ['C-302', 1, 'proyektor', 'Proyektor tidak menampilkan gambar', 6, 192],
            ['C-302', 2, 'listrik', 'Lampu depan berkedip', 18, 105],
            ['A-105', 1, 'ac', 'AC bocor menetes ke meja', 3, 260],
            ['A-301', 3, 'internet', 'Colokan LAN tidak berfungsi', 9, 75],
            ['B-210', 1, 'furnitur', 'Meja dosen goyang', 12, 410],
            ['B-T01', 5, 'air', 'Toilet tidak bisa disiram', 2, 55],
            ['P-201', 1, 'listrik', 'Stopkontak meja baca mati', 20, 300],
            ['C-104', 6, 'pintu', 'Engsel jendela lepas', 25, 1500],
            ['C-210', 1, 'ac', 'AC berbunyi keras', 14, 140],
            ['B-105', 2, 'proyektor', 'Kabel HDMI putus', 4, 95],
        ];
        foreach ($history as $i => [$code, $reporter, $cat, $desc, $daysAgo, $minutes]) {
            $created = now()->subDays($daysAgo)->setTime(9, 10 + $i);
            $q = $this->quest($rooms[$code], $s[$reporter], $cat, $desc, $created);
            $this->party($q, [($reporter % 14) + 1, (($reporter + 1) % 14) + 1], $created->copy()->addMinutes(10), 5);
            $q->forceFill([
                'status' => Quest::RESOLVED,
                'technician_id' => $tech->id,
                'started_at' => $created->copy()->addMinutes(intdiv($minutes, 2)),
                'resolved_at' => $created->copy()->addMinutes($minutes),
                'photo_after' => $this->photo($q, 'after'),
                'technician_note' => 'Perbaikan contoh dari seeder.',
            ])->save();
            $this->xp->award($q->reporter, 45, 'discoverer_bonus', "Quest {$q->number} selesai (First Discoverer)", $q);
            $q->confirmations()->with('user')->get()->each(fn ($c) => $this->xp->award($c->user, 15, 'party', "Party {$q->number} selesai", $q));
            \App\Models\XpTransaction::where('quest_id', $q->id)->whereIn('type', ['discoverer_bonus', 'party'])->update(['created_at' => $q->resolved_at, 'updated_at' => $q->resolved_at]);
        }
        // Dua Raid Boss lama untuk grafik mingguan.
        Quest::where('status', Quest::RESOLVED)->orderBy('id')->take(2)->get()
            ->each(fn (Quest $q) => $q->forceFill(['is_raid_boss' => true, 'raid_boss_at' => $q->created_at->copy()->addMinutes(30)])->save());

        // Satu laporan ditolak milik Mahasiswa Demo 01.
        $rejected = $this->quest($rooms['C-210'], $s[1], 'furnitur', 'Kursi rusak', now()->subDays(8));
        $rejected->forceFill(['status' => Quest::REJECTED, 'reject_reason' => 'Foto tidak sesuai ruangan', 'moderation_note' => 'Foto tidak menunjukkan ruangan C-210.', 'moderated_at' => now()->subDays(8)->addHours(3), 'moderator_id' => $admin->id])->save();
        $this->xp->award($s[1], -20, 'rejected_penalty', "Laporan {$rejected->number} ditolak admin", $rejected);

        // Saldo awal supaya tiap tier bisa dicoba: Demo 01 di Tier 1, Demo 02 bisa klaim sertifikat Madya, Demo 16 belum masuk peringkat.
        $balances = [1 => 1240, 2 => 1560, 3 => 980, 4 => 760, 5 => 640, 6 => 610, 7 => 590, 8 => 560, 9 => 520, 10 => 505, 11 => 470, 12 => 430, 13 => 380, 14 => 300, 15 => 520, 16 => 320];
        foreach ($balances as $i => $target) {
            $student = $s[$i]->fresh();
            $gap = $target - $student->xp;
            if ($gap !== 0) {
                $this->xp->award($student, $gap, 'seed_balance', 'Saldo XP data contoh');
            }
        }
    }

    /** @return array<string, Room> */
    private function rooms(): array
    {
        $data = [
            'A' => ['Gedung A', [['A-105', 'Ruang Kuliah A-105', 1], ['A-201', 'Ruang Kuliah A-201', 2], ['A-301', 'Ruang Dosen A-301', 3]]],
            'B' => ['Gedung B', [['B-105', 'Ruang Kuliah B-105', 1], ['B-210', 'Ruang Kuliah B-210', 2], ['B-T01', 'Toilet Gedung B Lt 1', 1]]],
            'C' => ['Gedung C', [['C-104', 'Ruang Kuliah C-104', 1], ['C-210', 'Ruang Kuliah C-210', 2], ['C-301', 'Lab Jaringan 301', 3], ['C-302', 'Lab Komputer 302', 3]]],
            'P' => ['Perpustakaan', [['P-101', 'Ruang Sirkulasi', 1], ['P-201', 'Perpustakaan Lt 2', 2]]],
        ];
        $rooms = [];
        foreach ($data as $code => [$name, $list]) {
            $building = Building::create(['code' => $code, 'name' => $name]);
            foreach ($list as [$roomCode, $roomName, $floor]) {
                $room = Room::create(['building_id' => $building->id, 'code' => $roomCode, 'name' => $roomName, 'floor' => $floor]);
                if ($roomCode !== 'C-301') {
                    $room->forceFill(['qr_printed_at' => Carbon::parse('2026-11-03 10:00')])->save();
                }
                $rooms[$roomCode] = $room;
            }
        }

        return $rooms;
    }

    private function quest(Room $room, User $reporter, string $category, string $description, Carbon $at): Quest
    {
        $quest = Quest::create([
            'room_id' => $room->id,
            'reporter_id' => $reporter->id,
            'category' => $category,
            'description' => $description,
            'photo_before' => 'pending',
        ]);
        $quest->forceFill([
            'number' => sprintf('KQ-%s-%04d', $at->format('ym'), $quest->id),
            'created_at' => $at,
            'updated_at' => $at,
        ])->save();
        $quest->forceFill(['photo_before' => $this->photo($quest, 'before')])->save();
        $this->xp->award($reporter, 5, 'quest_created', "Quest {$quest->number} terbit", $quest);
        \App\Models\XpTransaction::where('quest_id', $quest->id)->update(['created_at' => $at, 'updated_at' => $at]);

        return $quest;
    }

    private function party(Quest $quest, array $studentIds, Carbon $start, int $stepMinutes): void
    {
        foreach (array_values($studentIds) as $n => $id) {
            if ($this->students[$id]->id === $quest->reporter_id) {
                continue;
            }
            $c = QuestConfirmation::create(['quest_id' => $quest->id, 'user_id' => $this->students[$id]->id]);
            $c->forceFill(['created_at' => $start->copy()->addMinutes($n * $stepMinutes)])->save();
        }
    }

    /** Gambar pengganti foto bertuliskan "Foto contoh", bukan foto asli. */
    private function photo(Quest $quest, string $which): string
    {
        $label = $which === 'after' ? 'Foto contoh: sesudah perbaikan' : 'Foto contoh: '.Categories::short($quest->category);
        $bg = $which === 'after' ? '#E3EEEA' : '#E4E1D8';
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
  <rect width="800" height="600" fill="{$bg}"/>
  <rect x="24" y="24" width="752" height="552" fill="none" stroke="#8A8F86" stroke-width="2" stroke-dasharray="12 10"/>
  <text x="400" y="290" text-anchor="middle" font-family="sans-serif" font-size="34" fill="#4F5A54">{$label}</text>
  <text x="400" y="340" text-anchor="middle" font-family="monospace" font-size="24" fill="#4F5A54">{$quest->number}</text>
</svg>
SVG;
        $path = "quests/demo-{$quest->id}-{$which}.svg";
        PhotoStore::put($path, $svg, 'image/svg+xml');

        return $path;
    }
}
