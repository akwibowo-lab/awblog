# Panduan Deploy Staging Hostinger

Panduan ini menyiapkan subdomain staging terisolasi di Hostinger Premium tanpa mengganggu `akutwibowo.com` (WordPress produksi) maupun `galangbahari.com`.

## 1. Struktur Direktori yang Disarankan

Pisahkan kode Laravel dari `public_html` agar `.env`, `storage/`, dan `database/` tidak dapat diakses publik:

- **Aplikasi Laravel**: `~/apps/awblog-staging`
- **Document root subdomain**: `~/domains/akutwibowo.com/public_html/new` (atau custom folder subdomain di hPanel) diarahkan ke `~/apps/awblog-staging/public` via symlink:
  ```bash
  ln -s ~/apps/awblog-staging/public ~/domains/akutwibowo.com/public_html/new
  ```
  > Jika hPanel otomatis membuat folder kosong `~/domains/akutwibowo.com/public_html/new`, pindahkan/hapus folder kosong tersebut (hanya jika kosong) sebelum membuat symlink.

## 2. Subdomain (Cloudflare + hPanel), PHP 8.3 & Database

1. **DNS Cloudflare**: tambahkan A record `new` (`new.akutwibowo.com`) mengarah ke IP server Hostinger (SSL mode *Full (strict)* jika SSL Hostinger aktif).
2. **Subdomain hPanel**: tetap daftarkan `new.akutwibowo.com` di hPanel -> *Domains* -> *Subdomains* agar web server Hostinger mengenali host-nya.
3. **PHP Web**: pastikan versi PHP untuk subdomain/domain diset ke **PHP 8.3** di hPanel -> *Advanced* -> *PHP Configuration*.
3. **PHP CLI**: gunakan `/opt/alt/php83/usr/bin/php` untuk semua perintah `artisan` dan `composer`.
4. **Database**: buat database MariaDB baru khusus staging (mis. `uXXXX_awblog_stg`) beserta user khusus. Jangan gunakan database WordPress (`c71gfp8_*`) atau database situs lain.
5. **Proteksi Staging (opsional)**: tambahkan HTTP Basic Auth atau batasi akses di `.htaccess` jika staging belum siap diindeks publik; pastikan `robots.txt` atau header `X-Robots-Tag: noindex` aktif selama masa staging.

## 3. Setup Awal via SSH

```bash
mkdir -p ~/apps
git clone -b feat/staging-hostinger git@github.com:akwibowo-lab/awblog.git ~/apps/awblog-staging
cd ~/apps/awblog-staging
cp .env.staging.example .env
# Isi APP_KEY, DB_DATABASE, DB_USERNAME, DB_PASSWORD pada .env
/opt/alt/php83/usr/bin/php artisan key:generate
bash scripts/deploy-staging.sh feat/staging-hostinger
```

> Catatan aset frontend: shared hosting sering memiliki batas memori Node.js. Bangun aset di mesin dev (`npm run build`), lalu unggah isi `public/build/` ke `~/apps/awblog-staging/public/build/` via `rsync`/`scp`, atau jalankan `BUILD_ASSETS=1 bash scripts/deploy-staging.sh` bila Node.js tersedia di server.

## 4. Cron Job Scheduler & Queue

Tambahkan 1 cron job di hPanel -> *Advanced* -> *Cron Jobs* (setiap menit):

```cron
* * * * * /opt/alt/php83/usr/bin/php /home/uXXXX/apps/awblog-staging/artisan schedule:run >> /dev/null 2>&1
```

`routes/console.php` sudah menjadwalkan `queue:work --stop-when-empty --max-time=50` setiap menit dengan `withoutOverlapping()`.

## 5. Rollback

1. Hapus symlink/folder `~/domains/akutwibowo.com/public_html/new` dan hapus subdomain di hPanel & record DNS `new` di Cloudflare.
2. Hapus `~/apps/awblog-staging`.
3. Hapus cron job staging di hPanel dan drop database `uXXXX_awblog_stg`.
