# Kampus Quest

Aplikasi web pelaporan kerusakan fasilitas kampus bergaya quest. Mahasiswa memindai QR ruangan, melapor (New Quest) atau mengonfirmasi laporan yang ada (Join Party), teknisi menyelesaikan quest dengan foto bukti, dan XP yang terkumpul bisa diklaim menjadi e-sertifikat yang diverifikasi BEM lewat QR.

Dibangun dengan Laravel 13, MySQL, Blade, dan Tailwind CSS. Tampilannya mengikuti desain dari Google Stitch (token warna, font, dan komponen ada di `tailwind.config.js`).

## Kebutuhan

- PHP 8.3 atau lebih baru, dengan ekstensi `pdo_mysql`, `mbstring`, `fileinfo`, `dom`, `zip`
- Composer 2
- MySQL 8 atau MariaDB 10.6+ (paling mudah lewat Laragon atau XAMPP)
- Node.js 20+ (opsional, hanya kalau kamu mengubah tampilan. Hasil build CSS/JS sudah ikut di `public/build`)

Pengguna XAMPP: buka `php.ini`, pastikan baris `extension=fileinfo`, `extension=zip`, dan `extension=pdo_mysql` tidak diawali titik koma.

## Menjalankan

### Cara cepat (Windows + Laragon)

1. Buka Laragon dan tekan **Start All** supaya MySQL berjalan.
2. Pastikan database `quest_kampus` sudah ada.
3. Klik dua kali `kampus-quest.bat` di folder proyek.

Skrip itu memakai PHP 8.4 dari Laragon, memasang paket Laravel (sekali saja, butuh internet), membuat `.env`, menyiapkan tabel dan data demo, lalu membuka http://127.0.0.1:8000. Untuk membuka aplikasi lagi di lain waktu, klik dua kali file yang sama. Data yang sudah ada tidak ditimpa.

### Cara manual

Buka terminal di folder proyek (`C:\Users\ASUS\Documents\kampus-quest`), lalu:

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Buat database kosong bernama `quest_kampus` (utf8mb4) lewat phpMyAdmin atau HeidiSQL. Kalau user atau password MySQL kamu bukan `root` tanpa password, ubah `DB_USERNAME` dan `DB_PASSWORD` di `.env`.

```bash
php artisan migrate --seed
php artisan serve
```

Buka http://localhost:8000.

## Akun demo

Semua akun memakai kata sandi `kampusquest`. Nama dan angka di data demo adalah contoh, bukan data asli.

| Peran | NIM / NIP | Untuk mencoba |
| --- | --- | --- |
| Mahasiswa | `2210001` | Tier 1 (1.240 XP), punya quest aktif, party, dan satu laporan ditolak |
| Mahasiswa | `2210002` | Tier 2 (1.560 XP), bisa langsung klaim E-Sertifikat Madya |
| Mahasiswa | `2210016` | 320 XP, papan peringkat masih terkunci |
| Teknisi | `TKN001` | Antrean dengan satu Raid Boss, satu tiket sedang dikerjakan |
| Admin | `ADM001` | Statistik, moderasi (2 laporan ditandai), gedung dan ruangan, cetak QR |

Halaman verifikasi untuk BEM ada di http://localhost:8000/verifikasi dan bisa dibuka tanpa login.

### Mencoba alur pindai QR di laptop

Mahasiswa hanya bisa melapor setelah membuka alamat dari stiker QR ruangan. Untuk mencoba tanpa mencetak stiker: masuk sebagai admin, buka Gedung & Ruangan, cetak stiker satu ruangan, lalu pindai QR di layar dengan kamera HP. Atau salin alamatnya lewat tinker:

```bash
php artisan tinker --execute="echo App\Models\Room::where('code','C-104')->first()->scanUrl();"
```

Buka alamat itu saat sudah masuk sebagai `2210001`.

### Mencoba di HP

Browser hanya mengizinkan kamera di halaman HTTPS atau `localhost`. Jalankan `php artisan serve --host=0.0.0.0`, lalu buka `http://IP-LAPTOP:8000` dari HP di Wi-Fi yang sama. Di alamat http seperti ini:

- Tombol Ambil Foto tetap bekerja: aplikasi membuka kamera bawaan HP lewat form upload.
- Pemindai QR di dalam aplikasi tidak bisa membuka kamera. Pakai aplikasi kamera bawaan HP untuk memindai stiker, karena isi QR adalah alamat ruangan.

Untuk pemindai di dalam aplikasi, pakai HTTPS. Di Laragon: pindahkan folder proyek ke `C:\laragon\www`, aktifkan SSL di menu Apache, lalu buka `https://kampus-quest.test` dan ubah `APP_URL` di `.env` ke alamat itu.

## Membuat akun asli

Belum ada halaman pendaftaran. Akun dibuat lewat perintah ini:

```bash
php artisan kq:user 2210123 "Nama Mahasiswa" mahasiswa --faculty="Fakultas Teknik"
php artisan kq:user 198001012005 "Nama Teknisi" teknisi
php artisan kq:user ADM002 "Nama Admin" admin
```

Kata sandi ditanyakan di terminal (atau tambahkan `--password=...`).

## Mengubah aturan permainan

Semua angka ada di `config/kampusquest.php`: XP per aktivitas (+5, +45, +15, -20), batas Raid Boss (10 konfirmasi dalam 60 menit), penurunan HP, tier (500, 1.500, 3.000 XP), dan rekomendasi SKP sertifikat.

## Mengubah tampilan

```bash
npm install
npm run build
```

Untuk mode pengembangan dengan reload otomatis, jalankan `npm run dev` bersamaan dengan `php artisan serve`.

## Peta layar Stitch ke kode

| Layar Stitch | File Blade | Alamat |
| --- | --- | --- |
| M1 Masuk | `resources/views/auth/login.blade.php` | `/masuk` |
| M2 Pindai QR Ruangan | `resources/views/student/scan.blade.php` | `/pindai` |
| M3 Profil Ruangan | `resources/views/student/room.blade.php` | `/r/{token}` |
| M4 Laporkan Kerusakan | `resources/views/student/report.blade.php` | `/r/{token}/lapor` |
| M5 Join Party | `resources/views/student/join.blade.php` | `/quest/{nomor}/gabung` |
| M6 Detail Quest | `resources/views/quest/show.blade.php` | `/quest/{nomor}` |
| M7 Quest Saya | `resources/views/student/my-quests.blade.php` | `/quest-saya` |
| M8 Profil dan XP | `resources/views/student/profile.blade.php` | `/profil` |
| M9 Peringkat Fakultas | `resources/views/student/leaderboard.blade.php` | `/peringkat` |
| M10 Tiket Lapangan | `resources/views/technician/ticket.blade.php` | `/teknisi/quest/{nomor}` |
| M11 Verifikasi BEM | `resources/views/verify/show.blade.php` | `/verifikasi` |
| W1 Antrean Tiket | `resources/views/technician/queue.blade.php` | `/teknisi` |
| W2 Gedung & Ruangan | `resources/views/admin/rooms.blade.php` | `/admin/ruangan` |
| W3 Moderasi | `resources/views/admin/moderation.blade.php` | `/admin/moderasi` |
| W4 Statistik | `resources/views/admin/stats.blade.php` | `/admin/statistik` |
| W5 E-Sertifikat PDF | `resources/views/certificate/pdf.blade.php` | `/sertifikat/{seri}/pdf` |

Logika inti ada di `app/Services/QuestService.php` (lapor, konfirmasi, Raid Boss, selesai, moderasi), `XpService.php`, dan `CertificateService.php`.

## Menjalankan test

```bash
php artisan test
```

Test memakai SQLite di memori, jadi butuh ekstensi `pdo_sqlite`. Ada 14 test yang mencakup alur lapor, Join Party, kenaikan Raid Boss, penyelesaian oleh teknisi, moderasi, klaim dan verifikasi sertifikat, serta pemisahan peran.

## Yang masih perlu kamu isi

- Logo kampus: sertifikat PDF masih memakai kotak `[LOGO KAMPUS]`.
- Pendaftaran akun dan reset kata sandi lewat aplikasi belum ada (saat ini lewat `php artisan kq:user`).
- Foto di data demo adalah gambar bertuliskan "Foto contoh". Foto asli dari kamera disimpan di `storage/app/private/quests`.

## Lisensi font

Plus Jakarta Sans (Tokotype) dan IBM Plex Mono (IBM) memakai SIL Open Font License. Salinan lisensinya ada di `resources/fonts`.
