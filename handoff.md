# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 6 — SEO (sitemap.xml dinamis, RSS feed `/feed`, `robots.txt`, redirect 301) selesai
- **Branch aktif**: `feat/seo-redirects` (bercabang dari `feat/blog-pages`; siap merge ke `main` setelah pemilik setuju)
- **Fitur sesi terakhir**: SEO (`sitemap.xml`, `/feed`, `robots.txt`, dan sistem redirect 301 URL lama)
- **Status**: selesai; `/sitemap.xml` dinamis (beranda, `/blog`, post terbit, kategori & tag aktif), `/feed` RSS 2.0 (20 artikel terbaru + `<link rel="alternate">` di layout), `public/robots.txt`, tabel & model `Redirect` + middleware global `HandleLegacyRedirects` (aturan DB + pola WP lama `?p=`, `/YYYY/MM/DD/slug`, `/rss`, `/feed/rss2`, `/category|tag/.../feed`, `/author/...`); `pint`, Pest (`26 passed`), dan Playwright (`5 passed`) ✔
- **Terakhir diperbarui**: 2026-11-22

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Staging Hostinger (1b) atau Homepage Portofolio (7)
- **Tujuan**: Menyiapkan deploy awal ke subdomain staging Hostinger (PHP 8.3 web & CLI, DB, cron) dengan approval pemilik, atau membangun homepage portofolio (hero, sertifikasi drone, galeri aplikasi, blog terbaru, kontak).

## Keputusan yang Sudah Final

| Tanggal | Keputusan | Alasan |
|---|---|---|
| - | Stack: Laravel 13 + Blade + Tailwind v4 + Alpine + Filament 5 + MariaDB | SEO baik, sederhana, selaras Hostinger & awork |
| 2026-11-12 | WP lama: `/%postname%/`, 235 artikel, URL artikel/kategori/tag dipertahankan persis; selebihnya 301 | Jaga SEO |
| 2026-11-10 | Domain `akutwibowo.com`; hosting Hostinger Premium; homepage bilingual ID/EN, blog ID | Target klien lokal + internasional |
| 2026-11-12 | Brand: Akut Wibowo (AW Orbit); arah visual sinematik gelap; token di `resources/css/app.css` | Identitas drone |
| 2026-11-22 | Dev 1 SOT aktif di Proxmox LXC `CT 103 (laravel-dev)` IP `192.168.0.105` (Ubuntu 24.04, 4 vCPU, 4 GB RAM, 30 GB disk) | Hemat RAM host 8 GB, multi-project Laravel via Remote SSH |

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
routes/web.php                                   /, /sitemap.xml, /feed, /blog, /category/{slug}, /tag/{slug}, /{slug}
app/Http/Controllers/BlogController.php          Controller tipis halaman blog publik
app/Http/Controllers/SeoController.php           Endpoint XML sitemap.xml & RSS feed /feed
app/Http/Middleware/HandleLegacyRedirects.php    Middleware global redirect 301 URL lama
app/Actions/Seo/ResolveLegacyRedirect.php        Resolver tabel redirects + pola WP lama
app/Models/                                      Post, Media, Category, Tag, Redirect
resources/views/seo/                             sitemap.blade.php, feed.blade.php
public/robots.txt                                Aturan crawler + Sitemap URL
tests/Feature/SeoTest.php                        Pest test sitemap, RSS, dan redirect 301
tests/e2e/seo.spec.js                            Playwright E2E sitemap, RSS, robots.txt, 301
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

1b. Staging Hostinger (subdomain, PHP 8.3 web dan CLI, deploy awal; butuh approval)
7. Homepage portofolio (hero, sertifikasi drone, galeri aplikasi, blog terbaru, kontak)
8. Halaman Proyek/Aplikasi (awork.id, ODM Studio App)
9. Admin Filament: kelola post, media, proyek, PDF portofolio, redirects
10. Performa & deploy

## Catatan untuk Sesi Berikutnya

- Mulai sesi baru dari branch `feat/seo-redirects` (atau merge branch fitur ke `main` terlebih dulu bila pemilik setuju).
- Fitur UI berikutnya (Homepage portofolio, Halaman Proyek, Admin Filament) wajib menyertakan checklist cek manual pemilik sebelum finalisasi.
- 5 post lama masih punya referensi `wp-content/uploads` karena 8 file sumber hilang di arsip `2026-11-12` (bisa dibersihkan di sesi perbaikan konten atau admin).
