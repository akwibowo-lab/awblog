# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 8 — Halaman Proyek/Aplikasi selesai
- **Branch aktif**: `feat/projects-page` (bercabang dari `feat/homepage-portfolio`; siap merge ke `main` setelah pemilik setuju)
- **Fitur sesi terakhir**: Halaman Proyek/Aplikasi (`/proyek` & `/proyek/{slug}`)
- **Status**: selesai; rute `/proyek` & `/proyek/{slug}`, filter kategori (`app`, `drone`, `case_study`), empty state, fallback `awork.id` & `ODM Studio App` + dukungan penuh model `Project` + `featuredMedia`, nav header `Proyek`, tautan dari homepage, `lang/id/projects.php`, `pint`, Pest (`32 passed`), Playwright (`6 passed`), dan cek manual ✔
- **Terakhir diperbarui**: 2026-11-22

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Admin Filament 5 (9) atau Staging Hostinger (1b)
- **Tujuan**: Membangun Admin Filament 5 (CRUD post, kategori, tag, media, proyek termasuk upload thumbnail `featured_media_id`, PDF portofolio, redirects, serta backend dinamis konten homepage termasuk foto hero/profil), atau deploy awal staging Hostinger (butuh approval).

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
routes/web.php                                   /, /proyek, /proyek/{slug}, /sitemap.xml, /feed, /blog, /category/{slug}, /tag/{slug}, /{slug}
app/Http/Controllers/HomeController.php          Controller tipis homepage portofolio
app/Http/Controllers/ProjectController.php       Controller tipis halaman /proyek & /proyek/{slug}
app/Actions/Projects/ListPublishedProjects.php   Daftar proyek terbit + filter tipe + fallback awork.id & ODM Studio App
app/Actions/Projects/GetPublishedProject.php     Detail proyek terbit + proyek terkait
resources/views/projects/{index,show}.blade.php  Indeks filterable & detail proyek
lang/id/projects.php                             Teks UI & meta SEO halaman proyek
tests/Feature/ProjectsTest.php                   Pest test /proyek & /proyek/{slug}
tests/e2e/projects.spec.js                       Playwright E2E proyek (filter, detail, tema, mobile 375px)
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

1b. Staging Hostinger (subdomain, PHP 8.3 web dan CLI, deploy awal; butuh approval)
9. Admin Filament: kelola post, media, proyek (upload thumbnail), PDF portofolio, redirects, serta backend dinamis seluruh konten homepage portofolio (hero + foto profil, sertifikasi drone, galeri aplikasi, kontak)
10. Performa & deploy

## Catatan untuk Sesi Berikutnya

- Mulai sesi baru dari branch `feat/projects-page` (atau merge branch fitur ke `main` terlebih dulu bila pemilik setuju).
- Fitur UI berikutnya (Admin Filament) wajib menyertakan checklist cek manual pemilik sebelum finalisasi.
- 5 post lama masih punya referensi `wp-content/uploads` karena 8 file sumber hilang di arsip `2026-11-12` (bisa dibersihkan di sesi perbaikan konten atau admin).
