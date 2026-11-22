# log-kegiatan.md — Riwayat Sesi

> **Append-only.** Tambahkan 1 entri di bagian paling bawah di akhir setiap sesi. Jangan edit entri lama. Agent hanya membaca 5 entri terakhir.

## Format Entri

```
### S<nomor> | <YYYY-MM-DD> | <fitur>
- Hasil   : selesai / sebagian / diblokir
- Dikerjakan: <1–3 poin singkat>
- File utama: <maks. 5 path>
- Tes     : pint ✔/✘ | pest ✔/✘ | e2e ✔/✘ | manual ✔/✘/n.a.
- Approval: <aksi kritis yang di-approve, atau ->
- Lanjut  : <1 baris>
```

---

### S0 | <tanggal> | Perencanaan
- Hasil   : selesai
- Dikerjakan: blueprint, struktur file agent (AGENTS, handoff, log, skills)
- File utama: AGENTS.md, handoff.md, log-kegiatan.md, skills.md
- Tes     : n.a.
- Approval: -
- Lanjut  : diskusi blueprint → mockup & branding → S1 inisialisasi proyek

### S1 | 2026-11-16 | Inisialisasi proyek (percobaan pertama)
- Hasil   : hilang
- Dikerjakan: sesi 1 selesai di lokal, lalu drive D laptop terhapus sebelum sempat di-push/sinkron; recovery oleh pemilik gagal
- File utama: -
- Tes     : n.a.
- Approval: -
- Lanjut  : bangun ulang sesi 1 dari awal (repo GitHub private dulu, push di akhir sesi)


### S1 | 2026-11-17 | Inisialisasi proyek Laravel + Filament + Tailwind
- Hasil   : selesai
- Dikerjakan: setup Laravel 13, Filament 5 (/admin), Pest 4, Playwright, Tailwind v4 token brand & placeholder `/`
- File utama: composer.json, .env.example, resources/css/app.css, resources/views/welcome.blade.php, tests/Feature/HomeTest.php
- Tes     : pint ✔ | pest ✔ | e2e ✔ | manual ✔
- Approval: bersihkan folder sisa parsial; repo GitHub origin; dependensi sesi (laravel 13, filament 5, pest, playwright)
- Lanjut  : staging Hostinger (1b) atau skema DB blog (2)


### S2 | 2026-11-22 | Skema DB (`posts`, `categories`, `tags`, `media`, `projects`) + Model & Relasi
- Hasil   : selesai
- Dikerjakan: 7 migrasi skema konten & pivot WP-ready, 2 enum (`ContentStatus`, `ProjectType`), 5 model + relasi `User::posts()`, 5 factory, dan tes Pest `DatabaseSchemaTest`
- File utama: database/migrations/2026_11_22_000001..000007_*.php, app/Models/Post.php, app/Models/Project.php, app/Enums/ContentStatus.php, tests/Feature/DatabaseSchemaTest.php
- Tes     : pint ✔ | pest ✔ (9 passed) | e2e ✔ (1 passed) | manual n.a.
- Approval: -
- Lanjut  : Impor WordPress (`c71gfp8_*`) ke staging `wp_legacy` (atau Staging Hostinger 1b)


### S3 | 2026-11-22 | Setup lingkungan dev Proxmox + migrasi repo ke Remote SSH
- Hasil   : selesai
- Dikerjakan: buat LXC CT 103 `laravel-dev` (`192.168.0.105`, Ubuntu 24.04, 4 vCPU, 4 GB RAM, 30 GB disk), install PHP 8.3.6 + Composer 2.11 + Node 22 + MariaDB 10.11 + Playwright Chromium, setup SSH key-only + GitHub SSH key + akses PC & laptop, clone & verifikasi `awblog`
- File utama: handoff.md, log-kegiatan.md
- Tes     : pint ✔ | pest ✔ | e2e ✔ | manual n.a.
- Approval: inspeksi read-only `aw-server` & `hostinger`; pembuatan & provisioning CT 103; static IP `192.168.0.105`; clone & install dependensi `awblog`
- Lanjut  : Impor WordPress (`c71gfp8_*`) ke staging `wp_legacy` via Remote SSH di `laravel-dev`


### S4 | 2026-11-22 | Impor WordPress (`c71gfp8_*`) ke staging `wp_legacy`
- Hasil   : selesai
- Dikerjakan: ekstrak & impor 60 tabel `c71gfp8_*` saja ke `wp_legacy`, tambah koneksi `wp_legacy`, buat `HtmlSanitizer` + `WordPressImporter` + command `wp:import`, uji impor penuh (20 kategori, 471 tag, 242 post, 0 skipped)
- File utama: config/database.php, app/Services/WpImport/HtmlSanitizer.php, app/Services/WpImport/WordPressImporter.php, app/Console/Commands/ImportWordPressCommand.php, tests/Feature/WpImportTest.php
- Tes     : pint ✔ | pest ✔ (12 passed) | e2e ✔ (1 passed) | manual n.a.
- Approval: nyalakan MySQL lokal, buat DB `wp_legacy`, dan impor 60 tabel `c71gfp8_*` dari `D:\wp-legacy\u735114786_AwKdm2025.sql`
- Lanjut  : Impor media + penulisan ulang URL gambar


### S5 | 2026-11-22 | Impor media + penulisan ulang URL gambar
- Hasil   : selesai
- Dikerjakan: buat `MediaProcessor` (varian thumbnail/medium/large WebP via GD), `MediaImporter`, `ContentUrlRewriter`, & command `wp:import-media`; buat symlink `public/storage`; jalankan impor penuh (1.477 media, 238 featured linked, 197 post, 842 URL/lazy, 8 file hilang)
- File utama: app/Services/WpImport/MediaProcessor.php, app/Services/WpImport/MediaImporter.php, app/Services/WpImport/ContentUrlRewriter.php, app/Console/Commands/ImportMediaCommand.php, tests/Feature/MediaImportTest.php
- Tes     : pint ✔ | pest ✔ (14 passed) | e2e ✔ (2 passed) | manual n.a.
- Approval: tulis media & varian WebP ke `storage/app/public/media/`, `storage:link`, serta perbarui `media`, `posts.featured_media_id`, dan `posts.content_html`
- Lanjut  : Halaman blog (daftar, detail, kategori, tag, pencarian)


### S6 | 2026-11-22 | Sinkronisasi & setup penuh environment dev di Proxmox laravel-dev
- Hasil   : selesai
- Dikerjakan: sinkron branch `feat/media-import` di `laravel-dev` (`192.168.0.105`), buat DB `wp_legacy` & impor 60 tabel `c71gfp8_*` saja dari `~/wp-legacy/u735114786_AwKdm2025.sql`, jalankan `migrate`, `wp:import` (242 post, 20 kategori, 471 tag), `storage:link`, dan `wp:import-media` (1.477 media WebP, 238 featured, 197 post, 842 URL/lazy, 8 file hilang)
- File utama: handoff.md, log-kegiatan.md
- Tes     : pint ✔ | pest ✔ (14 passed) | e2e ✔ (2 passed) | manual n.a.
- Approval: tambah variabel `WP_LEGACY_DB_*` di `.env`, buat DB `wp_legacy` + grant `SELECT` ke `awblog`, ekstrak & impor 60 tabel `c71gfp8_*`, jalankan `migrate`, `wp:import`, `storage:link`, dan `wp:import-media`
- Lanjut  : Halaman blog (daftar, detail, kategori, tag, pencarian)


### S7 | 2026-11-22 | Halaman blog publik (daftar, detail, kategori, tag, pencarian)
- Hasil   : selesai
- Dikerjakan: buat rute `/blog`, `/category/{slug}`, `/tag/{slug}`, `/{slug}` (mempertahankan URL WP), `BlogController`, `BlogIndexRequest`, `ListPublishedPosts` (13 post hal. 1 / 12 post hal. 2+) & `GetPublishedPost`, `ShortcodeCleaner` + `wp:clean-content` (bersihkan shortcode & 18 gambar broken), toggle mode gelap/terang, layout & view blog bertoken brand, serta tes Pest & Playwright
- File utama: routes/web.php, app/Http/Controllers/BlogController.php, app/Actions/Blog/ListPublishedPosts.php, app/Services/WpImport/ShortcodeCleaner.php, tests/Feature/BlogPagesTest.php
- Tes     : pint ✔ | pest ✔ (22 passed) | e2e ✔ (4 passed) | manual ✔
- Approval: -
- Lanjut  : SEO (sitemap, RSS, redirect 301, robots.txt) atau staging Hostinger (1b)
