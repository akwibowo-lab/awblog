<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pengaturan Umum';

    protected static ?string $title = 'Pengaturan Umum & Maintenance';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-site-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::getMaintenance());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mode Pemeliharaan (Maintenance Mode)')
                    ->description('Aktifkan mode pemeliharaan untuk frontend publik atau panel admin (/admin). Sesi admin yang sudah login tetap dapat mengakses /admin untuk menonaktifkan mode ini.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('frontend_enabled')
                            ->label('Maintenance Mode Frontend')
                            ->helperText('Pengunjung publik akan melihat halaman 503 Pemeliharaan.')
                            ->default(false),
                        Toggle::make('backend_enabled')
                            ->label('Maintenance Mode Backend (/admin)')
                            ->helperText('Tamu yang belum login ke /admin akan melihat halaman 503 Pemeliharaan.')
                            ->default(false),
                        TextInput::make('title')
                            ->label('Judul Halaman Maintenance')
                            ->required()
                            ->maxLength(160)
                            ->columnSpanFull(),
                        Textarea::make('message')
                            ->label('Pesan Pemeliharaan')
                            ->rows(3)
                            ->required()
                            ->maxLength(600)
                            ->columnSpanFull(),
                        TextInput::make('bypass_token')
                            ->label('Token Bypass Rahasia (Opsional)')
                            ->helperText('Gunakan ?bypass=TOKEN pada URL untuk tetap mengakses situs saat maintenance aktif. Jalankan "php artisan maintenance:off" via SSH jika terkunci.')
                            ->maxLength(100)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SiteSetting::saveMaintenance([
            'frontend_enabled' => (bool) ($state['frontend_enabled'] ?? false),
            'backend_enabled' => (bool) ($state['backend_enabled'] ?? false),
            'title' => (string) ($state['title'] ?? 'Situs Sedang Dalam Pemeliharaan'),
            'message' => (string) ($state['message'] ?? ''),
            'bypass_token' => trim((string) ($state['bypass_token'] ?? '')),
        ]);

        Notification::make()
            ->title('Pengaturan maintenance berhasil disimpan.')
            ->success()
            ->send();
    }
}
