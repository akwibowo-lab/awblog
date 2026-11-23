# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 1b — Staging Hostinger selesai
- **Branch aktif**: `feat/staging-hostinger` (bercabang dari `feat/filament-admin`)
- **Fitur sesi terakhir**: Staging Hostinger (`new.akutwibowo.com` di `~/apps/awblog-staging`, docroot symlink `public_html/new`, PHP 8.3.37 web & CLI, DB `u735114786_newAW` termigrasi, `storage:link`, cron `schedule:run` + `queue:work --stop-when-empty`)
- **Status**: selesai; origin Hostinger `http://new.akutwibowo.com/` & `/up` `200 OK` (menunggu instalasi SSL Hostinger selesai untuk HTTPS Cloudflare), `pint` ✔, Pest (`44 passed`), Playwright (`8 passed, --workers=1`) ✔
- **Terakhir diperbarui**: 2026-11-23

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Performa & Deploy (10)
- **Tujuan**: Optimasi performa (caching halaman/query, verifikasi LCP/WebP), verifikasi akhir HTTPS staging `new.akutwibowo.com` (serta impor data/media staging jika diperlukan), dan merge ke `main`.

## Keputusan yang Sudah Final

| Tanggal | Keputusan | Alasan |
|---|---|---|
| - | Stack: Laravel 13 + Blade + Tailwind v4 + Alpine + Filament 5 + MariaDB | SEO baik, sederhana, selaras Hostinger & awork |
| 2026-11-12 | WP lama: `/%postname%/`, 235 artikel, URL artikel/kategori/tag dipertahankan persis; selebihnya 301 | Jaga SEO |
| 2026-11-10 | Domain `akutwibowo.com`; hosting Hostinger Premium; homepage bilingual ID/EN, blog ID | Target klien lokal + internasional |
| 2026-11-12 | Brand: Akut Wibowo (AW Orbit); arah visual sinematik gelap; token di `resources/css/app.css` | Identitas drone |
| 2026-11-22 | Dev 1 SOT aktif di Proxmox LXC `CT 103 (laravel-dev)` IP `192.168.0.105` (Ubuntu 24.04, 4 vCPU, 4 GB RAM, 30 GB disk) | Hemat RAM host 8 GB, multi-project Laravel via Remote SSH |
| 2026-11-22 | Kontak homepage: WhatsApp `+6289623084000` (`wa.me/6289623084000`) & email `akwibowo@gmail.com` (tidak ditampilkan eksplisit di tombol) | Permintaan pemilik |
| 2026-11-23 | Subdomain staging `new.akutwibowo.com` di Hostinger (`~/apps/awblog-staging`, deploy via `rsync` dari `laravel-dev` + `scripts/deploy-staging.sh`) | Isolasi penuh dari WP produksi & `galangbahari.com`; Hostinger tanpa Node.js |

## Keputusan Terbuka (perlu jawaban pemilik)

- [ ] Host kanonik (`www.akutwibowo.com`), URL gambar lama `/wp-content/uploads/...`, metode bahasa (`/` + `/en/`), letak indeks blog (`/blog`).
- [ ] Konfirmasi SSL Hostinger untuk `new.akutwibowo.com` aktif (agar `https://new.akutwibowo.com` tidak `525`).

## Infrastruktur & Data Lama

- **Workspace SOT aktif (Remote SSH)**: `ssh://akut@laravel-dev/home/akut/projects/awblog` (`192.168.0.105`).
- **Staging Hostinger**: `ssh -p 65002 u735114786@145.79.14.199`, app `~/apps/awblog-staging`, docroot `~/domains/akutwibowo.com/public_html/new` (symlink), DB `u735114786_newAW`, PHP 8.3.37 (`/opt/alt/php83/usr/bin/php`).
- **DB dev Proxmox**: MariaDB 10.11 lokal (`awblog_dev`, `awblog_test`, user `awblog`) + staging `wp_legacy` (60 tabel prefix `c71gfp8_*`, read-only untuk `awblog`).
- **Media lokal dev**: `storage/app/public/media/` (1.477 media + varian WebP) tersambung via `public/storage`.
- **Repo GitHub**: `git@github.com:akwibowo-lab/awblog.git` (private).

## Peta Repo Ringkas (Tambahan S12)

```
.env.staging.example                   Template env staging Hostinger (new.akutwibowo.com)
scripts/deploy-staging.sh              Skrip deploy idempoten PHP 8.3 CLI
docs/deploy-hostinger.md               Panduan setup subdomain, cron, & rollback Hostinger
routes/console.php                     Schedule queue:work --stop-when-empty setiap menit
tests/Feature/StagingHealthTest.php    Tes Pest kesiapan staging & scheduler
tests/e2e/health.spec.js               Tes Playwright endpoint /up
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

10. Performa & deploy (caching halaman/query, verifikasi LCP/WebP, merge `main`)

## Catatan untuk Sesi Berikutnya

- Mulai sesi baru dari branch `feat/staging-hostinger` (atau setelah cek `https://new.akutwibowo.com` aktif SSL-nya).
- Di Playwright pada `laravel-dev`, jalankan `npx playwright test --workers=1` agar `php artisan serve` tidak timeout saat paralel.
- 5 post lama masih punya referensi `wp-content/uploads` karena 8 file sumber hilang di arsip `2026-11-12` (bisa dibersihkan via `/admin/posts`).
