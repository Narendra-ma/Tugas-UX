# Garasi Surya

Website sewa garasi mobil khusus Garasi Surya milik Ibu Surya (Laravel 12 + Bootstrap 5 + MySQL). Dibuat untuk Tugas 1 User Experience, kelompok 10, dengan data dari wawancara 8 narasumber. Persona utamanya Ibu Surya, pemilik garasi 6 slot di Karangasem, Bali, yang sekarang masih mengurus surat dan pembayaran secara manual.

Status: prototipe untuk belajar dan portofolio, belum untuk produksi. Lihat `docs/TODO.md` untuk yang belum dikerjakan.

## Fitur yang sudah ada

Penyewa
- Daftar, masuk, lihat landing page Garasi Surya (tarif, status 6 slot, cek ketersediaan per tanggal).
- Lihat status slot (tersedia / menunggu / terisi) lalu ajukan sewa harian, mingguan, atau bulanan.
- Pantau status pengajuan, batalkan sebelum dikonfirmasi, unggah bukti bayar.

Pemilik (peran admin digabung ke pemilik, sesuai laporan UX)
- Ringkasan: sewa aktif, slot kosong, pendapatan bulan ini, daftar terlambat dan segera jatuh tempo.
- Terima atau tolak pengajuan (alasan penolakan memakai template sopan), verifikasi bayar, tandai selesai.
- Akhiri sewa sepihak dengan alasan dan tunggakan. Penyewa masuk catatan khusus dan pengajuan berikutnya dari plat atau nomor HP yang sama diberi tanda merah.
- Kelola garasi, tarif, dan slot (slot dinonaktifkan, tidak dihapus).
- Simpan dan cari surat perjanjian berdasarkan nama, plat, slot, atau kategori.

Sistem
- Satu slot tidak bisa dipesan dua kali di tanggal yang bertumpuk (cek bentrok di dalam transaksi + lock).
- Pengajuan batal otomatis kalau pemilik tidak menjawab atau penyewa tidak bayar dalam 24 jam.

## Menjalankan di lokal

Butuh PHP 8.2+, Composer, MySQL.

```
composer install
cp .env.example .env
php artisan key:generate
```

Database bisa disiapkan lewat SQL atau migrasi.

Lewat SQL: `mysql -u root -p < database/garasi_surya.sql`, set `QUEUE_CONNECTION=sync` di `.env`, lalu `php artisan storage:link` dan `php artisan serve`.

Lewat migrasi: buat database `garasi_surya`, sesuaikan `DB_*` di `.env`, lalu:

```
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Pembatalan otomatis jalan lewat scheduler. Di lokal: `php artisan schedule:work`. Di server, pasang cron `* * * * * php /path/artisan schedule:run`.

Bootstrap dimuat dari CDN, jadi tidak perlu `npm install`.

## Akun contoh (dari seeder)

| Peran | Email | Kata sandi |
|---|---|---|
| Pemilik (Ibu Surya) | surya@garasi.test | password |
| Penyewa (Dimas) | dimas@example.test | password |

Ganti semuanya sebelum dipublikasikan.

## Tes

```
php artisan test
```

Tes memakai SQLite in-memory, jadi ekstensi `pdo_sqlite` harus aktif. Yang dites: cek bentrok booking, hitung harga, auto-cancel, filter garasi, upload bukti bayar, dan hak akses pemilik.

## Struktur singkat

- `app/Services/BookingService.php` semua aturan booking (bentrok, harga, perpindahan status).
- `app/Http/Controllers/Owner/` halaman pemilik. `app/Http/Controllers/` halaman penyewa.
- `app/Http/Requests/` validasi form.
- `app/Policies/` siapa boleh melihat atau mengelola apa.
- `docs/` catatan skema DB, hasil review, catatan belajar, dan daftar tugas tim.

## Kontribusi

Baca `CONTRIBUTING.md` (alur branch, format commit, pembagian tugas).
