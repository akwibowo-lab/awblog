# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 9 — Admin Filament 5 selesai
- **Branch aktif**: `feat/filament-admin` (bercabang dari `feat/projects-page`; siap merge ke `main` setelah pemilik setuju)
- **Fitur sesi terakhir**: Admin Filament 5 (`/admin`, CRUD konten, SEO analyzer, preview, revisi, dan backend dinamis homepage)
- **Status**: selesai; logo AW Orbit, `FilamentUser`, 6 Resource (`Post`, `Category`, `Tag`, `Media`, `Project`, `Redirect`), `ManageHomepageSettings` (foto hero, sertifikasi, PDF portofolio, kontak, SEO), `PostSeoAnalyzer` (focus keyword + alt text gambar), preview khusus `/admin/preview/...`, sticky sidebar, auto-save draft, maks. 2 revisi artikel, `pint`, Pest (`40 passed`), Playwright (`7 passed`), dan cek manual ✔
- **Terakhir diperbarui**: 2026-11-22

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Staging Hostinger (1b) atau Performa & Deploy (10)
- **Tujuan**: Menyiapkan deploy awal ke Staging Hostinger (subdomain, PHP 8.3 web & CLI, DB, storage symlink, cron scheduler — butuh approval), atau optimasi performa & kesiapan rilis produksi.

## Keputusan yang Sudah Final

| Tanggal | Keputusan | Alasan |
|---|---|---|
| - | Stack: Laravel 13 + Blade + Tailwind v4 + Alpine + Filament 5 + MariaDB | SEO baik, sederhana, selaras Hostinger & awork |
| 2026-11-12 | WP lama: `/%postname%/`, 235 artikel, URL artikel/kategori/tag dipertahankan persis; selebihnya 301 | Jaga SEO |
| 2026-11-10 | Domain `akutwibowo.com`; hosting Hostinger Premium; homepage bilingual ID/EN, blog ID | Target klien lokal + internasional |
| 2026-11-12 | Brand: Akut Wibowo (AW Orbit); arah visual sinematik gelap; token di `resources/css/app.css` | Identitas drone |
| 2026-11-22 | Dev 1 SOT aktif di Proxmox LXC `CT 103 (laravel-dev)` IP `192.168.0.105` (Ubuntu 24.04, 4 vCPU, 4 GB RAM, 30 GB disk) | Hemat RAM host 8 GB, multi-project Laravel via Remote SSH |
| 2026-11-22 | Kontak homepage: WhatsApp `+6289623084000` (`wa.me/6289623084000`) & email `akwibowo@gmail.com` (tidak ditampilkan eksplisit di tombol) | Permintaan pemilik |

## Keputusan Terbuka (perlu jawaban pemilik)

- [ ] Host kanonik (`www.akutwibowo.com`), URL gambar lama `/wp-content/uploads/...`, metode bahasa (`/` + `/en/`), letak indeks blog (`/blog`).
- [ ] Cek Hostinger Premium detail: document root `public/`, cron, pemisahan dari `galangbahari.com` (PHP 8.3.37 CLI `/opt/alt/php83/usr/bin/php` & MariaDB 11.8.9 sudah terkonfirmasi ✔).

## Infrastruktur & Data Lama

- **Workspace SOT aktif (Remote SSH)**: `ssh://akut@laravel-dev/home/akut/projects/awblog` (`192.168.0.105`).
- **DB dev Proxmox**: MariaDB 10.11 lokal (`awblog_dev`, `awblog_test`, user `awblog`) + staging `wp_legacy` (60 tabel prefix `c71gfp8_*`, read-only untuk `awblog`).
- **Media lokal dev**: `storage/app/public/media/` (1.477 media + varian WebP) tersambung via `public/storage`.
- **Repo GitHub**: `git@github.com:akwibowo-lab/awblog.git` (private).
- **Hostinger**: WordPress lama di `~/domains/akutwibowo.com/public_html`, prefix `c71gfp8_*` (abaikan `wpu0_*`). Arsip hanya baca di `~/wp-legacy/` di `laravel-dev`.

## Peta Repo Ringkas

```
app/Providers/Filament/AdminPanelProvider.php    Panel /admin (AW Orbit, back-to-top hook)
app/Filament/Resources/{Posts,Categories,Tags,Media,Projects,Redirects}/  CRUD Filament 5
app/Filament/Pages/ManageHomepageSettings.php    Pengaturan homepage dinamis, foto hero, PDF portofolio
app/Http/Controllers/Admin/PreviewController.php Rute preview /admin/preview/{posts,projects} (noindex)
app/Services/Seo/PostSeoAnalyzer.php             Skor SEO focus keyword, metadata, & alt text gambar
app/Services/MediaUploadService.php              Upload media nama asli + varian WebP
app/Models/{SiteSetting,PostRevision}.php        Pengaturan homepage & maks. 2 revisi artikel
tests/Feature/FilamentAdminTest.php              Pest test panel admin, SEO analyzer, preview, revisi
tests/e2e/admin.spec.js                          Playwright E2E login & responsif panel admin
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

1b. Staging Hostinger (subdomain, PHP 8.3 web dan CLI, deploy awal; butuh approval)
10. Performa & deploy (caching halaman/query, verifikasi LCP/WebP, merge `main`)

## Catatan untuk Sesi Berikutnya

- Mulai sesi baru dari branch `feat/filament-admin` (atau merge branch fitur ke `main` terlebih dulu bila pemilik setuju).
- Di Playwright pada `laravel-dev`, jalankan `npx playwright test --workers=1` agar `php artisan serve` tidak timeout saat paralel.
- 5 post lama masih punya referensi `wp-content/uploads` karena 8 file sumber hilang di arsip `2026-11-12` (bisa dibersihkan via `/admin/posts`).
