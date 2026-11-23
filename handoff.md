# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: Produksi aktif di `https://akutwibowo.com` (cutover selesai)
- **Branch aktif**: `feat/prod-cutover` (siap di-merge ke `main` setelah konfirmasi pemilik)
- **Fitur sesi terakhir**: Cutover DNS/Domain Produksi `akutwibowo.com` — backup DB WordPress produksi (`~/backups/wp-prod-20261123/wp_db.sql.gz`, 50 MB) & file WP `public_html` (`~/backups/wp-prod-20261123/public_html/`), setup `~/apps/awblog-prod` (`APP_ENV=production`, `APP_URL=https://akutwibowo.com`), tambah redirect 301 `www.akutwibowo.com` -> `https://akutwibowo.com` di `public/.htaccess`, symlink `public_html` ke `awblog-prod/public` tanpa menyentuh `new`, `parentguard`, `app`, `resume`, `ads.txt`, `.well-known`, tambah `scripts/deploy-prod.sh`, & verifikasi live (`HTTP/2 200` di `/`, `/blog`, `/sitemap.xml`, `/robots.txt`, `/admin/login`, `/ads.txt`, serta `301` dari `www`).
- **Status**: selesai (menunggu cek manual singkat pemilik di `https://akutwibowo.com` & `/admin`)
- **Terakhir diperbarui**: 2026-11-23

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Lengkapi data portofolio & pengaturan homepage via `/admin` produksi (`site_settings`, foto hero, proyek, PDF portofolio) + pisahkan DB produksi bila staging masih dipakai.
- **Tujuan**: Mengisi konten portofolio produksi dan merapikan isolasi DB/cron produksi di hPanel.

## Keputusan yang Sudah Final

| Tanggal | Keputusan | Alasan |
|---|---|---|
| - | Stack: Laravel 13 + Blade + Tailwind v4 + Alpine + Filament 5 + MariaDB | SEO baik, sederhana, selaras Hostinger & awork |
| 2026-11-12 | WP lama: `/%postname%/`, 235 artikel, URL artikel/kategori/tag dipertahankan persis; selebihnya 301 | Jaga SEO |
| 2026-11-10 | Domain `akutwibowo.com`; hosting Hostinger Premium; homepage bilingual ID/EN, blog ID | Target klien lokal + internasional |
| 2026-11-23 | Host kanonik produksi `https://akutwibowo.com` (non-www); `www.akutwibowo.com` redirect 301 ke non-www | Keputusan pemilik saat cutover |
| 2026-11-22 | Dev 1 SOT aktif di Proxmox LXC `CT 103 (laravel-dev)` IP `192.168.0.105` | Hemat RAM host 8 GB, multi-project Laravel via Remote SSH |
| 2026-11-23 | Produksi di `~/apps/awblog-prod`, staging di `~/apps/awblog-staging` (`new.akutwibowo.com`), symlink selektif di `public_html` | Isolasi app sambil menjaga `new`, `parentguard`, `app`, `resume`, `ads.txt` |

## Infrastruktur & Data Lama

- **Workspace SOT aktif (Remote SSH)**: `ssh://akut@laravel-dev/home/akut/projects/awblog` (`192.168.0.105`).
- **Produksi Hostinger**: `https://akutwibowo.com` (`~/apps/awblog-prod`, symlink selektif di `~/domains/akutwibowo.com/public_html`), DB `u735114786_newAW` (bersama staging sementara), PHP 8.3.37.
- **Backup WP pra-cutover**: `~/backups/wp-prod-20261123/wp_db.sql.gz` & `~/backups/wp-prod-20261123/public_html/` di Hostinger.
- **Staging Hostinger**: `https://new.akutwibowo.com` (`~/apps/awblog-staging`, `public_html/new`).
- **Repo GitHub**: `git@github.com:akwibowo-lab/awblog.git` (private).

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

- Isi/lengkapi data proyek portofolio & pengaturan homepage (`site_settings`, foto hero, PDF portofolio) lewat `https://akutwibowo.com/admin`
- Buat database MariaDB produksi terpisah (`u735114786_awblog`) di hPanel & cron `schedule:run` untuk `~/apps/awblog-prod`
- Merge `feat/prod-cutover` ke `main`

## Catatan untuk Sesi Berikutnya

- Jika ingin rollback ke WordPress: hapus 10 symlink Laravel di `public_html` lalu pindahkan kembali isi `~/backups/wp-prod-20261123/public_html/` ke `public_html`.
- Di Playwright pada `laravel-dev`, jalankan `npx playwright test --workers=1`.
