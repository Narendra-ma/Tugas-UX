<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Garage;
use App\Models\Slot;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingConflictTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $service;

    private Slot $slot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BookingService();
        $this->slot = Slot::factory()->create();
    }

    private function book(string $start, string $end, string $status = 'dibayar'): Booking
    {
        return Booking::factory()->status($status)->between($start, $end)->create(['slot_id' => $this->slot->id]);
    }

    public function test_tanggal_yang_tumpang_tindih_dianggap_bentrok(): void
    {
        $this->book('2026-12-10', '2026-12-20');

        $this->assertFalse($this->service->isFree($this->slot, '2026-12-15', '2026-12-25')); // overlap ujung
        $this->assertFalse($this->service->isFree($this->slot, '2026-12-01', '2026-12-10')); // kena hari pertama
        $this->assertFalse($this->service->isFree($this->slot, '2026-12-12', '2026-12-14')); // di dalam
        $this->assertFalse($this->service->isFree($this->slot, '2026-12-01', '2026-12-31')); // membungkus
    }

    public function test_tanggal_berdampingan_tidak_bentrok(): void
    {
        $this->book('2026-12-10', '2026-12-20');

        $this->assertTrue($this->service->isFree($this->slot, '2026-12-21', '2026-12-30'));
        $this->assertTrue($this->service->isFree($this->slot, '2026-12-01', '2026-12-09'));
    }

    public function test_booking_batal_ditolak_selesai_diputus_tidak_menahan_slot(): void
    {
        foreach (['batal', 'ditolak', 'selesai', 'diputus'] as $status) {
            $this->book('2026-12-10', '2026-12-20', $status);
        }

        $this->assertTrue($this->service->isFree($this->slot, '2026-12-10', '2026-12-20'));
    }

    public function test_pengajuan_yang_masih_menunggu_tetap_menahan_slot(): void
    {
        $this->book('2026-12-10', '2026-12-20', 'menunggu_konfirmasi');
        $this->book('2027-01-10', '2027-01-20', 'menunggu_bayar');

        $this->assertFalse($this->service->isFree($this->slot, '2026-12-15', '2026-12-16'));
        $this->assertFalse($this->service->isFree($this->slot, '2027-01-15', '2027-01-16'));
    }

    public function test_slot_lain_di_tanggal_sama_tidak_bentrok(): void
    {
        $this->book('2026-12-10', '2026-12-20');
        $other = Slot::factory()->create(['garage_id' => $this->slot->garage_id]);

        $this->assertTrue($this->service->isFree($other, '2026-12-10', '2026-12-20'));
    }

    public function test_create_menolak_booking_kedua_di_slot_dan_tanggal_sama(): void
    {
        $data = [
            'car_type' => 'Brio', 'plate' => 'L 1 AB', 'period' => 'daily',
            'start_date' => today()->addDays(5)->toDateString(),
            'end_date' => today()->addDays(8)->toDateString(),
        ];

        $this->service->create(User::factory()->create(), $this->slot, $data);

        $this->expectException(ValidationException::class);
        $this->service->create(User::factory()->create(), $this->slot, $data);
    }

    public function test_slot_nonaktif_tidak_bisa_dipesan(): void
    {
        $this->slot->update(['is_active' => false]);

        $this->expectException(ValidationException::class);
        $this->service->create(User::factory()->create(), $this->slot, [
            'car_type' => 'Brio', 'plate' => 'L 1 AB', 'period' => 'daily',
            'start_date' => today()->addDay()->toDateString(),
            'end_date' => today()->addDays(2)->toDateString(),
        ]);
    }

    public function test_total_harga_dihitung_per_periode(): void
    {
        $garage = Garage::factory()->create(['price_daily' => 30000, 'price_weekly' => 150000, 'price_monthly' => 500000]);

        $this->assertSame(90000, $this->service->total($garage, 'daily', '2026-12-01', '2026-12-03'));    // 3 hari
        $this->assertSame(300000, $this->service->total($garage, 'weekly', '2026-12-01', '2026-12-10'));  // 10 hari = 2 minggu
        $this->assertSame(1000000, $this->service->total($garage, 'monthly', '2026-12-01', '2027-01-04')); // 35 hari = 2 bulan
    }

    public function test_pengajuan_lewat_batas_waktu_dibatalkan_otomatis(): void
    {
        $lewat = $this->book('2026-12-10', '2026-12-20', 'menunggu_konfirmasi');
        $lewat->update(['expires_at' => now()->subMinute()]);
        $masihAda = $this->book('2027-02-01', '2027-02-10', 'menunggu_bayar');

        $this->assertSame(1, $this->service->expireStale());
        $this->assertSame('batal', $lewat->fresh()->status);
        $this->assertSame('menunggu_bayar', $masihAda->fresh()->status);
    }
}
