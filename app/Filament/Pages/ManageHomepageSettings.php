<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageHomepageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Konten Homepage';

    protected static ?string $title = 'Pengaturan Konten Homepage';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.manage-homepage-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::getHomepage());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Homepage')
                    ->tabs([
                        Tab::make('Hero & Foto Profil')
                            ->columns(2)
                            ->schema([
                                TextInput::make('hero_name')
                                    ->label('Nama Utama')
                                    ->required()
                                    ->maxLength(120),
                                TextInput::make('hero_role')
                                    ->label('Peran / Subtitle')
                                    ->required()
                                    ->maxLength(180),
                                Textarea::make('hero_summary')
                                    ->label('Ringkasan Hero')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('hero_cta_projects')
                                    ->label('Label Tombol Proyek')
                                    ->required()
                                    ->maxLength(80),
                                TextInput::make('hero_cta_contact')
                                    ->label('Label Tombol Kontak')
                                    ->required()
                                    ->maxLength(80),
                                TextInput::make('hero_whatsapp_url')
                                    ->label('URL WhatsApp Hero')
                                    ->url()
                                    ->required()
                                    ->columnSpanFull(),
                                FileUpload::make('hero_image_path')
                                    ->label('Foto Profil / Hero')
                                    ->helperText('Kosongkan untuk tetap memakai logo AW Orbit bawaan.')
                                    ->image()
                                    ->disk('public')
                                    ->directory('homepage')
                                    ->preserveFilenames()
                                    ->maxSize(5120),
                                TextInput::make('hero_image_alt')
                                    ->label('Alt Text Foto Hero')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Tab::make('Sertifikasi & PDF Portofolio')
                            ->columns(2)
                            ->schema([
                                TextInput::make('certification_heading')
                                    ->label('Judul Bagian Sertifikasi')
                                    ->required()
                                    ->maxLength(160),
                                TextInput::make('certification_download_label')
                                    ->label('Label Tombol Unduh PDF')
                                    ->required()
                                    ->maxLength(100),
                                Textarea::make('certification_intro')
                                    ->label('Pengantar Sertifikasi')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                                FileUpload::make('portfolio_pdf_path')
                                    ->label('File PDF Portofolio')
                                    ->helperText('Unggah PDF portofolio (maks. 20 MB).')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->disk('public')
                                    ->directory('portfolio')
                                    ->preserveFilenames()
                                    ->maxSize(20480)
                                    ->columnSpanFull(),
                                Repeater::make('certification_items')
                                    ->label('Daftar Sertifikasi & Kapabilitas')
                                    ->schema([
                                        TextInput::make('code')
                                            ->label('Kode')
                                            ->required()
                                            ->maxLength(40),
                                        TextInput::make('title')
                                            ->label('Judul')
                                            ->required()
                                            ->maxLength(160),
                                        Textarea::make('detail')
                                            ->label('Detail')
                                            ->rows(2)
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->defaultItems(3),
                            ]),
                        Tab::make('Proyek & Blog')
                            ->columns(2)
                            ->schema([
                                TextInput::make('projects_heading')
                                    ->label('Judul Galeri Proyek')
                                    ->required()
                                    ->maxLength(160),
                                Textarea::make('projects_intro')
                                    ->label('Pengantar Galeri Proyek')
                                    ->rows(2)
                                    ->required(),
                                TextInput::make('blog_heading')
                                    ->label('Judul Tulisan Terbaru')
                                    ->required()
                                    ->maxLength(160),
                                Textarea::make('blog_intro')
                                    ->label('Pengantar Tulisan Terbaru')
                                    ->rows(2)
                                    ->required(),
                            ]),
                        Tab::make('Kontak & SEO')
                            ->columns(2)
                            ->schema([
                                TextInput::make('contact_heading')
                                    ->label('Judul Bagian Kontak')
                                    ->required()
                                    ->maxLength(160)
                                    ->columnSpanFull(),
                                Textarea::make('contact_body')
                                    ->label('Teks Ajakan Kontak')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('contact_whatsapp_label')
                                    ->label('Label Tombol WhatsApp')
                                    ->required()
                                    ->maxLength(80),
                                TextInput::make('contact_whatsapp_url')
                                    ->label('URL WhatsApp Kontak')
                                    ->url()
                                    ->required(),
                                TextInput::make('contact_email_label')
                                    ->label('Label Tombol Email')
                                    ->required()
                                    ->maxLength(80),
                                TextInput::make('contact_email_address')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required(),
                                TextInput::make('seo_title')
                                    ->label('SEO Title Homepage')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Textarea::make('seo_description')
                                    ->label('SEO Meta Description Homepage')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        SiteSetting::saveHomepage($state);

        Notification::make()
            ->title('Pengaturan homepage disimpan.')
            ->success()
            ->send();
    }
}
