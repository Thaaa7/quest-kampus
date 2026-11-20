# Cek antislop (Delivery Gate)

Mode: DURING. Arah desain: kampus resmi, gamifikasi halus (pilihan pemilik), dial ENERGY 1, RHYTHM 2, MOTION 1.
Diperiksa 20 November 2026 dengan headless Chrome (390 px dan 1440 px) dan 14 feature test.

## Hard Gate

| Aturan | Status | Bukti |
| --- | --- | --- |
| R-02 tanda pisah panjang | PASS | Pencarian karakter U+2014, U+2192, U+2197 di resources, app, config, lang, seeder: 0 hasil. |
| R-03 mobile | PASS | Cek `scrollWidth > clientWidth` di 35 screenshot: tidak ada overflow. Tabel lebar bergulir di dalam kartunya. Tombol minimal 44 sampai 48 px. |
| R-17, R-36 angka dan klaim | PASS | Tidak ada statistik pemasaran. Semua angka berasal dari database. |
| R-18, R-38 data karangan | PASS | Tidak ada testimoni. Akun demo bernama "Mahasiswa Demo 01", foto demo bertuliskan "Foto contoh". |
| R-23 aset | PASS | Logo memakai wordmark teks dan kotak `[LOGO KAMPUS]`. Avatar berupa inisial. Lencana tier memakai ikon shield. |
| R-24, R-26 navigasi dan kontrol | PASS | Klik satu per satu: Masuk ke /pindai, salah sandi ke pesan error, kategori duplikat ke peringatan + tautan, Terbitkan tanpa foto ke validasi, kamera ke foto tersimpan, Konfirmasi ke state "Sudah Kamu Konfirmasi", tab Quest Saya, Semua waktu di peringkat, Klaim ke nomor seri, Unduh PDF ke file PDF, Mulai Kerjakan ke tiket, Selesaikan Quest nonaktif sampai foto After ada lalu sukses, filter gedung dan kategori, Tolak tanpa alasan ke error, Tolak ke penalti, Loloskan ke antrean kosong, Tambah Ruangan duplikat ke error, Cetak Stiker nonaktif sampai ada centang lalu membuka halaman cetak, Menu admin di HP membuka dan menutup. |
| R-25 kontras | PASS | Teks terendah 4.70:1 (#9A5B0B di #F7EEDC). Border input #8A8F86 3.31:1. |
| R-27 state | PASS | Kosong, gagal, dan memuat ada di Quest Saya, Profil Ruangan, Antrean, Moderasi, Statistik, Verifikasi, Pindai (izin ditolak, QR asing), form (tombol "Menerbitkan..."). |
| R-28 FAQ | PASS | Tidak ada FAQ. |
| R-32 keyboard | PASS | Skip link, focus outline 2 px, dialog `<dialog>` tertutup dengan Escape (diuji), filter memakai tombol Terapkan bukan auto-submit. |
| R-33 skrip patch | PASS | Tidak ada skrip yang mengubah file sumber. |
| R-34, R-21 tema | PASS | Satu tema terang. Satu-satunya layar gelap adalah viewfinder kamera. |
| R-35 verifikasi | PASS | Server dijalankan, semua alur di atas diklik di Chrome, console bersih (satu-satunya error adalah uji halaman 404 yang disengaja). |
| R-37 arah desain | PASS | Arah dari pemilik dan DESIGN.md dari Stitch. |
| R-39 semantik | PASS | `lang="id"`, `<title>` unik, landmark header/nav/main, label terlihat di setiap field, `aria-label` di tombol ikon, `cursor: pointer` di base CSS, `[hidden]` selalu menang. Zoom tidak dikunci. |
| R-40 performa | PASS | Foto memakai `width`/`height` atau rasio, font dan ikon di-host sendiri, tidak ada skrip pihak ketiga. Skor Lighthouse tidak diukur. |

## Purpose-Gate dan Liveliness

| Aturan | Status | Bukti |
| --- | --- | --- |
| R-01, R-10, R-12, R-13 | PASS | Tanpa gradien warna, glow, atau glassmorphism. Bayangan hanya di dialog. |
| R-04, R-22 | PASS | Ikon Material Symbols dipilih per kategori fasilitas, disalin sebagai SVG. Tidak ada ilustrasi. |
| R-06 | PASS | Plus Jakarta Sans untuk UI, IBM Plex Mono hanya untuk kode, nomor seri, HP, dan stopwatch. |
| R-09, R-14, R-19 | PASS | Cap status menandai status nyata. Daftar memakai baris, bukan grid kartu identik. Satu-satunya gerak adalah stopwatch. |
| Liveliness | PASS | Fokus per layar: HP (M3), stopwatch (M6, W1), XP (M8), ruangan kritis (W4). Satu aksen merah untuk Raid Boss. Motif: meter HP 10 segmen, cap status, nomor quest. |
| R-05, R-11, R-15, R-16, R-29, R-30, R-31 | PASS | Tanpa baris kartu statistik. Radius 2, 6, 8 px. Tombol memakai kalimat dari alur. Palet 2 inti + 1 aksen. Alasan desain ada di dokumen paket prompt Stitch. |
