<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

#[Fillable(['key', 'value'])]
class SiteSetting extends Model
{
    public const HOMEPAGE_KEY = 'homepage';

    public const MAINTENANCE_KEY = 'maintenance';

    private const HOMEPAGE_CACHE_KEY = 'site_settings.homepage';

    private const MAINTENANCE_CACHE_KEY = 'site_settings.maintenance';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * Load stored homepage settings merged over default translation values.
     *
     * @return array<string, mixed>
     */
    public static function getHomepage(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $defaults = self::homepageDefaults();

        try {
            $stored = Cache::rememberForever(self::HOMEPAGE_CACHE_KEY, function (): array {
                $record = self::query()->where('key', self::HOMEPAGE_KEY)->first();

                return is_array($record?->value) ? $record->value : [];
            });
        } catch (Throwable) {
            $stored = [];
        }

        $filtered = array_filter(
            $stored,
            static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []
        );

        if ($locale === 'en') {
            $sharedKeys = [
                'hero_name',
                'hero_whatsapp_url',
                'hero_image_path',
                'portfolio_pdf_path',
                'contact_whatsapp_url',
                'contact_email_address',
            ];
            $filtered = array_intersect_key($filtered, array_flip($sharedKeys));
        }

        return array_replace($defaults, $filtered);
    }

    /**
     * Persist homepage settings and clear cached copy.
     *
     * @param  array<string, mixed>  $data
     */
    public static function saveHomepage(array $data): void
    {
        self::query()->updateOrCreate(
            ['key' => self::HOMEPAGE_KEY],
            ['value' => $data]
        );

        Cache::forget(self::HOMEPAGE_CACHE_KEY);
    }

    /**
     * Load stored maintenance settings merged over defaults.
     *
     * @return array<string, mixed>
     */
    public static function getMaintenance(): array
    {
        $defaults = self::maintenanceDefaults();

        try {
            $stored = Cache::rememberForever(self::MAINTENANCE_CACHE_KEY, function (): array {
                $record = self::query()->where('key', self::MAINTENANCE_KEY)->first();

                return is_array($record?->value) ? $record->value : [];
            });
        } catch (Throwable) {
            $stored = [];
        }

        return array_replace($defaults, array_filter(
            $stored,
            static fn (mixed $value): bool => $value !== null && $value !== ''
        ));
    }

    /**
     * Persist maintenance settings and clear cached copy.
     *
     * @param  array<string, mixed>  $data
     */
    public static function saveMaintenance(array $data): void
    {
        self::query()->updateOrCreate(
            ['key' => self::MAINTENANCE_KEY],
            ['value' => $data]
        );

        Cache::forget(self::MAINTENANCE_CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    public static function maintenanceDefaults(): array
    {
        return [
            'frontend_enabled' => false,
            'backend_enabled' => false,
            'title' => 'Situs Sedang Dalam Pemeliharaan',
            'message' => 'Kami sedang melakukan pembaruan dan peningkatan layanan. Silakan kembali beberapa saat lagi.',
            'bypass_token' => '',
        ];
    }

    /**
     * Default homepage content sourced from Indonesian translation strings.
     *
     * @return array<string, mixed>
     */
    public static function homepageDefaults(): array
    {
        return [
            'hero_role' => __('home.hero.role'),
            'hero_name' => __('home.hero.name'),
            'hero_summary' => __('home.hero.summary'),
            'hero_cta_projects' => __('home.hero.cta_projects'),
            'hero_cta_contact' => __('home.hero.cta_contact'),
            'hero_whatsapp_url' => __('home.hero.whatsapp_url'),
            'hero_image_path' => null,
            'hero_image_alt' => __('home.hero.orbit_alt'),
            'certification_heading' => __('home.certification.heading'),
            'certification_intro' => __('home.certification.intro'),
            'certification_download_label' => __('home.certification.download_pdf'),
            'portfolio_pdf_path' => null,
            'certification_items' => __('home.certification.items'),
            'projects_heading' => __('home.projects.heading'),
            'projects_intro' => __('home.projects.intro'),
            'blog_heading' => __('home.blog.heading'),
            'blog_intro' => __('home.blog.intro'),
            'contact_heading' => __('home.contact.heading'),
            'contact_body' => __('home.contact.body'),
            'contact_whatsapp_label' => __('home.contact.whatsapp_label'),
            'contact_whatsapp_url' => __('home.contact.whatsapp_url'),
            'contact_email_label' => __('home.contact.email_label'),
            'contact_email_address' => __('home.contact.email_address'),
            'seo_title' => __('home.seo.title'),
            'seo_description' => __('home.seo.description'),
        ];
    }
}
