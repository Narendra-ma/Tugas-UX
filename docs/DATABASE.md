# Catatan skema database (MySQL / InnoDB)

## Tabel

| Tabel | Isi | Kunci penting |
|---|---|---|
| users | akun, `role` (owner / tenant), `phone` | unique `email` |
| garages | data garasi dan tarif harian/mingguan/bulanan | FK `owner_id`, index `(is_active, city)`, index `price_monthly` |
| slots | satu baris per tempat parkir (A1, A2, ...) | FK `garage_id`, unique `(garage_id, code)` |
| bookings | pengajuan dan sewa | FK `user_id`, `slot_id`, index `(slot_id, start_date, end_date)`, index `(status, expires_at)`, index `plate` |
| documents | surat perjanjian, bukti bayar, identitas | FK `booking_id`, index `(booking_id, category)` |
| blacklists | catatan khusus penyewa bermasalah per pemilik | FK `owner_id`, index `(owner_id, plate)`, `(owner_id, phone)` |

Relasi: owner 1-N garages, garage 1-N slots, slot 1-N bookings, user (penyewa) 1-N bookings, booking 1-N documents.

Kenapa ada tabel `slots`: laporan UX menyebut garasi Ibu Surya muat 6 mobil dan status yang ditampilkan per slot. Kalau cuma `garages`, kita tidak bisa tahu slot mana yang kosong.

## Mencegah dua booking di slot dan tanggal yang sama

Dua rentang bentrok kalau `start_lama <= end_baru AND end_lama >= start_baru`. Hari pertama dan terakhir ikut dihitung. Hanya status `menunggu_konfirmasi`, `menunggu_bayar`, dan `dibayar` yang menahan slot (konstanta `Booking::BLOCKING`).

Cek saja tidak cukup, karena dua request bisa lolos cek bersamaan. Di `BookingService::create()` urutannya:

1. buka transaksi
2. `lockForUpdate()` pada baris slot, request kedua menunggu sampai yang pertama commit
3. cek bentrok
4. simpan booking

MySQL tidak punya exclusion constraint seperti PostgreSQL (`EXCLUDE USING gist`), jadi penjaganya di level aplikasi. Kalau nanti pindah ke PostgreSQL, constraint itu bisa jadi lapisan pengaman kedua.

## Index dan alasannya

| Query | Index yang dipakai |
|---|---|
| cek bentrok (`slot_id`, lalu rentang tanggal) | `bookings(slot_id, start_date, end_date)` |
| auto-cancel (`status IN (...) AND expires_at < now`) | `bookings(status, expires_at)` |
| filter kota di halaman cari | `garages(is_active, city)` |
| filter harga bulanan | `garages(price_monthly)` |
| cari surat per plat | `bookings(plate)` |

Catatan: `city LIKE '%kata%'` dengan wildcard di depan tidak bisa memakai index. Untuk puluhan garasi tidak masalah. Kalau data besar, pakai `LIKE 'kata%'`, kolom terpisah, atau FULLTEXT.

## Hindari N+1

- Daftar garasi: jumlah slot dihitung dengan `withCount`, bukan `$garage->slots->count()` di view.
- Detail garasi: slot dan booking yang bentrok di-load sekali (`with` bertingkat), lalu `Slot::displayStatus()` hanya membaca relasi yang sudah ada.
- Pengajuan pemilik: `with(['user', 'slot.garage'])`. Daftar plat/HP catatan khusus diambil satu kali (`flagsFor`), bukan query per baris.

## Cara memeriksa performa

Nyalakan query log atau pakai `DB::listen`, lalu jalankan query yang dicurigai dengan:

```sql
EXPLAIN SELECT * FROM bookings
WHERE slot_id = 1 AND start_date <= '2026-12-31' AND end_date >= '2026-12-01'
  AND status IN ('menunggu_konfirmasi','menunggu_bayar','dibayar');
```

Yang dicari: kolom `key` harus berisi `bookings_slot_id_start_date_end_date_index`, dan `type` bukan `ALL`. Di seeder datanya terlalu sedikit sehingga MySQL kadang tetap memilih full scan. Tes lagi setelah ada ribuan baris.

## Hal yang belum dibereskan

- `total_price`, `arrears_amount`, dan tarif disimpan sebagai integer rupiah. Aman untuk rupiah, tapi kalau ada diskon persentase perlu aturan pembulatan.
- Pagination memakai offset. Cukup untuk data kecil, ganti ke `cursorPaginate()` kalau tabel sudah besar.
- Foto garasi baru satu (`photo_path`). Kalau perlu banyak foto atau video, buat tabel `garage_photos`.
- Tidak ada soft delete. Slot dinonaktifkan, bukan dihapus, supaya riwayat booking tetap utuh.
