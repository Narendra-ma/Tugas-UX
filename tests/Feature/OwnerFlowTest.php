<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_penyewa_tidak_bisa_buka_halaman_pemilik(): void
    {
        $this->actingAs(User::factory()->create())->get('/pemilik')->assertForbidden();
    }

    public function test_pemilik_tidak_bisa_mengelola_booking_garasi_orang_lain(): void
    {
        $booking = Booking::factory()->create();
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)->post(route('owner.bookings.accept', $booking))->assertForbidden();
    }

    public function test_terima_lalu_bayar_membuat_sewa_aktif(): void
    {
        $booking = Booking::factory()->create();
        $owner = $booking->slot->garage->owner;

        $this->actingAs($owner)->post(route('owner.bookings.accept', $booking));
        $this->assertSame('menunggu_bayar', $booking->fresh()->status);

        $this->actingAs($owner)->post(route('owner.bookings.pay', $booking));
        $this->assertSame('dibayar', $booking->fresh()->status);
    }

    public function test_putus_sepihak_mencatat_blacklist(): void
    {
        $booking = Booking::factory()->status('dibayar')->create(['plate' => 'DK 9 ZZ']);
        $owner = $booking->slot->garage->owner;

        $this->actingAs($owner)->post(route('owner.bookings.terminate', $booking), [
            'reason' => 'Pergi tanpa melunasi',
            'arrears_amount' => 500000,
        ])->assertSessionHasNoErrors();

        $this->assertSame('diputus', $booking->fresh()->status);
        $this->assertDatabaseHas('blacklists', ['owner_id' => $owner->id, 'plate' => 'DK 9 ZZ', 'arrears_amount' => 500000]);
    }

    public function test_penyewa_bisa_batal_hanya_sebelum_dikonfirmasi(): void
    {
        $booking = Booking::factory()->status('menunggu_bayar')->create();

        $this->actingAs($booking->user)->post(route('bookings.cancel', $booking))->assertStatus(409);
        $this->assertSame('menunggu_bayar', $booking->fresh()->status);
    }
}
