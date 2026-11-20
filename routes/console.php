<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

Artisan::command('kq:user {identifier} {name} {role=mahasiswa} {--faculty=} {--password=}', function () {
    $role = $this->argument('role');
    if (! in_array($role, [User::MAHASISWA, User::TEKNISI, User::ADMIN], true)) {
        $this->error('Peran harus mahasiswa, teknisi, atau admin.');

        return 1;
    }

    $password = $this->option('password') ?: $this->secret('Kata sandi');
    if (! $password) {
        $this->error('Kata sandi wajib diisi.');

        return 1;
    }

    $user = User::updateOrCreate(
        ['identifier' => $this->argument('identifier')],
        ['name' => $this->argument('name'), 'role' => $role, 'faculty' => $this->option('faculty'), 'password' => $password],
    );

    $this->info("Akun {$user->identifier} ({$user->roleLabel()}) tersimpan.");
})->purpose('Buat atau perbarui akun Kampus Quest');

Artisan::command('kq:setup', function () {
    // Aman dijalankan berulang: migrasi selalu, data demo hanya bila belum ada akun.
    if ($this->call('migrate', ['--force' => true]) !== 0) {
        return 1;
    }

    if (User::query()->exists()) {
        $this->info('Database sudah berisi akun, data demo tidak ditambahkan lagi.');

        return 0;
    }

    $this->call('db:seed', ['--force' => true]);
    $this->info('Data demo dibuat. Semua akun demo memakai kata sandi: kampusquest');

    return 0;
})->purpose('Siapkan database Kampus Quest (migrasi dan data demo)');

