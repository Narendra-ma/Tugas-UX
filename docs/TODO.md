# Daftar tugas tim

Ambil satu, tulis namamu di kolom "Pengambil", kerjakan di branch `feature/<kode>-...`. Sumbernya adalah bagian Key Requirements dan Opportunities di laporan UX.

| Kode | Tugas | Dari laporan | Tingkat | Pengambil |
|---|---|---|---|---|
| T1 | Countdown batas bayar / batas respon di halaman penyewa (sekarang masih teks tanggal) | Skenario 4 | mudah | |
| T2 | Panduan langkah demi langkah (onboarding / tooltip) untuk pemilik yang baru pertama masuk | Owner #13, Sistem #7 | sedang | |
| T3 | Notifikasi jatuh tempo ke pemilik dan penyewa (email dulu, WhatsApp belakangan) | Sistem #2 | sedang | |
| T4 | Foto garasi lebih dari satu + video singkat | Skenario 1 | sedang | |
| T5 | Ulasan penyewa untuk garasi | Skenario 1 | sedang | |
| T6 | Laporan pendapatan bulanan bisa diekspor (CSV / PDF) | Skenario 3 | sedang | |
| T7 | Lupa kata sandi dan verifikasi email | Pain point Ibu Surya | sedang | |
| T8 | Riwayat tunggakan per penyewa (halaman detail penyewa) | Owner #9, #10 | sedang | |
| T9 | Enkripsi berkas surat di storage | NFR #11 | sulit | |
| T10 | Audit aksesibilitas WCAG 2.1 AA (kontras 4.5:1, target sentuh 44px) dan perbaiki | NFR #12 | sedang | |
| T11 | Peran admin terpisah dari pemilik | Catatan Key Requirements | sulit | |
| T12 | Diskon sewa bulanan / tahunan | Skenario 1 | mudah | |
| T13 | Pembayaran digital (e-wallet / transfer otomatis) | NFR #10, wawancara | sulit | |
| T14 | Tes untuk `StoreBookingRequest` dan `GarageRequest` | | mudah | |
| T15 | Screenshot dan demo di README | | mudah | |
| T16 | Deploy (hosting) dan setup cron untuk `schedule:run` | | sedang | |
| T17 | Pasang GitHub Actions untuk `php artisan test` dan Pint | | mudah | |

## Status terhadap Key Requirements

Sudah: pemilik #1-12, penyewa #1-7, sistem #1, #3, #5, #6, mode terang/gelap, tampilan responsif, teks besar, kata sandi ter-hash.

Sebagian: sistem #2 (hanya daftar di dashboard, belum notifikasi), sistem #4 (konfirmasi ada untuk tolak, putus sepihak, dan ubah slot, tetapi belum ada fitur hapus), pemilik #10 (riwayat penyewa belum punya halaman sendiri).

Belum: pemilik #13 dan sistem #7 (panduan, T2), NFR enkripsi storage (T9), audit WCAG (T10), pengukuran waktu muat < 3 detik, cadangan otomatis.

## Catatan data contoh

Tarif Garasi Surya (Rp 30.000 / hari, 150.000 / minggu, 500.000 / bulan) adalah perkiraan, bukan data dari wawancara. Wawancara hanya menyebut kisaran wajar Rp 300.000 - 750.000 per bulan. Tanyakan ke Ibu Surya atau ganti sesuai kesepakatan kelompok.
