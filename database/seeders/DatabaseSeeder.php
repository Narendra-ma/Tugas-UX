<?php

namespace Database\Seeders;

use App\Models\Blacklist;
use App\Models\Booking;
use App\Models\Garage;
use App\Models\User;
use Illuminate\Database\Seeder;

// Data contoh berdasarkan wawancara Tugas 1 UX (kelompok 10): hanya Garasi Surya. Tarif hanya perkiraan.
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $surya = User::factory()->owner()->create([
            'name' => 'Ibu Surya',
            'email' => 'surya@garasi.test',
            'phone' => '081234500001',
        ]);

        $garasi = Garage::factory()->for($surya, 'owner')->create([
            'name' => 'Garasi Surya',
            'city' => 'Karangasem',
            'address' => 'Dusun Bangbang, Desa Rendang, Kec. Rendang, Kab. Karangasem, Bali',
            'description' => 'Garasi pribadi yang dikelola langsung oleh pemilik. Bersih dan nyaman.',
            'size_note' => 'Muat 6 mobil',
            'price_daily' => 30000,
            'price_weekly' => 150000,
            'price_monthly' => 500000,
        ]);
        foreach (['A1', 'A2', 'A3', 'A4', 'A5', 'A6'] as $code) {
            $garasi->slots()->create(['code' => $code]);
        }

        $dimas = User::factory()->create(['name' => 'Dimas', 'email' => 'dimas@example.test', 'phone' => '081234500003']);
        $budi = User::factory()->create(['name' => 'Budi', 'email' => 'budi@example.test', 'phone' => '081234500004']);
        $rudi = User::factory()->create(['name' => 'Rudi', 'email' => 'rudi@example.test', 'phone' => '081234500005']);
        $slots = $garasi->slots()->orderBy('code')->get()->keyBy('code');

        // sewa aktif
        Booking::factory()->status('dibayar')->between(today()->subDays(10)->toDateString(), today()->addDays(20)->toDateString())
            ->create(['user_id' => $dimas->id, 'slot_id' => $slots['A1']->id, 'plate' => 'DK 1111 AA', 'paid_at' => now()->subDays(10), 'expires_at' => null]);

        // sudah lewat jatuh tempo
        Booking::factory()->status('dibayar')->between(today()->subDays(35)->toDateString(), today()->subDays(5)->toDateString())
            ->create(['user_id' => $budi->id, 'slot_id' => $slots['A2']->id, 'plate' => 'DK 2222 BB', 'paid_at' => now()->subDays(35), 'expires_at' => null]);

        // pengajuan baru yang belum dijawab
        Booking::factory()->between(today()->addDays(2)->toDateString(), today()->addDays(32)->toDateString())
            ->create(['user_id' => $rudi->id, 'slot_id' => $slots['A3']->id, 'plate' => 'DK 3333 CC']);

        // catatan khusus dari kasus penyewa yang marah dan pergi tanpa melunasi
        $kabur = User::factory()->create(['name' => 'Penyewa Contoh Bermasalah', 'email' => 'kabur@example.test', 'phone' => '081234500009']);
        Blacklist::forceCreate([
            'owner_id' => $surya->id,
            'name' => $kabur->name,
            'phone' => $kabur->phone,
            'plate' => 'DK 9999 ZZ',
            'reason' => 'Marah saat diingatkan jatuh tempo dan keluar tanpa melunasi',
            'arrears_amount' => 500000,
        ]);
    }
}
