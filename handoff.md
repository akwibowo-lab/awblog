# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 5 — Halaman blog publik (daftar, detail, kategori, tag, pencarian) selesai
- **Branch aktif**: `feat/blog-pages` (bercabang dari `feat/media-import`; siap merge ke `main` setelah pemilik setuju)
- **Fitur sesi terakhir**: Halaman blog publik (`/blog`, `/{slug}`, `/category/{slug}`, `/tag/{slug}`, pencarian `?q=`)
- **Status**: menunggu cek manual pemilik (visual, responsif, & artikel asli WordPress); `pint`, Pest (`20 passed`), dan Playwright (`4 passed`) lolos
- **Terakhir diperbarui**: 2026-11-22

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: SEO: sitemap, RSS, redirect 301, meta & Open Graph lanjutan
- **Tujuan**: Menambahkan `sitemap.xml` dinamis (post, kategori, tag, beranda), feed RSS (`/feed`), `robots.txt`, serta tabel/middleware redirect 301 untuk URL lama non-standar agar peringkat SEO WordPress terjaga.

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
routes/web.php                                   /, /blog, /category/{slug}, /tag/{slug}, /{slug}
app/Http/Controllers/BlogController.php          Controller tipis halaman blog publik
app/Http/Requests/BlogIndexRequest.php           Validasi query pencarian & paginasi
app/Actions/Blog/                                ListPublishedPosts, GetPublishedPost
app/Models/                                      Post (scopes/SEO/HTML), Media (variantUrl WebP), Category, Tag
lang/id/blog.php                                 Teks tampilan publik blog Bahasa Indonesia
resources/views/layouts/public.blade.php         Layout publik SEO/OG/navigasi/footer
resources/views/blog/                            index, show, partials/post-card, partials/pagination
tests/Feature/BlogPagesTest.php                  Pest test blog, SEO, filter, pencarian, anti N+1
tests/e2e/blog.spec.js                           Playwright E2E alur blog & responsif mobile
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

6. SEO: sitemap, RSS, redirect 301, meta, OG
1b. Staging Hostinger (subdomain, PHP 8.3 web dan CLI, deploy awal; butuh approval)
7. Homepage portofolio (hero, sertifikasi drone, galeri aplikasi, blog terbaru, kontak)
8. Halaman Proyek/Aplikasi (awork.id, ODM Studio App)
9. Admin Filament: kelola post, media, proyek, PDF portofolio
10. Performa & deploy

## Catatan untuk Sesi Berikutnya

- Mulai sesi baru dari branch `feat/blog-pages` setelah cek manual pemilik selesai (atau merge branch fitur ke `main` terlebih dulu bila pemilik setuju).
- Fitur UI berikutnya (Homepage portofolio, Halaman Proyek, Admin Filament) wajib menyertakan checklist cek manual pemilik sebelum finalisasi.
- Perubahan struktur URL/redirect, `robots.txt`, sitemap, atau kanonikal wajib approval eksplisit pemilik sesuai `AGENTS.md` bagian 6.
- 5 post lama masih punya referensi `wp-content/uploads` karena 8 file sumber hilang di arsip `2026-11-12` (bisa ditangani saat sesi SEO/redirect atau perbaikan konten).
