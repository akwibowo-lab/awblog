# handoff.md — Serah Terima Antar Sesi

> Ditimpa (bukan ditambah) di akhir setiap sesi. Maks. ±60 baris. Riwayat lama pindah ke `log-kegiatan.md`.

## Status Saat Ini

- **Fase**: Produksi aktif di `https://akutwibowo.com`
- **Branch aktif**: `main` (setelah merge `feat/language-switch`)
- **Fitur sesi terakhir**: Switch Language ID/EN di navbar publik untuk `/`, `/proyek`, dan `/proyek/{slug}` (tombol `#lang-toggle` action-oriented `EN`/`ID` senada `#theme-toggle`, cookie `awblog_locale` + auto-detect `CF-IPCountry` non-`ID` → `en`, blog tetap `id`, `lang/en/{blog,home,projects}.php`, `Project::localized*`, `SiteSetting::getHomepage`, serta dummy EN di `DevPortfolioSeeder`).
- **Status**: selesai di `laravel-dev` (Pint, Pest 57 passed, Playwright 3 passed) dan di-deploy ke produksi `https://akutwibowo.com`.
- **Terakhir diperbarui**: 2026-11-28

## Fitur Sesi Berikutnya (hanya 1)

- **Nama**: Pastikan cron `schedule:run` untuk `~/apps/awblog-prod` aktif di hPanel + pasang `GEMINI_API_KEY` di `.env` produksi bila belum.
- **Tujuan**: Menjamin queue scheduler berjalan rutin dan tombol Auto-Translate AI di `/admin` aktif di produksi.

## Keputusan yang Sudah Final

| Tanggal | Keputusan | Alasan |
|---|---|---|
| - | Stack: Laravel 13 + Blade + Tailwind v4 + Alpine + Filament 5 + MariaDB | SEO baik, sederhana, selaras Hostinger & awork |
| 2026-11-12 | WP lama: `/%postname%/`, 235 artikel, URL artikel/kategori/tag dipertahankan persis; selebihnya 301 | Jaga SEO |
| 2026-11-10 | Domain `akutwibowo.com`; hosting Hostinger Premium; homepage bilingual ID/EN, blog ID | Target klien lokal + internasional |
| 2026-11-23 | Host kanonik produksi `https://akutwibowo.com` (non-www); `www.akutwibowo.com` redirect 301 ke non-www | Keputusan pemilik saat cutover |
| 2026-11-22 | Dev 1 SOT aktif di Proxmox LXC `CT 103 (laravel-dev)` IP `192.168.0.105` | Hemat RAM host 8 GB, multi-project Laravel via Remote SSH |
| 2026-11-23 | Produksi di `~/apps/awblog-prod` (DB `u735114786_AWblog`); staging `new.akutwibowo.com` dibersihkan | Uji coba dilakukan di `laravel-dev`, hemat resource Hostinger |
| 2026-11-23 | Rasio tampilan gambar baru (hero, thumbnail proyek & blog) standar `16:9` (`aspect-video`) | Menghindari cropping pada aset 16:9 dari pemilik |
| 2026-11-23 | 2FA `/admin` memakai `AppAuthentication` bawaan Filament 5 (TOTP + recovery codes) dengan `profile(isSimple: false)` tanpa reset password web | Tanpa tambah dependency baru, aman & terintegrasi |
| 2026-11-28 | Tautan proyek bertipe (`web`, `github`, `playstore`, `download`) disimpan di `projects.links` (JSON) & auto-translate EN memakai Gemini API (`GEMINI_API_KEY`) | Ikon akurat per platform & draf terjemahan cepat di `/admin` |

## Infrastruktur & Data Lama

- **Workspace SOT aktif (Remote SSH)**: `ssh://akut@laravel-dev/home/akut/projects/awblog` (`192.168.0.105`).
- **Produksi Hostinger**: `https://akutwibowo.com` (`~/apps/awblog-prod`, DB `u735114786_AWblog`, symlink selektif di `~/domains/akutwibowo.com/public_html`), PHP 8.3.37.
- **Backup pra-isolasi**: `~/backups/wp-prod-20261123/` & `~/backups/awblog-newAW-20261123.sql.gz` di Hostinger.
- **Repo GitHub**: `git@github.com:akwibowo-lab/awblog.git` (private).

## Backlog (urut prioritas, jangan dikerjakan tanpa dijadwalkan)

- Pastikan cron `schedule:run` untuk `~/apps/awblog-prod` aktif di hPanel
- Verifikasi tampilan portofolio live di `https://akutwibowo.com` setelah isi data aktual

## Catatan untuk Sesi Berikutnya

- Jika fitur Auto-Translate AI di `/admin` belum aktif di produksi, tambahkan `GEMINI_API_KEY` + `GEMINI_MODEL=gemini-2.5-flash` di `~/apps/awblog-prod/.env`.
- Di Playwright pada `laravel-dev`, jalankan `npx playwright test --workers=1`.
