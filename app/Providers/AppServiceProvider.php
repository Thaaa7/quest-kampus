<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Batas percobaan login per akun dan per IP. Mahasiswa satu kampus sering
        // keluar lewat IP publik yang sama, jadi batas per IP saja akan mengunci semua orang.
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(6)->by(Str::lower((string) $request->input('identifier')).'|'.$request->ip()));
    }
}
