# Catatan belajar

Supaya kamu bisa menjelaskan setiap bagian saat ditanya (wawancara magang atau dosen), bukan hanya menyalin. Baca kodenya sambil membaca catatan ini.

## 1. Cek bentrok tanggal

Bayangkan dua orang memesan kamar hotel. Pesanan lama tanggal 10-20. Pesanan baru bentrok kalau ia mulai sebelum yang lama berakhir, dan berakhir setelah yang lama mulai:

```
start_lama <= end_baru  AND  end_lama >= start_baru
```

Coba sendiri dengan kertas: lama 10-20, baru 20-25 (bentrok, hari ke-20 dipakai dua orang), baru 21-25 (aman).
Kode: `Booking::scopeOverlapping`. Tes: `BookingConflictTest`.

## 2. Kenapa perlu transaksi dan lock

Cek lalu simpan bukan satu langkah. Kalau dua orang menekan "kirim" bersamaan, keduanya bisa lolos cek sebelum salah satu tersimpan. `DB::transaction` + `lockForUpdate()` membuat request kedua menunggu sampai yang pertama selesai, jadi cek yang kedua melihat data terbaru.
Kode: `BookingService::create`.

## 3. N+1

Menampilkan 10 garasi lalu memanggil `$garage->slots->count()` di tiap kartu = 1 query daftar + 10 query slot. `withCount` dan `with` menggabungkannya. Buka Laravel Debugbar atau `DB::listen` dan hitung query di halaman beranda.

## 4. Form Request, Policy, Service

- Form Request: "apakah data dari form ini valid?"
- Policy: "apakah user ini boleh menyentuh data itu?"
- Service: "apa aturan bisnisnya?" (bentrok, harga, status)
- Controller: menyambungkan ketiganya, tanpa logika.

## 5. Upload yang aman

Jangan percaya nama atau ekstensi file dari user. Tiga lapis di sini: validasi tipe (dari isi file) dan ukuran, nama file diacak saat disimpan, dan file disimpan di disk private sehingga hanya bisa dibuka lewat route yang mengecek hak akses.

## Cek pemahaman

1. Pesanan A: 1-10 Des. Pesanan B: 10-15 Des di slot yang sama. Bentrok atau tidak? Mengapa? Ubah kodenya kalau kamu ingin hari terakhir dianggap "check-out" (boleh dipakai orang lain di hari yang sama).
2. Apa yang terjadi kalau `lockForUpdate()` dihapus dan dua request datang bersamaan? Bagaimana cara mensimulasikannya?
3. Mengapa `role` tidak masuk `$fillable` di `User`?
4. Mengapa slot dinonaktifkan, tidak dihapus?
5. Penyewa A menebak URL `/berkas/bukti/5` milik penyewa B. Bagian kode mana yang menghentikannya?

## Tugas latihan

- Tambah test untuk `StoreBookingRequest`: tanggal lampau ditolak, checkbox persetujuan wajib.
- Ubah aturan harga mingguan supaya sisa hari dihitung harian, bukan dibulatkan ke atas. Tes mana yang harus kamu ubah?

## Bacaan di dokumentasi resmi Laravel

Eloquent: Relationships (eager loading), Validation (form requests), Authorization (policies), Database: Transactions, Queries (pessimistic locking), File Storage, Task Scheduling, Testing.
