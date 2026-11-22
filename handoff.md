# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 2 — Skema DB inti + Lingkungan dev Proxmox (1 SOT) selesai
- **Branch aktif**: `feat/db-schema` (siap merge ke `main` setelah pemilik setuju)
- **Fitur sesi terakhir**: Setup LXC Proxmox `CT 103 (laravel-dev)` + migrasi repo `awblog` ke Remote SSH (menyusul Skema DB S2)
- **Status**: selesai; `pint`, Pest (`DatabaseSchemaTest` + `HomeTest`), Playwright Chromium E2E, dan SSH dari PC & laptop ke `laravel-dev` lolos
- **Terakhir diperbarui**: 2026-11-22

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Impor WordPress (`c71gfp8_*`) ke staging `wp_legacy`
- **Tujuan**: Menyiapkan database staging `wp_legacy` (hanya baca tabel prefix `c71gfp8_*`, abaikan `wpu0_*`) dan command/service impor artikel, kategori, tag, serta relasi ke skema Laravel dengan sanitasi HTML & pelestarian slug lama.

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

- **Workspace SOT (Remote SSH)**: `ssh://akut@laravel-dev/home/akut/projects/awblog` (`192.168.0.105`, MAC `BC:24:11:41:A0:77`; mobile Tailscale via `laravel-dev-ts` / `ProxyJump aw-server-ts`).
- **DB dev Proxmox**: MariaDB 10.11 lokal (`awblog_dev`, `awblog_test`, user `awblog`, password di `~/.awblog_db_pass`).
- **Repo GitHub**: `git@github.com:akwibowo-lab/awblog.git` (private, SSH key `akut@laravel-dev`).
- **Arsip lokal PC/laptop (pasif)**: `D:\AntigravitySync\Project\AkutWibowo` — tidak dipakai edit lagi.
- **Hostinger**: WordPress lama di `~/domains/akutwibowo.com/public_html`, prefix `c71gfp8_` (abaikan `wpu0_*`). Data WP disimpan di luar repo (`~/wp-legacy` di `laravel-dev`), hanya baca.

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

- Buka proyek via **Remote-SSH** ke `laravel-dev` (`/home/akut/projects/awblog`) dari PC maupun laptop (keduanya sudah terverifikasi ✔).
- Pastikan dump SQL `c71gfp8_*` dan arsip `uploads` (`akutwibowo-uploads-2026-11-12.tar.gz`) disalin ke `~/wp-legacy` di `laravel-dev` (di luar repo/document root) sebelum mulai impor WP.
