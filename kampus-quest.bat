@echo off
setlocal EnableExtensions
title Kampus Quest
cd /d "%~dp0"

rem ==================================================================
rem  Kampus Quest: siapkan lalu jalankan aplikasi (Windows + Laragon)
rem  Klik dua kali file ini. Aman dijalankan berulang kali.
rem ==================================================================

rem Laravel 13 butuh PHP 8.3 atau lebih baru. Ubah baris ini bila folder PHP-mu berbeda.
set "PHP_DIR=C:\laragon\bin\php\php-8.4.27-1-Win32-vs17-x64"
set "PHP_EXE=%PHP_DIR%\php.exe"

set "COMPOSER_PHAR=C:\laragon\bin\composer\composer.phar"
if exist "%~dp0composer.phar" set "COMPOSER_PHAR=%~dp0composer.phar"

echo.
echo [1/5] Memeriksa PHP...
if not exist "%PHP_EXE%" (
    echo [GAGAL] PHP tidak ditemukan di: %PHP_DIR%
    echo         Buka file ini dengan Notepad dan ubah baris PHP_DIR ke folder PHP 8.3 atau 8.4.
    goto :fail
)

rem php.ini khusus proyek supaya ekstensi yang dibutuhkan pasti aktif,
rem apa pun versi PHP yang sedang dipilih di Laragon.
if not exist "storage\php" mkdir "storage\php"
(
    echo extension_dir="%PHP_DIR%\ext"
    echo extension=curl
    echo extension=fileinfo
    echo extension=mbstring
    echo extension=openssl
    echo extension=pdo_mysql
    echo extension=zip
    echo memory_limit=512M
    echo upload_max_filesize=10M
    echo post_max_size=12M
    echo max_execution_time=120
    echo date.timezone=Asia/Makassar
) > "storage\php\php.ini"
set "PHPRC=%~dp0storage\php"

"%PHP_EXE%" -r "echo '        PHP ', PHP_VERSION, PHP_EOL; foreach (['openssl','mbstring','fileinfo','pdo_mysql'] as $e) { if (!extension_loaded($e)) { echo '[GAGAL] Ekstensi PHP belum aktif: ', $e, PHP_EOL; exit(1); } } if (version_compare(PHP_VERSION, '8.3.0', '<')) { echo '[GAGAL] Butuh PHP 8.3 atau lebih baru.', PHP_EOL; exit(1); }"
if errorlevel 1 goto :fail

echo.
echo [2/5] Memasang paket Laravel (sekali saja, butuh internet)...
if exist "vendor\autoload.php" (
    echo         Sudah terpasang, dilewati.
) else (
    if not exist "%COMPOSER_PHAR%" (
        echo [GAGAL] composer.phar tidak ditemukan di %COMPOSER_PHAR%
        echo         Unduh dari https://getcomposer.org/download/ lalu taruh composer.phar di folder ini.
        goto :fail
    )
    "%PHP_EXE%" "%COMPOSER_PHAR%" install --no-interaction --prefer-dist
)
if not exist "vendor\autoload.php" goto :fail

echo.
echo [3/5] Menyiapkan file .env...
if not exist ".env" copy ".env.example" ".env" >nul
findstr /r /c:"^APP_KEY=base64:" ".env" >nul
if errorlevel 1 "%PHP_EXE%" artisan key:generate --force
findstr /r /c:"^APP_KEY=base64:" ".env" >nul
if errorlevel 1 goto :fail

echo.
echo [4/5] Menyiapkan database quest_kampus...
"%PHP_EXE%" artisan config:clear >nul
"%PHP_EXE%" artisan kq:setup
if errorlevel 1 (
    echo.
    echo [GAGAL] Tidak bisa menyiapkan database.
    echo         - Pastikan Laragon sudah Start All, MySQL harus berjalan.
    echo         - Pastikan database quest_kampus sudah ada.
    echo         - Kalau MySQL-mu memakai password, isi DB_PASSWORD di file .env.
    goto :fail
)

echo.
echo [5/5] Menjalankan Kampus Quest di http://127.0.0.1:8000
echo         Akun demo: mahasiswa 2210001, teknisi TKN001, admin ADM001
echo         Kata sandi semua akun demo: kampusquest
echo         Tutup jendela ini untuk menghentikan aplikasi.
echo.
start "" /b cmd /c "ping -n 4 127.0.0.1 >nul & explorer http://127.0.0.1:8000"
"%PHP_EXE%" artisan serve --host=127.0.0.1 --port=8000
goto :eof

:fail
echo.
echo Proses berhenti. Baca pesan di atas, perbaiki, lalu jalankan file ini lagi.
pause
exit /b 1
