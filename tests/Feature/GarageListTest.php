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

    public function test_landing_menampilkan_garasi_surya_dan_slotnya(): void
    {
        $garage = Garage::factory()->create(['name' => 'Garasi Surya']);
        Slot::factory()->create(['garage_id' => $garage->id, 'code' => 'A1']);

        $this->get('/')->assertOk()->assertSee('Garasi Surya')->assertSee('A1');
    }

    public function test_slot_terisi_ditandai_di_tanggal_yang_dicek(): void
    {
        $garage = Garage::factory()->create();
        $slot = Slot::factory()->create(['garage_id' => $garage->id, 'code' => 'A1']);
        Booking::factory()->status('dibayar')->between('2026-12-01', '2026-12-31')->create(['slot_id' => $slot->id]);

        $this->get('/?start_date=2026-12-10&end_date=2026-12-15')->assertOk()->assertSee('Terisi');
        $this->get('/?start_date=2027-02-01&end_date=2027-02-05')->assertOk()->assertSee('Tersedia');
    }

    public function test_garasi_nonaktif_tidak_tampil(): void
    {
        Garage::factory()->create(['is_active' => false]);

        $this->get('/')->assertNotFound();
    }
}
