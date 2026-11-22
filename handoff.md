# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 2 — Skema DB inti selesai
- **Branch aktif**: `feat/db-schema`
- **Fitur sesi terakhir**: Skema DB (`media`, `categories`, `tags`, `posts`, `projects`, `category_post`, `post_tag`) + Enum, Model, Relasi, Factory
- **Status**: selesai, seluruh tes (pint, Pest `DatabaseSchemaTest` + `HomeTest`, Playwright `home.spec.js`) lolos
- **Terakhir diperbarui**: 2026-11-22

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Setup lingkungan dev Proxmox + migrasi repo ke Remote SSH (1 Source of Truth)
- **Tujuan**: Merancang dan menyiapkan lingkungan development di Proxmox (multi-project Laravel: PHP 8.3, Composer, Node, MySQL/MariaDB, Playwright/Chromium, Git + SSH) agar laptop & PC bekerja via Remote SSH dari 1 SOT, lalu clone repo `awblog` ke sana. Mulai dengan diskusi arsitektur/topologi sebelum eksekusi.

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
app/Enums/                                     ContentStatus, ProjectType
app/Models/                                    User, Media, Category, Tag, Post, Project
database/migrations/                           media, categories, tags, posts, category_post, post_tag, projects
database/factories/                            MediaFactory, CategoryFactory, TagFactory, PostFactory, ProjectFactory
tests/Feature/DatabaseSchemaTest.php           Pest test skema DB, relasi, constraint, scope
tests/Feature/HomeTest.php                     Pest feature test GET /
tests/e2e/home.spec.js                         Playwright E2E test /
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

2a. Setup lingkungan dev Proxmox + Remote SSH (1 Source of Truth)
3. Impor WordPress (`c71gfp8_*`) ke staging `wp_legacy`
4. Impor media + penulisan ulang URL gambar
5. Halaman blog (daftar, detail, kategori, tag, pencarian)
1b. Staging Hostinger (subdomain, PHP 8.3 web dan CLI, deploy awal; butuh approval)
6. SEO: sitemap, RSS, redirect 301, meta, OG
7. Homepage portofolio (hero, sertifikasi drone, galeri aplikasi, blog terbaru, kontak)
8. Halaman Proyek/Aplikasi (awork.id, ODM Studio App)
9. Admin Filament: kelola post, media, proyek, PDF portofolio
10. Performa & deploy

## Catatan untuk Sesi Berikutnya

- Data SQL `c71gfp8_*` dan arsip `uploads` (`akutwibowo-uploads-2026-11-12.tar.gz`) disiapkan pemilik di `D:\wp-legacy` sebelum sesi impor WP.
