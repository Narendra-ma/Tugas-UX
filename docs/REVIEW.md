# Review kode

Review mandiri memakai checklist keamanan, kebenaran, performa, dan kualitas. Kode ini dibuat dengan bantuan AI, jadi wajib dibaca, dijalankan, dan dites sendiri sebelum dianggap benar.

## Kondisi saat ini

Kode lolos `php -l` (cek sintaks), tetapi test dan aplikasi belum pernah dijalankan: lingkungan tempat kode ini disusun tidak bisa menjalankan `composer install`. Jalankan `php artisan test` dan klik semua alur di browser sebelum push. Kalau ada yang gagal, itu bahan latihan debugging yang bagus.

## Kritis

Tidak ada temuan yang diketahui. Tapi lihat bagian "Kondisi saat ini" di atas.

## Penting

1. **Pembatasan login.** `POST /masuk` sudah dibatasi `throttle:10,1`. Pendaftaran belum dibatasi, jadi bisa dibanjiri akun palsu. Perbaikan: tambah `throttle` di `POST /daftar`, atau captcha.
2. **Tidak ada lupa kata sandi dan verifikasi email.** Laporan UX mencatat "lupa kata sandi" sebagai pain point Ibu Surya. Sekarang tidak ada cara pulih. Lihat TODO T7.
3. **Cek bentrok hanya di level aplikasi.** Aman selama semua booking lewat `BookingService::create()`. Jangan buat booking lewat jalur lain tanpa memanggil service itu. Lihat `docs/DATABASE.md`.
4. **Pencocokan catatan khusus lemah.** Plat dan nomor HP dicocokkan persis. "DK 1234 AB" dan "DK1234AB" dianggap beda. Plat sudah di-uppercase dan spasi dirapikan di `StoreBookingRequest`, tapi spasi di tengah belum dihapus. Penyewa juga bisa ganti nomor HP.
5. **Berkas pribadi (KTP, surat) belum dienkripsi di storage.** Laporan UX meminta storage-level encryption. Sekarang file hanya di disk private dan diakses lewat policy. Lihat TODO T9.
6. **Pengingat jatuh tempo baru berupa daftar di dashboard.** Belum ada notifikasi ke pemilik atau penyewa. Lihat TODO T3.

## Saran

- `Booking::STATUS_LABELS`, `PERIODS`, dan kategori dokumen ada di model. Kalau mau multi-bahasa, pindahkan ke `lang/`.
- Dashboard menjalankan sekitar 8 query. Cukup untuk sekarang, tapi bisa digabung atau di-cache kalau lambat.
- Pencarian surat memakai `LIKE '%q%'` (tidak memakai index), aman untuk data kecil.
- Hak akses sudah ada di tes, tapi belum ada tes untuk validasi `StoreBookingRequest` (tanggal lampau, checkbox persetujuan).
- `role` sengaja tidak masuk `$fillable` supaya tidak bisa diisi lewat form. Akun pemilik dibuat lewat seeder atau `tinker`.

## Yang sudah baik

- Controller tipis, aturan booking terkumpul di satu service sehingga mudah dites.
- Upload bukti bayar: validasi tipe dan ukuran di Form Request, nama file diacak, disimpan di disk private, diakses lewat controller dengan policy. Tipe file dicek dari isi file, bukan hanya ekstensi.
- Pemilik hanya bisa mengelola garasi dan booking miliknya (Policy), diuji di `OwnerFlowTest`.
- Perpindahan status dijaga (`must()`), jadi tombol yang ditekan dua kali tidak merusak data.
