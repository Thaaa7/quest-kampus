<?php

/*
| Aturan permainan Kampus Quest. Angka-angka ini mengikuti dokumen alur
| (userflow) dan bisa diubah tanpa menyentuh kode.
*/

return [

    'hp' => [
        'max' => 100,
        // Setiap quest aktif menurunkan HP ruangan sebanyak ini.
        'per_quest' => 20,
        // Quest yang naik jadi Raid Boss menurunkan HP sebanyak ini (zona kritis).
        'raid_boss' => 80,
        // Batas zona untuk label dan warna meter HP.
        'zones' => [
            'sehat' => 70,
            'perhatian' => 40,
        ],
    ],

    'raid_boss' => [
        // Minimal konfirmasi dalam jendela waktu agar quest naik jadi Raid Boss.
        'threshold' => 10,
        'window_minutes' => 60,
    ],

    'xp' => [
        'create' => 5,
        'discoverer_bonus' => 45,
        'party' => 15,
        'penalty' => -20,
    ],

    'tiers' => [
        [
            'level' => 1,
            'name' => 'Penjaga Kampus',
            'xp' => 500,
            'reward' => 'Lencana profil dan akses papan peringkat fakultas',
        ],
        [
            'level' => 2,
            'name' => 'Ksatria Fasilitas',
            'xp' => 1500,
            'certificate' => 'madya',
            'reward' => 'E-Sertifikat Kontribusi Madya',
        ],
        [
            'level' => 3,
            'name' => 'Pahlawan Sarpras',
            'xp' => 3000,
            'certificate' => 'utama',
            'reward' => 'E-Sertifikat Kontribusi Utama',
        ],
    ],

    'certificates' => [
        'madya' => ['name' => 'E-Sertifikat Kontribusi Madya', 'code' => 'M', 'skp' => 5, 'tier' => 2],
        'utama' => ['name' => 'E-Sertifikat Kontribusi Utama', 'code' => 'U', 'skp' => 10, 'tier' => 3],
    ],

    // Laporan hanya bisa dibuat dalam rentang ini setelah memindai QR ruangan.
    'scan_ttl_minutes' => 30,

    // Laporan otomatis ditandai untuk moderasi bila satu akun melapor sebanyak
    // ini dalam jendela waktu tersebut.
    'auto_flag' => [
        'max_reports' => 3,
        'window_minutes' => 10,
    ],

    'photo_max_kb' => 8192,
];
