<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProofUploadTest extends TestCase
{
    use RefreshDatabase;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->booking = Booking::factory()->status('menunggu_bayar')->create();
    }

    private function upload(User $user, UploadedFile $file)
    {
        return $this->actingAs($user)->post(route('bookings.proof', $this->booking), ['proof' => $file]);
    }

    public function test_penyewa_bisa_unggah_gambar(): void
    {
        $this->upload($this->booking->user, UploadedFile::fake()->image('bukti.png'))->assertSessionHasNoErrors();

        $path = $this->booking->fresh()->payment_proof_path;
        $this->assertNotNull($path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_tipe_file_berbahaya_ditolak(): void
    {
        $this->upload($this->booking->user, UploadedFile::fake()->create('shell.php', 10, 'application/x-php'))
            ->assertSessionHasErrors('proof');

        $this->assertNull($this->booking->fresh()->payment_proof_path);
    }

    public function test_file_lebih_dari_2mb_ditolak(): void
    {
        $this->upload($this->booking->user, UploadedFile::fake()->create('besar.pdf', 3000, 'application/pdf'))
            ->assertSessionHasErrors('proof');
    }

    public function test_penyewa_lain_tidak_boleh_unggah_atau_melihat(): void
    {
        $orangLain = User::factory()->create();

        $this->upload($orangLain, UploadedFile::fake()->image('bukti.png'))->assertForbidden();

        $this->upload($this->booking->user, UploadedFile::fake()->image('bukti.png'));
        $this->actingAs($orangLain)->get(route('files.proof', $this->booking))->assertForbidden();
    }

    public function test_pemilik_garasi_bisa_melihat_bukti(): void
    {
        $this->upload($this->booking->user, UploadedFile::fake()->image('bukti.png'));
        $owner = $this->booking->slot->garage->owner;

        $this->actingAs($owner)->get(route('files.proof', $this->booking))->assertOk();
    }
}
