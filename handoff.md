# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 4 — Impor media + sinkronisasi penuh environment dev SOT (`laravel-dev`) selesai
- **Branch aktif**: `feat/media-import` (bercabang dari `feat/wp-import`; siap merge ke `main` setelah pemilik setuju)
- **Fitur sesi terakhir**: Sinkronisasi & setup penuh environment dev `awblog` di Proxmox `laravel-dev` (`192.168.0.105`)
- **Status**: selesai; SOT dev aktif di `laravel-dev` (60 tabel `c71gfp8_*` di `wp_legacy`, 242 post / 20 kategori / 471 tag, 1.477 media WebP, 238 featured linked, 197 post & 842 URL/lazy diperbarui, 8 file hilang); `pint`, Pest (`14 passed`), dan Playwright (`2 passed`) lolos
- **Terakhir diperbarui**: 2026-11-22

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Halaman blog (daftar, detail, kategori, tag, pencarian)
- **Tujuan**: Membangun halaman publik indeks blog (`/blog`), detail artikel (`/{slug}` mempertahankan URL lama), filter kategori & tag, dan pencarian, lengkap dengan SEO meta, Open Graph, kanonikal, dan gambar WebP `loading="lazy"`.

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
app/Console/Commands/ImportWordPressCommand.php  Command wp:import (--dry-run, --limit)
app/Console/Commands/ImportMediaCommand.php      Command wp:import-media (--archive, --dry-run)
app/Services/WpImport/                           HtmlSanitizer, WordPressImporter, MediaProcessor, MediaImporter, ContentUrlRewriter
app/Enums/ & app/Models/                         ContentStatus, ProjectType, User, Media, Category, Tag, Post, Project
config/database.php                              Koneksi wp_legacy (prefix c71gfp8_)
database/migrations/ & database/factories/       Skema konten inti + factory
tests/Feature/WpImportTest.php & MediaImportTest Pest test impor WP & media WebP
tests/e2e/home.spec.js & media-import.spec.js    Playwright E2E test / & /storage/media/*.webp
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

5. Halaman blog (daftar, detail, kategori, tag, pencarian)
1b. Staging Hostinger (subdomain, PHP 8.3 web dan CLI, deploy awal; butuh approval)
6. SEO: sitemap, RSS, redirect 301, meta, OG
7. Homepage portofolio (hero, sertifikasi drone, galeri aplikasi, blog terbaru, kontak)
8. Halaman Proyek/Aplikasi (awork.id, ODM Studio App)
9. Admin Filament: kelola post, media, proyek, PDF portofolio
10. Performa & deploy

## Catatan untuk Sesi Berikutnya

- SOT dev aktif penuh di `laravel-dev` (`/home/akut/projects/awblog`); mulai sesi baru langsung dari sini (buat branch `feat/blog-pages` atau merge `feat/wp-import` & `feat/media-import` ke `main` dulu bila pemilik setuju).
- 5 post masih memiliki referensi `wp-content/uploads` karena 8 file sumber tidak ada di arsip `2026-11-12` (bisa ditangani/fallback saat sesi halaman blog jika perlu).
