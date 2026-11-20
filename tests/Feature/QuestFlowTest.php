<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Certificate;
use App\Models\Quest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestFlowTest extends TestCase
{
    use RefreshDatabase;

    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $building = Building::create(['code' => 'C', 'name' => 'Gedung C']);
        $this->room = Room::create(['building_id' => $building->id, 'code' => 'C-302', 'name' => 'Lab Komputer 302', 'floor' => 3]);
    }

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->image('foto.jpg', 800, 600);
    }

    private function scanAndReport(User $user, string $category = 'ac'): Quest
    {
        $this->actingAs($user)->get(route('room.show', $this->room->qr_token))->assertOk();
        $this->post(route('quest.store', $this->room->qr_token), [
            'category' => $category,
            'description' => 'AC tidak menyala sejak pagi',
            'photo' => $this->photo(),
        ])->assertRedirect();

        return Quest::latest('id')->first();
    }

    public function test_login_redirects_each_role_to_its_home(): void
    {
        $student = User::factory()->create(['identifier' => '2210001', 'password' => 'rahasia123']);
        $this->post(route('login.attempt'), ['identifier' => '2210001', 'password' => 'salah'])
            ->assertSessionHasErrors('password');
        $this->post(route('login.attempt'), ['identifier' => '2210001', 'password' => 'rahasia123'])
            ->assertRedirect(route('scan'));
        $this->post(route('logout'))->assertRedirect(route('login'));

        User::factory()->technician()->create(['identifier' => 'TKN001', 'password' => 'rahasia123']);
        $this->post(route('login.attempt'), ['identifier' => 'TKN001', 'password' => 'rahasia123'])
            ->assertRedirect(route('technician.queue'));
    }

    public function test_reporting_requires_a_recent_scan_of_the_room_qr(): void
    {
        $student = User::factory()->create();
        $this->actingAs($student)->get(route('quest.create', $this->room->qr_token))->assertRedirect(route('scan'));
        $this->get(route('room.show', 'token-palsu'))->assertNotFound();
    }

    public function test_new_quest_gives_xp_and_lowers_room_hp(): void
    {
        $student = User::factory()->create();
        $quest = $this->scanAndReport($student);

        $this->assertMatchesRegularExpression('/^KQ-\d{4}-\d{4}$/', $quest->number);
        $this->assertSame(5, $student->fresh()->xp);
        $this->assertSame(80, $this->room->fresh()->hp());
        Storage::disk('local')->assertExists($quest->photo_before);
        $this->get(route('quest.show', $quest))->assertOk()->assertSee('Quest terbit');
    }

    public function test_photo_is_required(): void
    {
        $student = User::factory()->create();
        $this->actingAs($student)->get(route('room.show', $this->room->qr_token));
        $this->post(route('quest.store', $this->room->qr_token), ['category' => 'ac', 'description' => 'Rusak'])
            ->assertSessionHasErrors(['photo' => 'Ambil foto kerusakan dulu.']);
    }

    public function test_duplicate_report_is_sent_to_join_party_instead(): void
    {
        $quest = $this->scanAndReport(User::factory()->create());
        $other = User::factory()->create();
        $this->actingAs($other)->get(route('room.show', $this->room->qr_token));
        $this->post(route('quest.store', $this->room->qr_token), ['category' => 'ac', 'description' => 'AC mati', 'photo' => $this->photo()])
            ->assertRedirect(route('quest.join', $quest));
        $this->assertSame(1, Quest::count());

        // "Masalahnya berbeda" tetap boleh membuat laporan baru.
        $this->post(route('quest.store', $this->room->qr_token), ['category' => 'ac', 'description' => 'AC unit belakang bocor', 'photo' => $this->photo(), 'force_new' => 'ac'])
            ->assertRedirect();
        $this->assertSame(2, Quest::count());
    }

    public function test_ten_confirmations_within_an_hour_make_a_raid_boss(): void
    {
        $quest = $this->scanAndReport(User::factory()->create());
        $party = User::factory()->count(10)->create();

        foreach ($party->take(9) as $member) {
            $this->actingAs($member)->post(route('quest.confirm', $quest))->assertRedirect(route('quest.join', $quest));
        }
        $this->assertFalse($quest->fresh()->is_raid_boss);

        $this->actingAs($party->last())->post(route('quest.confirm', $quest))->assertSessionHas('status');
        $quest->refresh();
        $this->assertTrue($quest->is_raid_boss);
        $this->assertNotNull($quest->raid_boss_at);
        $this->assertSame(20, $this->room->fresh()->hp());

        // Konfirmasi ganda tidak menambah party.
        $this->actingAs($party->last())->post(route('quest.confirm', $quest));
        $this->assertSame(10, $quest->confirmations()->count());
    }

    public function test_reporter_cannot_confirm_own_quest(): void
    {
        $reporter = User::factory()->create();
        $quest = $this->scanAndReport($reporter);
        $this->actingAs($reporter)->post(route('quest.confirm', $quest))->assertSessionHas('error');
        $this->assertSame(0, $quest->confirmations()->count());
    }

    public function test_technician_resolves_with_after_photo_and_xp_is_paid(): void
    {
        $reporter = User::factory()->create();
        $quest = $this->scanAndReport($reporter);
        $member = User::factory()->create();
        $this->actingAs($member)->post(route('quest.confirm', $quest));

        $tech = User::factory()->technician()->create();
        $this->actingAs($tech)->get(route('technician.queue'))->assertOk()->assertSee($quest->number);

        // Belum mulai: tidak bisa selesai.
        $this->post(route('technician.resolve', $quest), ['photo_after' => $this->photo()])->assertSessionHas('error');

        $this->post(route('technician.start', $quest))->assertRedirect(route('technician.ticket', $quest));
        $this->post(route('technician.resolve', $quest), [])->assertSessionHasErrors('photo_after');
        $this->post(route('technician.resolve', $quest), ['photo_after' => $this->photo(), 'technician_note' => 'Kapasitor diganti'])
            ->assertSessionHas('resolved', 2);

        $quest->refresh();
        $this->assertSame(Quest::RESOLVED, $quest->status);
        $this->assertSame(100, $this->room->fresh()->hp());
        $this->assertSame(50, $reporter->fresh()->xp);
        $this->assertSame(15, $member->fresh()->xp);
        $this->get(route('technician.ticket', $quest))->assertOk();
    }

    public function test_admin_rejects_spam_with_penalty(): void
    {
        $reporter = User::factory()->create();
        $quest = $this->scanAndReport($reporter);
        $tech = User::factory()->technician()->create();
        $this->actingAs($tech)->post(route('technician.flag', $quest), ['reason' => 'Ditandai teknisi: kerusakan tidak ditemukan']);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.moderation'))->assertOk()->assertSee($quest->number);
        $this->post(route('admin.moderation.reject', $quest), [])->assertSessionHasErrors('reason', null, 'reject');
        $this->post(route('admin.moderation.reject', $quest), ['reason' => 'Kerusakan tidak ditemukan teknisi'])->assertRedirect(route('admin.moderation'));

        $this->assertSame(Quest::REJECTED, $quest->fresh()->status);
        $this->assertSame(-15, $reporter->fresh()->xp);
        $this->assertSame(100, $this->room->fresh()->hp());
    }

    public function test_certificate_claim_pdf_and_public_verification(): void
    {
        $student = User::factory()->create(['name' => 'Mahasiswa Uji']);
        app(\App\Services\XpService::class)->award($student, 1500, 'seed_balance', 'Saldo uji');

        $this->actingAs($student)->post(route('certificate.claim', 'utama'))->assertSessionHas('error');
        $this->post(route('certificate.claim', 'madya'))->assertSessionHas('status');
        $certificate = Certificate::first();
        $this->assertMatchesRegularExpression('/^KQ-M-\d{4}-\d{6}$/', $certificate->serial);

        $pdf = $this->get(route('certificate.pdf', $certificate));
        $pdf->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $this->post(route('logout'));
        $this->get(route('verify.show', strtolower($certificate->serial)))->assertOk()->assertSee('SERTIFIKAT SAH')->assertSee('Mahasiswa Uji');
        $this->get(route('verify.show', 'KQ-M-2026-999999'))->assertOk()->assertSee('TIDAK TERDAFTAR');
        $this->get(route('verify', ['seri' => $certificate->serial]))->assertRedirect(route('verify.show', $certificate->serial));
    }

    public function test_other_students_cannot_download_someone_elses_certificate(): void
    {
        $owner = User::factory()->create();
        app(\App\Services\XpService::class)->award($owner, 1500, 'seed_balance', 'Saldo uji');
        $certificate = app(\App\Services\CertificateService::class)->claim($owner, 'madya');
        $this->actingAs(User::factory()->create())->get(route('certificate.pdf', $certificate))->assertForbidden();
    }

    public function test_roles_are_kept_apart(): void
    {
        $student = User::factory()->create();
        $this->actingAs($student)->get(route('admin.stats'))->assertForbidden();
        $this->actingAs($student)->get(route('technician.queue'))->assertForbidden();
        $this->actingAs(User::factory()->technician()->create())->get(route('scan'))->assertForbidden();
        auth()->logout();
        $this->get(route('profile'))->assertRedirect(route('login'));
    }

    public function test_leaderboard_unlocks_at_tier_one(): void
    {
        $student = User::factory()->create();
        $this->actingAs($student)->get(route('leaderboard'))->assertOk()->assertSee("Papan peringkat terbuka di 500\u{00A0}XP");
        app(\App\Services\XpService::class)->award($student, 500, 'seed_balance', 'Saldo uji');
        $this->get(route('leaderboard'))->assertOk()->assertSee('Kamu');
    }

    public function test_admin_manages_rooms_and_prints_qr(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.rooms'))->assertOk()->assertSee('C-302');
        $this->post(route('admin.rooms.store'), ['building_id' => $this->room->building_id, 'code' => 'c-302', 'name' => 'Duplikat', 'floor' => 3])
            ->assertSessionHasErrors('code', null, 'room');
        $this->post(route('admin.rooms.store'), ['building_id' => $this->room->building_id, 'code' => 'c-303', 'name' => 'Lab 303', 'floor' => 3])->assertRedirect();
        $this->assertDatabaseHas('rooms', ['code' => 'C-303']);

        $oldToken = $this->room->qr_token;
        $this->put(route('admin.rooms.update', $this->room), ['building_id' => $this->room->building_id, 'code' => 'C-302', 'name' => 'Lab Komputer 302', 'floor' => 3, 'reset_token' => '1'])->assertRedirect();
        $this->assertNotSame($oldToken, $this->room->fresh()->qr_token);

        $this->post(route('admin.rooms.print'), ['rooms' => [$this->room->id]])->assertOk()->assertSee('<svg', false);
        $this->assertNotNull($this->room->fresh()->qr_printed_at);
        $this->get(route('admin.stats'))->assertOk();
    }

    public function test_photos_can_live_in_the_database_for_hosts_without_a_disk(): void
    {
        config(['kampusquest.photo_store' => 'database']);
        $student = User::factory()->create();
        $quest = $this->scanAndReport($student);

        Storage::disk('local')->assertMissing($quest->photo_before);
        $this->assertDatabaseHas('photos', ['path' => $quest->photo_before, 'mime' => 'image/jpeg']);
        $this->get(route('photo', [$quest, 'before']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
