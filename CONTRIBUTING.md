# Kontribusi

Ambil satu item dari `docs/TODO.md`, kerjakan di branch sendiri, lalu buka pull request ke `main`. Jangan push langsung ke `main`.

## Branch

```
feature/<kode>-<deskripsi-singkat>   feature/T3-notifikasi-jatuh-tempo
fix/<deskripsi-singkat>              fix/plat-nomor-huruf-kecil
chore/<deskripsi-singkat>            chore/update-dependensi
docs/<deskripsi-singkat>             docs/screenshot-readme
```

Tulis nama kamu di item TODO yang diambil (edit `docs/TODO.md` di PR pertamamu) supaya tidak dobel kerja.

## Commit

Satu commit satu perubahan, format Conventional Commits, kalimat perintah, maksimal sekitar 72 karakter.

```
feat: tambah countdown batas bayar di halaman penyewa
fix: tolak tanggal selesai sebelum tanggal mulai
test: cek bentrok untuk slot nonaktif
docs: perbarui daftar akun contoh
```

Saran urutan commit untuk push pertama ke GitHub:

```
chore: init project laravel 12
feat: migrasi garasi, slot, booking, surat, catatan khusus
feat: cek bentrok dan harga booking
feat: halaman cari garasi dan pengajuan sewa
feat: panel pemilik (ringkasan, pengajuan, surat)
test: bentrok, filter, upload bukti bayar, hak akses
docs: readme, skema database, catatan belajar
```

## Sebelum push

```
git status
git diff
php artisan test
vendor/bin/pint
```

Pastikan `.env` dan file di `storage/app` tidak ikut ter-commit.

## Pull request

Isi template yang muncul otomatis: apa dan mengapa, daftar perubahan, cara tes, screenshot kalau mengubah tampilan. Minta minimal satu anggota lain untuk review.

## Konflik

```
git fetch origin
git rebase origin/main
# selesaikan file yang konflik, lalu
git add <file>
git rebase --continue
php artisan test
```

Jangan `git push --force` ke branch yang dipakai bersama.

## Aturan kode

- Ikuti PSR-12 dan konvensi Laravel. Controller tipis, aturan bisnis di service.
- Validasi di Form Request, otorisasi di Policy.
- Hindari N+1 (`with()`), jangan query mentah dari input user.
- Fitur baru yang menyentuh aturan penting (bentrok, status, harga) wajib ada test.
- Komentar hanya untuk menjelaskan "kenapa".
