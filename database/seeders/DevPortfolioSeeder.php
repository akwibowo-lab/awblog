<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\ProjectType;
use App\Models\Media;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Services\Cache\ContentCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DevPortfolioSeeder extends Seeder
{
    /**
     * Populate full dummy portfolio data, 16:9 WebP media, hero image, and PDF for laravel-dev.
     */
    public function run(): void
    {
        $disk = Storage::disk('public');

        $heroPath = 'homepage/hero-drone-dummy.webp';
        $this->createWebpBanner($disk->path($heroPath), 1280, 720, [17, 27, 46], [245, 165, 36]);

        $pdfPath = 'portfolio/akut-wibowo-portfolio.pdf';
        $this->createDummyPdf($disk->path($pdfPath));

        SiteSetting::saveHomepage([
            ...SiteSetting::homepageDefaults(),
            'hero_image_path' => $heroPath,
            'hero_image_alt' => 'Foto profil pilot drone Akut Wibowo di lapangan (dummy dev)',
            'portfolio_pdf_path' => $pdfPath,
            'certification_download_label' => 'Unduh Portofolio PDF (2026)',
        ]);

        $projects = [
            [
                'slug' => 'awork-id',
                'type' => ProjectType::App,
                'title_id' => 'awork.id',
                'title_en' => 'awork.id',
                'summary_id' => 'Platform manajemen kerja dan alur proyek terpadu untuk tim operasional dan lapangan.',
                'description_id' => '<p>awork.id dirancang untuk membantu tim operasional mengelola daftar tugas, jadwal proyek, dan pelacakan progres dalam satu ruang kerja yang ringkas.</p><h2>Fokus Pengembangan</h2><ul><li>Struktur proyek dan penugasan yang mudah dipantau oleh tim lapangan maupun pengelola.</li><li>Antarmuka berbasis web yang ringan untuk akses harian melalui desktop dan ponsel.</li><li>Fondasi backend Laravel dan basis data relasional yang terukur.</li></ul>',
                'tech_stack' => ['Laravel', 'MySQL', 'Tailwind CSS', 'Alpine.js'],
                'live_url' => 'https://awork.id',
                'repo_url' => null,
                'links' => [
                    ['type' => 'web', 'url' => 'https://awork.id', 'label' => 'Website'],
                    ['type' => 'playstore', 'url' => 'https://play.google.com/store/apps/details?id=id.awork.app', 'label' => 'Play Store'],
                ],
                'is_featured' => true,
                'sort_order' => 1,
                'bg' => [15, 23, 42],
                'accent' => [245, 165, 36],
            ],
            [
                'slug' => 'odm-studio-app',
                'type' => ProjectType::Drone,
                'title_id' => 'ODM Studio App',
                'title_en' => 'ODM Studio App',
                'summary_id' => 'Antarmuka pengelolaan pemrosesan citra udara dan keluaran pemetaan OpenDroneMap.',
                'description_id' => '<p>ODM Studio App menyederhanakan alur kerja pengolahan foto udara hasil misi drone menjadi ortofoto dan model permukaan melalui OpenDroneMap.</p><h2>Cakupan Alur Kerja</h2><ul><li>Pengaturan parameter pemrosesan citra dan pemantauan status tugas pemetaan.</li><li>Penataan hasil keluaran GIS agar siap ditinjau dan dibagikan ke pemangku kepentingan proyek.</li></ul>',
                'tech_stack' => ['Laravel', 'OpenDroneMap', 'GIS', 'Docker'],
                'live_url' => null,
                'repo_url' => 'https://github.com/akwibowo-lab/odm-studio',
                'is_featured' => false,
                'sort_order' => 2,
                'bg' => [12, 34, 56],
                'accent' => [92, 200, 255],
            ],
            [
                'slug' => 'pemetaan-koridor-pesisir',
                'type' => ProjectType::CaseStudy,
                'title_id' => 'Pemetaan Koridor Pesisir Utara',
                'title_en' => 'North Coast Corridor Mapping',
                'summary_id' => 'Studi kasus akuisisi fotogrametri udara 420 hektare dengan GSD 2,5 cm/piksel untuk evaluasi garis pantai.',
                'description_id' => '<p>Misi pemetaan udara koridor pesisir menggunakan wahana RTK untuk menghasilkan ortomosaik resolusi tinggi dan model elevasi digital.</p><h2>Hasil Utama</h2><ul><li>Cakupan area 420 ha dalam 6 sorti penerbangan terkontrol.</li><li>Akurasi horizontal dan vertikal diverifikasi dengan 12 titik GCP lapangan.</li></ul>',
                'tech_stack' => ['DJI RTK', 'Fotogrametri', 'QGIS', 'Ortofoto'],
                'live_url' => null,
                'repo_url' => null,
                'is_featured' => false,
                'sort_order' => 3,
                'bg' => [22, 36, 48],
                'accent' => [56, 189, 248],
            ],
            [
                'slug' => 'flight-log-telemetry',
                'type' => ProjectType::App,
                'title_id' => 'Flight Log & Telemetry Tracker',
                'title_en' => 'Flight Log & Telemetry Tracker',
                'summary_id' => 'Aplikasi catatan jam terbang pilot, riwayat baterai, dan inspeksi pra-terbang berbasis web.',
                'description_id' => '<p>Sistem pencatatan log terbang untuk memantau kesehatan baterai LiPo, jam terbang wahana, dan daftar periksa keselamatan sebelum misi.</p>',
                'tech_stack' => ['Laravel', 'Filament', 'SQLite'],
                'live_url' => 'https://example.test/flight-log',
                'repo_url' => null,
                'links' => [
                    ['type' => 'web', 'url' => 'https://example.test/flight-log', 'label' => 'Web App'],
                    ['type' => 'download', 'url' => '/storage/portfolio/akut-wibowo-portfolio.pdf', 'label' => 'Panduan'],
                ],
                'is_featured' => false,
                'sort_order' => 4,
                'bg' => [24, 24, 37],
                'accent' => [251, 191, 36],
            ],
        ];

        foreach ($projects as $item) {
            $slug = $item['slug'];
            $mediumPath = "media/dev-portfolio/{$slug}-medium.webp";
            $largePath = "media/dev-portfolio/{$slug}-large.webp";

            $this->createWebpBanner($disk->path($mediumPath), 800, 450, $item['bg'], $item['accent']);
            $this->createWebpBanner($disk->path($largePath), 1280, 720, $item['bg'], $item['accent']);

            $media = Media::query()->updateOrCreate(
                ['path' => $largePath],
                [
                    'disk' => 'public',
                    'filename' => "{$slug}-large.webp",
                    'mime_type' => 'image/webp',
                    'size_bytes' => filesize($disk->path($largePath)) ?: 1024,
                    'width' => 1280,
                    'height' => 720,
                    'alt_text' => "Thumbnail proyek {$item['title_id']}",
                    'variants' => [
                        'thumbnail' => $mediumPath,
                        'medium' => $mediumPath,
                        'large' => $largePath,
                    ],
                ]
            );

            Project::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'featured_media_id' => $media->id,
                    'type' => $item['type'],
                    'title_id' => $item['title_id'],
                    'title_en' => $item['title_en'],
                    'summary_id' => $item['summary_id'],
                    'description_id' => $item['description_id'],
                    'tech_stack' => $item['tech_stack'],
                    'live_url' => $item['live_url'],
                    'repo_url' => $item['repo_url'],
                    'links' => $item['links'] ?? null,
                    'is_featured' => $item['is_featured'],
                    'sort_order' => $item['sort_order'],
                    'status' => ContentStatus::Published,
                    'published_at' => now()->subDays($item['sort_order']),
                ]
            );
        }

        ContentCache::bump('homepage');
        ContentCache::bump('projects');
    }

    /**
     * @param  array{0:int,1:int,2:int}  $bg
     * @param  array{0:int,1:int,2:int}  $accent
     */
    private function createWebpBanner(string $absolutePath, int $width, int $height, array $bg, array $accent): void
    {
        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $image = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, $bg[0], $bg[1], $bg[2]);
        $accentColor = imagecolorallocate($image, $accent[0], $accent[1], $accent[2]);
        $lineColor = imagecolorallocate($image, min(255, $bg[0] + 28), min(255, $bg[1] + 32), min(255, $bg[2] + 40));

        imagefill($image, 0, 0, $bgColor);
        imagerectangle($image, 24, 24, $width - 25, $height - 25, $lineColor);
        imagefilledellipse($image, (int) ($width * 0.78), (int) ($height * 0.32), (int) ($height * 0.28), (int) ($height * 0.28), $accentColor);
        imagefilledrectangle($image, 48, (int) ($height * 0.65), (int) ($width * 0.45), (int) ($height * 0.7), $accentColor);

        imagewebp($image, $absolutePath, 85);
        imagedestroy($image);
    }

    private function createDummyPdf(string $absolutePath): void
    {
        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pdf = "%PDF-1.4\n1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj\n4 0 obj<< /Length 55 >>stream\nBT /F1 18 Tf 72 700 Td (Akut Wibowo - Dummy Portfolio PDF) Tj ET\nendstream\nendobj\n5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\nxref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000241 00000 n \n0000000346 00000 n \ntrailer<< /Size 6 /Root 1 0 R >>\nstartxref\n416\n%%EOF\n";
        file_put_contents($absolutePath, $pdf);
    }
}
