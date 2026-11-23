# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 1b — Performa, Staging HTTPS, & Merge `main` selesai
- **Branch aktif**: `main` (hasil merge `feat/staging-hostinger`)
- **Fitur sesi terakhir**: Performa & Deploy (10) — caching HTML beranda, query blog/proyek, & XML sitemap/RSS via `ContentCache` + invalidasi otomatis model event di `AppServiceProvider`, header `Cache-Control` aset statis di `public/.htaccess`, verifikasi LCP (`fetchpriority="high"`) & WebP (`loading="lazy"`), verifikasi HTTPS `https://new.akutwibowo.com` (`HTTP/2 200`), deploy ke staging, & merge ke `main`
- **Status**: selesai; `pint` ✔, Pest (`48 passed`) ✔, Playwright (`9 passed, --workers=1`) ✔, `https://new.akutwibowo.com/` & `/up` `HTTP/2 200` ✔
- **Terakhir diperbarui**: 2026-11-23

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Impor Data & Media WordPress ke Staging (`new.akutwibowo.com`)
- **Tujuan**: Impor 235 artikel, kategori, tag, redirect, dan media WebP (`storage/app/public/media/`) dari dev `laravel-dev` ke DB staging `u735114786_newAW` & `~/apps/awblog-staging/storage/app/public/media/` (serta buat akun admin staging) agar blog staging terisi penuh untuk review sebelum cutover produksi.

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
| 2026-11-23 | `ContentCache` berbasis domain-version (`CACHE_STORE=file`, aman untuk `serializable_classes => false`) | Kompatibel shared hosting tanpa Redis |

## Keputusan Terbuka (perlu jawaban pemilik)

- [ ] Host kanonik (`www.akutwibowo.com`), URL gambar lama `/wp-content/uploads/...`, metode bahasa (`/` + `/en/`), letak indeks blog (`/blog`).

## Infrastruktur & Data Lama

- **Workspace SOT aktif (Remote SSH)**: `ssh://akut@laravel-dev/home/akut/projects/awblog` (`192.168.0.105`).
- **Staging Hostinger**: `ssh -p 65002 u735114786@145.79.14.199`, app `~/apps/awblog-staging`, docroot `~/domains/akutwibowo.com/public_html/new` (symlink), DB `u735114786_newAW` (baru termigrasi, belum terisi data WP), PHP 8.3.37.
- **DB dev Proxmox**: MariaDB 10.11 lokal (`awblog_dev`, `awblog_test`, user `awblog`) + staging `wp_legacy` (60 tabel prefix `c71gfp8_*`, read-only untuk `awblog`).
- **Media lokal dev**: `storage/app/public/media/` (1.477 media + varian WebP) tersambung via `public/storage`.
- **Repo GitHub**: `git@github.com:akwibowo-lab/awblog.git` (private).

## Peta Repo Ringkas (Tambahan S13)

```
app/Services/Cache/ContentCache.php       Cache helper berbasis domain-version (file/db safe)
app/Providers/AppServiceProvider.php      Invalidasi cache otomatis via model events
public/.htaccess                          Header Cache-Control aset statis (.webp, .css, .js)
tests/Feature/PerformanceCacheTest.php    Tes Pest cache hit/invalidation & LCP/WebP
tests/e2e/performance.spec.js             Tes Playwright LCP fetchpriority & lazy loading
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

- Impor data & media WordPress ke staging `new.akutwibowo.com` + buat akun admin staging
- Cutover DNS/domain produksi `akutwibowo.com` setelah review staging selesai

## Catatan untuk Sesi Berikutnya

- Mulai dari branch `main` (buat branch baru bila perlu perubahan kode).
- Database staging `u735114786_newAW` saat ini masih kosong (hanya tabel hasil `migrate --force`), sehingga blog staging tampil kosong sampai impor data & `rsync` media dilakukan.
- Di Playwright pada `laravel-dev`, jalankan `npx playwright test --workers=1`.
