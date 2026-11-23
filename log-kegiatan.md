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


### S8 | 2026-11-22 | SEO: sitemap.xml dinamis, RSS feed /feed, robots.txt, dan redirect 301
- Hasil   : selesai
- Dikerjakan: buat migrasi & model `Redirect`, `ResolveLegacyRedirect`, middleware global `HandleLegacyRedirects` (aturan DB + pola WP lama `?p=`, `/YYYY/MM/DD/slug`, `/rss`, `/feed/rss2`, `/category|tag/.../feed`, `/author/...`), `SeoController` & view XML `/sitemap.xml` dan `/feed` (RSS 2.0), `<link rel="alternate">` di layout publik, perbarui `public/robots.txt`, serta tambah tes Pest & Playwright
- File utama: app/Http/Controllers/SeoController.php, app/Actions/Seo/ResolveLegacyRedirect.php, app/Http/Middleware/HandleLegacyRedirects.php, app/Models/Redirect.php, routes/web.php, public/robots.txt, tests/Feature/SeoTest.php, tests/e2e/seo.spec.js
- Tes     : pint ✔ | pest ✔ (26 passed) | e2e ✔ (5 passed) | manual n.a.
- Approval: tambah rute `/sitemap.xml` dan `/feed`, perbarui `public/robots.txt`, serta tambah tabel & middleware redirect 301 URL lama
- Lanjut  : Staging Hostinger (1b) atau Homepage portofolio (7)


### S9 | 2026-11-22 | Homepage Portofolio
- Hasil   : selesai
- Dikerjakan: bangun homepage `/` 5 bagian (Hero + AW Orbit `fetchpriority="high"`, Sertifikasi Drone editorial, Galeri Aplikasi `Project` + fallback `awork.id` & `ODM Studio App`, Blog Terbaru 3 artikel, Kontak WhatsApp & `mailto:`), smooth scroll + tombol back-to-top, token visual di `resources/css/app.css`, teks & SEO meta di `lang/id/home.php`, serta tes Pest & Playwright
- File utama: app/Http/Controllers/HomeController.php, app/Actions/Home/GetHomepageData.php, resources/views/home.blade.php, lang/id/home.php, tests/Feature/HomeTest.php, tests/e2e/home.spec.js
- Tes     : pint ✔ | pest ✔ (28 passed) | e2e ✔ (5 passed) | manual ✔
- Approval: -
- Lanjut  : Halaman Proyek/Aplikasi (8), Admin Filament (9, termasuk backend dinamis homepage & foto hero), atau Staging Hostinger (1b)


### S10 | 2026-11-22 | Halaman Proyek & Aplikasi (/proyek)
- Hasil   : selesai
- Dikerjakan: buat rute `/proyek` & `/proyek/{slug}` (kecualikan `proyek` pada `/{slug}`), `ProjectController`, `ListPublishedProjects` & `GetPublishedProject` (model `Project` + fallback `awork.id` & `ODM Studio App`, filter `?type=`), view `projects/index` & `projects/show`, menu nav `Proyek` (responsif 375px) & tautan dari homepage, `lang/id/projects.php`, serta tes Pest & Playwright
- File utama: routes/web.php, app/Http/Controllers/ProjectController.php, app/Actions/Projects/ListPublishedProjects.php, app/Actions/Projects/GetPublishedProject.php, resources/views/projects/index.blade.php, resources/views/projects/show.blade.php, tests/Feature/ProjectsTest.php, tests/e2e/projects.spec.js
- Tes     : pint ✔ | pest ✔ (32 passed) | e2e ✔ (6 passed) | manual ✔
- Approval: -
- Lanjut  : Admin Filament 5 (9, termasuk upload thumbnail proyek & foto hero homepage) atau Staging Hostinger (1b)


### S11 | 2026-11-22 | Admin Filament 5 & Backend Dinamis Homepage
- Hasil   : selesai
- Dikerjakan: konfigurasi `AdminPanelProvider` (logo AW Orbit, hook back-to-top) & `User::canAccessPanel`, buat 6 Resource (`Post`, `Category`, `Tag`, `Media`, `Project`, `Redirect`) + `MediaUploadService` (nama asli SEO + varian WebP) + `PostSeoAnalyzer` (focus keyword + alt text gambar), rute preview `/admin/preview/{posts,projects}`, sticky sidebar, auto-save draft + maks. 2 revisi artikel (`post_revisions`), serta `ManageHomepageSettings` (`site_settings`, foto hero/profil, PDF portofolio) yang terhubung ke `GetHomepageData` & `home.blade.php`
- File utama: app/Providers/Filament/AdminPanelProvider.php, app/Filament/Resources/Posts/PostResource.php, app/Filament/Resources/Projects/ProjectResource.php, app/Filament/Pages/ManageHomepageSettings.php, app/Services/Seo/PostSeoAnalyzer.php, app/Models/SiteSetting.php, tests/Feature/FilamentAdminTest.php, tests/e2e/admin.spec.js
- Tes     : pint ✔ | pest ✔ (40 passed) | e2e ✔ (7 passed, --workers=1) | manual ✔
- Approval: -
- Lanjut  : Staging Hostinger (1b, butuh approval) atau Performa & deploy (10)


### S12 | 2026-11-23 | Staging Hostinger (new.akutwibowo.com)
- Hasil   : selesai (menunggu propagasi instalasi SSL Hostinger untuk HTTPS Cloudflare)
- Dikerjakan: buat `.env.staging.example`, `scripts/deploy-staging.sh`, `docs/deploy-hostinger.md`, jadwal `queue:work --stop-when-empty` di `routes/console.php`, serta tes `StagingHealthTest` & `health.spec.js`; eksekusi deploy awal ke Hostinger (`~/apps/awblog-staging`, symlink `public_html/new`, `composer install --no-dev`, `APP_KEY`, `storage:link`, `migrate --force` ke `u735114786_newAW`, optimize cache & `schedule:run`) dengan origin `http://new.akutwibowo.com/` `200 OK` (PHP 8.3.37)
- File utama: .env.staging.example, scripts/deploy-staging.sh, docs/deploy-hostinger.md, routes/console.php, tests/Feature/StagingHealthTest.php, tests/e2e/health.spec.js
- Tes     : pint ✔ | pest ✔ (44 passed) | e2e ✔ (8 passed, --workers=1) | manual ✔ (DB & cron hPanel)
- Approval: approve pemilik untuk setup SSH/deploy ke `new.akutwibowo.com` dan `u735114786_newAW`
- Lanjut  : Performa & Deploy (10)
