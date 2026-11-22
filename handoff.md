# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 3 — Impor WordPress (`c71gfp8_*`) ke staging `wp_legacy` selesai
- **Branch aktif**: `feat/wp-import` (siap merge ke `main` setelah pemilik setuju)
- **Fitur sesi terakhir**: Impor WordPress (`c71gfp8_*`) ke staging `wp_legacy` & command `wp:import`
- **Status**: selesai; `pint`, Pest (`12 passed`), Playwright (`1 passed`), dan impor nyata `wp_legacy` (60 tabel `c71gfp8_*`, 20 kategori, 471 tag, 242 post, 0 skipped) lolos
- **Terakhir diperbarui**: 2026-11-22

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Impor media + penulisan ulang URL gambar
- **Tujuan**: Mengekstrak arsip `akutwibowo-uploads-2026-11-12.tar.gz`, memproses/mengimpor media ke `storage` (thumbnail/medium/large + WebP), menghubungkan `featured_media_id`, dan menulis ulang URL `/wp-content/uploads/...` di `posts.content_html`.

## Keputusan yang Sudah Final

| Tanggal | Keputusan | Alasan |
|---|---|---|
| - | Stack: Laravel 13 + Blade + Tailwind v4 + Alpine + Filament 5 + MariaDB | SEO baik, sederhana, selaras Hostinger & awork |
| 2026-11-12 | WP lama: `/%postname%/`, 235 artikel, URL artikel/kategori/tag dipertahankan persis; selebihnya 301 | Jaga SEO |
| 2026-11-10 | Domain `akutwibowo.com`; hosting Hostinger Premium; homepage bilingual ID/EN, blog ID | Target klien lokal + internasional |
| 2026-11-12 | Brand: Akut Wibowo (AW Orbit); arah visual sinematik gelap; token di `resources/css/app.css` | Identitas drone |
| 2026-11-22 | Dev 1 SOT di Proxmox LXC `CT 103 (laravel-dev)` IP `192.168.0.105` (Ubuntu 24.04, 4 vCPU, 4 GB RAM, 30 GB disk) | Hemat RAM host 8 GB, multi-project Laravel via Remote SSH |

## Keputusan Terbuka (perlu jawaban pemilik)

- [ ] Host kanonik (`www.akutwibowo.com`), URL gambar lama `/wp-content/uploads/...`, metode bahasa (`/` + `/en/`), letak indeks blog (`/blog`).
- [ ] Cek Hostinger Premium detail: document root `public/`, cron, pemisahan dari `galangbahari.com` (PHP 8.3.37 CLI `/opt/alt/php83/usr/bin/php` & MariaDB 11.8.9 sudah terkonfirmasi ✔).

## Infrastruktur & Data Lama

- **Workspace SOT (Remote SSH)**: `ssh://akut@laravel-dev/home/akut/projects/awblog` (`192.168.0.105`).
- **DB dev**: MariaDB/MySQL lokal (`akutwibowo` / `awblog_dev`) + staging `wp_legacy` (berisi 60 tabel prefix `c71gfp8_*` saja).
- **Repo GitHub**: `git@github.com:akwibowo-lab/awblog.git` (private).
- **Hostinger**: WordPress lama di `~/domains/akutwibowo.com/public_html`, prefix `c71gfp8_*` (abaikan `wpu0_*`). Data WP disimpan di luar repo (`D:\wp-legacy` / `~/wp-legacy`), hanya baca.

## Peta Repo Ringkas

```
app/Console/Commands/ImportWordPressCommand.php  Command artisan wp:import (--dry-run, --limit)
app/Services/WpImport/                           HtmlSanitizer, ImportResult, WordPressImporter
app/Enums/ & app/Models/                         ContentStatus, ProjectType, User, Media, Category, Tag, Post, Project
config/database.php                              Koneksi wp_legacy (prefix c71gfp8_)
database/migrations/ & database/factories/       Skema konten inti + factory
tests/Feature/WpImportTest.php                   Pest test sanitasi HTML, impor idempoten, dry-run
tests/Feature/DatabaseSchemaTest.php & HomeTest  Pest test skema DB & /
tests/e2e/home.spec.js                           Playwright E2E test /
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

4. Impor media + penulisan ulang URL gambar
5. Halaman blog (daftar, detail, kategori, tag, pencarian)
1b. Staging Hostinger (subdomain, PHP 8.3 web dan CLI, deploy awal; butuh approval)
6. SEO: sitemap, RSS, redirect 301, meta, OG
7. Homepage portofolio (hero, sertifikasi drone, galeri aplikasi, blog terbaru, kontak)
8. Halaman Proyek/Aplikasi (awork.id, ODM Studio App)
9. Admin Filament: kelola post, media, proyek, PDF portofolio
10. Performa & deploy

## Catatan untuk Sesi Berikutnya

- Merge `feat/wp-import` ke `main` bila pemilik setuju.
- Pastikan `akutwibowo-uploads-2026-11-12.tar.gz` tersedia di `D:\wp-legacy` (atau `~/wp-legacy` bila via Remote SSH `laravel-dev`) sebelum sesi impor media.
