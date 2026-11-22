# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: 7 — Homepage Portofolio selesai
- **Branch aktif**: `feat/homepage-portfolio` (bercabang dari `feat/seo-redirects`; siap merge ke `main` setelah pemilik setuju)
- **Fitur sesi terakhir**: Homepage Portofolio (`/`)
- **Status**: selesai; 5 bagian (Hero dengan logo AW Orbit `fetchpriority="high"` & CTA WhatsApp `wa.me/6289623084000`, Sertifikasi Drone editorial, Galeri Aplikasi `Project` + fallback `awork.id` & `ODM Studio App`, Blog Terbaru 3 artikel `post-card`, Kontak WhatsApp + `mailto:akwibowo@gmail.com` tanpa teks email eksplisit + RSS), smooth scroll + tombol back-to-top, `lang/id/home.php`, SEO meta; `pint`, Pest (`28 passed`), dan Playwright (`5 passed`) ✔
- **Terakhir diperbarui**: 2026-11-22

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Halaman Proyek/Aplikasi (8) atau Admin Filament (9) atau Staging Hostinger (1b)
- **Tujuan**: Membangun halaman detail/indeks Proyek (`/proyek` untuk `awork.id`, `ODM Studio App`), atau Admin Filament 5 (kelola post, media, proyek, PDF portofolio, redirects, serta konten dinamis homepage portofolio termasuk foto hero), atau deploy awal staging Hostinger (butuh approval).

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
routes/web.php                                   /, /sitemap.xml, /feed, /blog, /category/{slug}, /tag/{slug}, /{slug}
app/Http/Controllers/HomeController.php          Controller tipis homepage portofolio
app/Actions/Home/GetHomepageData.php             Ambil proyek terbit, 3 post terbaru, PDF portofolio
resources/views/home.blade.php                   Homepage 5 bagian + back-to-top
lang/id/home.php                                 Teks & meta SEO homepage Bahasa Indonesia
app/Http/Controllers/BlogController.php          Controller tipis halaman blog publik
app/Http/Controllers/SeoController.php           Endpoint XML sitemap.xml & RSS feed /feed
app/Http/Middleware/HandleLegacyRedirects.php    Middleware global redirect 301 URL lama
tests/Feature/HomeTest.php                       Pest test homepage portofolio & SEO meta
tests/e2e/home.spec.js                           Playwright E2E homepage (desktop, mobile, back-to-top)
```

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

1b. Staging Hostinger (subdomain, PHP 8.3 web dan CLI, deploy awal; butuh approval)
8. Halaman Proyek/Aplikasi (`awork.id`, ODM Studio App)
9. Admin Filament: kelola post, media, proyek, PDF portofolio, redirects, serta backend dinamis seluruh konten homepage portofolio (hero + foto profil, sertifikasi drone, galeri aplikasi, kontak)
10. Performa & deploy

## Catatan untuk Sesi Berikutnya

- Mulai sesi baru dari branch `feat/homepage-portfolio` (atau merge branch fitur ke `main` terlebih dulu bila pemilik setuju).
- Fitur UI berikutnya (Halaman Proyek, Admin Filament) wajib menyertakan checklist cek manual pemilik sebelum finalisasi.
- 5 post lama masih punya referensi `wp-content/uploads` karena 8 file sumber hilang di arsip `2026-11-12` (bisa dibersihkan di sesi perbaikan konten atau admin).
