# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 1 — Inisialisasi proyek selesai
- **Branch aktif**: `feat/init-project`
- **Fitur sesi terakhir**: Inisialisasi proyek Laravel 13 + Filament 5 + Tailwind v4 + Pest 4 + Playwright
- **Status**: selesai, seluruh tes (pint, Pest `HomeTest`, Playwright `home.spec.js`) & verifikasi manual (`/` dan `/admin`) lolos
- **Terakhir diperbarui**: 2026-11-17

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Staging Hostinger (1b) atau Skema DB Blog (2) — pilih salah satu saat mulai sesi
- **Tujuan**: Menyiapkan subdomain staging Hostinger (PHP 8.3 web & CLI) ATAU merancang migrasi skema DB (`posts`, `categories`, `tags`, `media`, `projects`).

## Keputusan yang Sudah Final

| Tanggal | Keputusan | Alasan |
|---|---|---|
| - | Stack: Laravel 13 + Blade + Tailwind v4 + Alpine + Filament 5 + MySQL | SEO baik, sederhana, disamakan dengan awork |
| 2026-11-12 | WP lama: `/%postname%/`, 235 artikel, URL artikel/kategori/tag dipertahankan persis; selebihnya 301 | Jaga SEO |
| 2026-11-10 | Domain `akutwibowo.com`; hosting Hostinger Premium; homepage bilingual ID/EN, blog ID | Target klien lokal + internasional |
| 2026-11-12 | Brand: Akut Wibowo (AW Orbit); arah visual sinematik gelap; token di `resources/css/app.css` | Identitas drone |

## Keputusan Terbuka (perlu jawaban pemilik)

- [ ] Host kanonik (`www.akutwibowo.com`), URL gambar lama `/wp-content/uploads/...`, metode bahasa (`/` + `/en/`), letak indeks blog (`/blog`).
- [ ] Cek Hostinger Premium: PHP 8.3 web & CLI (`/opt/alt/php83/usr/bin/php`), ekstensi, document root `public/`, cron, pemisahan dari `galangbahari.com`.

## Infrastruktur & Data Lama

- **Workspace lokal**: `D:\AntigravitySync\Project\AkutWibowo` (repo GitHub private `origin`: `akwibowo-lab/awblog`).
- **Hostinger**: WordPress lama di `~/domains/akutwibowo.com/public_html`, prefix `c71gfp8_` (abaikan `wpu0_*`).
- **Data WP lokal**: simpan di luar repo (`D:\wp-legacy`), hanya baca.

## Peta Repo Ringkas

```
app/Providers/Filament/AdminPanelProvider.php  Panel /admin
public/branding/                               aw-orbit-dark.svg, favicon.svg
resources/css/app.css                          Token brand Tailwind v4 + font Sora/Inter/JetBrains Mono
resources/views/welcome.blade.php              Placeholder /
tests/Feature/HomeTest.php                     Pest feature test GET /
tests/e2e/home.spec.js                         Playwright E2E test /
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

1b. Staging Hostinger (subdomain, PHP 8.3 web dan CLI, deploy awal; butuh approval)
2. Skema DB (`posts`, `categories`, `tags`, `media`, `projects`)
3. Impor WordPress (`c71gfp8_*`) ke staging `wp_legacy`
4. Impor media + penulisan ulang URL gambar
5. Halaman blog (daftar, detail, kategori, tag, pencarian)
6. SEO: sitemap, RSS, redirect 301, meta, OG
7. Homepage portofolio (hero, sertifikasi drone, galeri aplikasi, blog terbaru, kontak)
8. Halaman Proyek/Aplikasi (awork.id, ODM Studio App)
9. Admin Filament: kelola post, media, proyek, PDF portofolio
10. Performa & deploy

## Perintah Penting

```
php artisan serve
php artisan test --filter=<Fitur>
php vendor/bin/pint
npx playwright test
```

## Catatan untuk Sesi Berikutnya

- Data SQL `c71gfp8_*` dan arsip `uploads` (`akutwibowo-uploads-2026-11-12.tar.gz`) disiapkan pemilik di `D:\wp-legacy` sebelum sesi impor WP.
