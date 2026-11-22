<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Garage;
use App\Models\Slot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GarageListTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_lokasi_dan_harga(): void
    {
        Garage::factory()->create(['name' => 'Murah Surabaya', 'city' => 'Surabaya', 'price_monthly' => 400000]);
        Garage::factory()->create(['name' => 'Mahal Surabaya', 'city' => 'Surabaya', 'price_monthly' => 900000]);
        Garage::factory()->create(['name' => 'Murah Bali', 'city' => 'Karangasem', 'price_monthly' => 400000]);

        $this->get('/?city=Surabaya&max_price=500000')
            ->assertOk()
            ->assertSee('Murah Surabaya')
            ->assertDontSee('Mahal Surabaya')
            ->assertDontSee('Murah Bali');
    }

    public function test_filter_ketersediaan_menyembunyikan_garasi_yang_penuh(): void
    {
        $penuh = Garage::factory()->create(['name' => 'Garasi Penuh']);
        $slot = Slot::factory()->create(['garage_id' => $penuh->id]);
        Booking::factory()->status('dibayar')->between('2026-12-01', '2026-12-31')->create(['slot_id' => $slot->id]);

        $kosong = Garage::factory()->create(['name' => 'Garasi Kosong']);
        Slot::factory()->create(['garage_id' => $kosong->id]);

        $this->get('/?start_date=2026-12-10&end_date=2026-12-15')
            ->assertOk()
            ->assertSee('Garasi Kosong')
            ->assertDontSee('Garasi Penuh');
    }

    public function test_garasi_nonaktif_tidak_tampil(): void
    {
        Garage::factory()->create(['name' => 'Sudah Tutup', 'is_active' => false]);

        $this->get('/')->assertOk()->assertDontSee('Sudah Tutup');
    }
}
