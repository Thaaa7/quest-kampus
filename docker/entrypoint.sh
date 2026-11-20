#!/bin/sh
# Dijalankan setiap kali container menyala, termasuk saat Render membangunkan server yang tertidur.
set -e
cd /var/www/html

export PORT="${PORT:-10000}"

# Render mengisi APP_KEY dengan nilai acak base64 (generateValue). Laravel butuh awalan "base64:".
if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY kosong, memakai kunci sementara. Sesi login akan hilang setiap restart." >&2
  APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
fi
case "$APP_KEY" in
  base64:*) ;;
  *)
    if php -r 'exit(strlen((string) base64_decode(getenv("APP_KEY"), true)) === 32 ? 0 : 1);'; then
      APP_KEY="base64:$APP_KEY"
    else
      APP_KEY="base64:$(php -r 'echo base64_encode(hash("sha256", getenv("APP_KEY"), true));')"
    fi
    ;;
esac
export APP_KEY

# Render memberi alamat publik layanan di RENDER_EXTERNAL_URL.
if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
  export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Migrasi, lalu data demo hanya kalau database masih kosong.
# Database bisa butuh beberapa detik untuk siap, jadi dicoba ulang.
tries=0
until php artisan kq:setup --no-interaction; do
  tries=$((tries + 1))
  if [ "$tries" -ge 10 ]; then
    echo "Database belum bisa dihubungi setelah 10 kali coba." >&2
    exit 1
  fi
  sleep 3
done

php artisan config:cache --no-interaction
chown -R www-data:www-data storage bootstrap/cache

# Apache hanya boleh memuat satu MPM. Railway kadang menambahkan mpm_event saat container
# menyala, jadi pastikan yang aktif hanya mpm_prefork (yang dibutuhkan mod_php).
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
a2enmod -q mpm_prefork >/dev/null 2>&1 || true

exec apache2-foreground
