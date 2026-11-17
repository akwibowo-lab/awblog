# skills.md — Playbook Prosedur Baku

> Agent: baca **hanya bagian skill yang dibutuhkan sesi ini**, bukan seluruh file. Aturan umum tetap di `AGENTS.md`.
> Tanda 🔒 = langkah yang butuh approval pemilik.

---

## SKILL 1 — Sesi Fitur Baru (alur standar)

1. Baca `handoff.md` + 5 entri terakhir `log-kegiatan.md`.
2. Buat branch `feat/<nama>`.
3. Tulis rencana ≤10 baris (file disentuh, skema, route, tes). Tunggu "lanjut".
4. Implementasi bertahap: migrasi/model → action/service → controller/route → view → Filament (bila ada).
5. Tulis tes Pest untuk logika, 1 spec Playwright untuk alur utama.
6. Jalankan: `pint` → `php artisan test --filter=<Fitur>` → `npx playwright test <spec>`.
7. Jika perlu cek manual: buat checklist di `handoff.md`, berhenti, tunggu pemilik.
8. Tulis ulang `handoff.md`, tambah 1 entri `log-kegiatan.md`, commit.

## SKILL 2 — Migrasi Database (skema Laravel baru)

1. Satu migrasi = satu tujuan. Nama jelas (`create_posts_table`).
2. Sertakan index untuk `slug` (unik), `published_at`, foreign key.
3. Selalu tulis `down()` yang benar.
4. Uji lokal: `migrate` → `migrate:rollback` → `migrate`.
5. 🔒 Menjalankan di database non-lokal / mengubah kolom berisi data.

## SKILL 3 — Impor WordPress ke Laravel

Prinsip: **baca-saja dari WP, tulis ke skema Laravel, idempoten (bisa diulang aman).**

1. 🔒 Minta pemilik menyediakan dump SQL + folder `wp-content/uploads`. Impor dump ke database staging `wp_legacy` (lokal).
2. Audit dulu, laporkan ringkas: jumlah post/page, kategori, tag, komentar, lampiran, plugin SEO yang dipakai, struktur permalink, shortcode yang ada.
3. Buat command `php artisan wp:import {--only=} {--dry-run}`. Wajib mendukung `--dry-run` (hanya melaporkan).
4. Pemetaan:
   - `wp_posts` (`post_type=post`, `status=publish`) → `posts`
   - `post_name` → `slug` (pertahankan persis)
   - `post_date_gmt` → `published_at`
   - `wp_terms` + `term_taxonomy` + `term_relationships` → `categories`, `tags`, pivot
   - `_thumbnail_id` (postmeta) → gambar unggulan
   - meta SEO (Yoast/RankMath) → `meta_title`, `meta_description`
   - `wp_users` → penulis (cukup nama; **jangan** impor hash password)
5. Simpan `wp_id` pada tiap baris agar impor idempoten (`updateOrCreate` berdasar `wp_id`).
6. Konten: hapus komentar blok Gutenberg (`<!-- wp:... -->`), pertahankan HTML, sanitasi (whitelist tag), tangani shortcode (laporkan yang tak dikenal, jangan dibuang diam-diam).
7. Laporan akhir: jumlah diimpor / dilewati / gagal + daftar slug bermasalah.
8. 🔒 Menjalankan impor ke database yang bukan lokal.

## SKILL 4 — Impor Media & Penulisan Ulang URL Gambar

1. Salin `uploads/` ke storage Laravel **tanpa menimpa** (🔒 bila ada file yang akan tertimpa).
2. Petakan URL lama (`/wp-content/uploads/YYYY/MM/file.jpg`) → URL baru. Simpan pemetaan di tabel `media`.
3. Tulis ulang `src`, `srcset`, dan tautan `<a>` di konten. Pertahankan `alt`.
4. Hapus suffix ukuran WP (`-300x200`) saat mencari file asli; hasilkan turunan baru.
5. Konversi WebP + ukuran responsif lewat queue/command terpisah (bukan saat impor).
6. Laporkan gambar yang hilang/rusak (daftar path), jangan gagal diam-diam.
7. Verifikasi: sampel 10 artikel acak, semua gambar tampil (E2E memeriksa tidak ada gambar 404).

## SKILL 5 — Redirect & SEO

1. Bandingkan daftar URL lama (dari audit) dengan URL baru. Semua yang beda → redirect 301 (tabel `redirects` atau file peta).
2. Sitemap XML, RSS, `robots.txt`, canonical, OG/Twitter card.
3. Tes: loop semua URL lama → status 200 atau 301→200, tanpa rantai redirect.
4. 🔒 Mengubah aturan redirect/robots/sitemap di produksi.

## SKILL 6 — Halaman Publik (Blade + Tailwind)

1. Mobile-first, lalu tablet, lalu desktop.
2. Komponen Blade kecil dan dipakai ulang (`x-card-project`, `x-hero`, `x-post-card`).
3. Satu sumber token desain (warna, font, spasi) di `tailwind.config` / CSS variables, mengacu dokumen branding.
4. Cek: heading berurutan, `alt` gambar, kontras, ukuran target sentuh ≥44px.
5. Performa: gambar hero dioptimasi, font di-preload seperlunya, tanpa JS berlebih.
6. Beri checklist manual visual ke pemilik (mobile + desktop).

## SKILL 7 — Admin Filament

1. Satu Resource per entitas (Post, Category, Tag, Project).
2. Rich editor untuk konten; upload media langsung ke pustaka media.
3. Validasi slug unik; pratinjau sebelum publish; status draft/terbit/terjadwal.
4. Otorisasi: hanya pemilik yang bisa akses panel.

## SKILL 8 — Menulis Tes E2E (Playwright)

1. Satu spec per alur utama, nama jelas (`blog-detail.spec.ts`).
2. Gunakan selector stabil (`data-testid`, role), hindari selector CSS rapuh.
3. Data uji dari seeder/factory, bukan data produksi.
4. Cek: halaman terbuka, elemen kunci tampil, tidak ada error konsol, tidak ada request 404.
5. Jalankan hanya spec fitur ini; suite penuh di akhir fitur.

## SKILL 9 — Debugging Hemat Token

1. Reproduksi dulu, tulis gejala 1–2 baris.
2. Baca log terakhir saja (`tail -n 50 storage/logs/laravel.log`).
3. Hipotesis → uji paling murah dulu. Maks. 2 percobaan sebelum melapor (lihat `AGENTS.md` §9).
4. Setelah ketemu: perbaikan minimal + tes yang mencegah kambuh.

## SKILL 10 — Akhir Sesi

1. Pastikan Definition of Done (`AGENTS.md` §5) terpenuhi, atau catat apa yang belum.
2. Timpa `handoff.md` (status, fitur berikut, keputusan baru, backlog).
3. Tambah 1 entri `log-kegiatan.md`.
4. Commit. Beri pemilik ringkasan ≤5 baris dan satu-satunya hal yang perlu ia lakukan berikutnya.

## SKILL 11 — Deploy (hanya 🔒)

1. Semua langkah butuh approval. Siapkan checklist: backup DB + uploads, `.env` produksi, `migrate --force`, cache config/route/view, symlink storage, permission, queue/scheduler.
2. Uji pasca-deploy: halaman utama, 5 artikel acak, gambar, sitemap, 5 redirect lama.
3. Siapkan rencana rollback sebelum mulai.
