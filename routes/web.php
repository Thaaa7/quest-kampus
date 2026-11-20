<?php

use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\Student\LeaderboardController;
use App\Http\Controllers\Student\MyQuestsController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\RoomController;
use App\Http\Controllers\Student\ScanController;
use App\Http\Controllers\Technician\QueueController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect(auth()->user()->homeRoute()) : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'show'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.attempt');
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Halaman publik untuk pengurus BEM, tanpa login.
Route::get('/verifikasi', [CertificateController::class, 'verify'])->name('verify');
Route::get('/verifikasi/{serial}', [CertificateController::class, 'verifySerial'])->name('verify.show');

Route::middleware('auth')->group(function () {
    Route::get('/foto/{quest}/{which}', PhotoController::class)->whereIn('which', ['before', 'after'])->name('photo');
    Route::get('/sertifikat/{certificate}/pdf', [CertificateController::class, 'pdf'])->name('certificate.pdf');

    Route::middleware('role:mahasiswa')->group(function () {
        Route::get('/pindai', ScanController::class)->name('scan');
        Route::get('/r/{token}', [RoomController::class, 'show'])->name('room.show');
        Route::get('/r/{token}/lapor', [QuestController::class, 'create'])->name('quest.create');
        Route::post('/r/{token}/lapor', [QuestController::class, 'store'])->name('quest.store');
        Route::get('/quest/{quest}/gabung', [QuestController::class, 'join'])->name('quest.join');
        Route::post('/quest/{quest}/konfirmasi', [QuestController::class, 'confirm'])->name('quest.confirm');
        Route::get('/quest-saya', MyQuestsController::class)->name('my-quests');
        Route::get('/profil', [ProfileController::class, 'show'])->name('profile');
        Route::post('/profil/sertifikat/{tier}', [ProfileController::class, 'claim'])->whereIn('tier', ['madya', 'utama'])->name('certificate.claim');
        Route::get('/peringkat', LeaderboardController::class)->name('leaderboard');
    });

    Route::get('/quest/{quest}', [QuestController::class, 'show'])->name('quest.show');

    Route::middleware('role:teknisi')->prefix('teknisi')->name('technician.')->group(function () {
        Route::get('/', [QueueController::class, 'index'])->name('queue');
        Route::get('/quest/{quest}', [QueueController::class, 'ticket'])->name('ticket');
        Route::post('/quest/{quest}/mulai', [QueueController::class, 'start'])->name('start');
        Route::post('/quest/{quest}/selesai', [QueueController::class, 'resolve'])->name('resolve');
        Route::post('/quest/{quest}/tandai', [QueueController::class, 'flag'])->name('flag');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::redirect('/', '/admin/statistik');
        Route::get('/statistik', StatsController::class)->name('stats');
        Route::get('/moderasi', [ModerationController::class, 'index'])->name('moderation');
        Route::post('/moderasi/{quest}/loloskan', [ModerationController::class, 'approve'])->name('moderation.approve');
        Route::post('/moderasi/{quest}/tolak', [ModerationController::class, 'reject'])->name('moderation.reject');
        Route::get('/ruangan', [AdminRoomController::class, 'index'])->name('rooms');
        Route::post('/ruangan', [AdminRoomController::class, 'store'])->name('rooms.store');
        Route::post('/ruangan/cetak', [AdminRoomController::class, 'print'])->name('rooms.print');
        Route::get('/ruangan/{room}/ubah', [AdminRoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/ruangan/{room}', [AdminRoomController::class, 'update'])->name('rooms.update');
        Route::post('/gedung', [AdminRoomController::class, 'storeBuilding'])->name('buildings.store');
    });
});
