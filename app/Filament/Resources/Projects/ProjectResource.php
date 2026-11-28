<?php

namespace App\Filament\Resources\Projects;

use App\Enums\ContentStatus;
use App\Enums\ProjectLinkType;
use App\Enums\ProjectType;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Project;
use App\Services\Translation\GeminiProjectTranslator;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Livewire\Component;
use Throwable;
use UnitEnum;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Portofolio';

    protected static ?string $navigationLabel = 'Proyek';

    protected static ?string $modelLabel = 'Proyek';

    protected static ?string $pluralModelLabel = 'Proyek';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title_id';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Detail Proyek')
                            ->columnSpan(2)
                            ->columns(2)
                            ->headerActions([
                                Action::make('auto_translate_en')
                                    ->label('Auto-Translate ID → EN (AI)')
                                    ->icon(Heroicon::OutlinedLanguage)
                                    ->color('gray')
                                    ->action(function (Get $get, Set $set): void {
                                        $titleId = (string) ($get('title_id') ?? '');
                                        $summaryId = (string) ($get('summary_id') ?? '');
                                        $descriptionId = (string) ($get('description_id') ?? '');

                                        if (trim($titleId) === '' && trim($summaryId) === '' && trim($descriptionId) === '') {
                                            Notification::make()
                                                ->title('Isi teks Bahasa Indonesia terlebih dahulu.')
                                                ->warning()
                                                ->send();

                                            return;
                                        }

                                        try {
                                            $translated = app(GeminiProjectTranslator::class)->translateToEnglish(
                                                $titleId,
                                                $summaryId,
                                                $descriptionId
                                            );

                                            if ($translated['title_en'] !== '') {
                                                $set('title_en', $translated['title_en']);
                                            }
                                            if ($translated['summary_en'] !== '') {
                                                $set('summary_en', $translated['summary_en']);
                                            }
                                            if ($translated['description_en'] !== '') {
                                                $set('description_en', $translated['description_en']);
                                            }

                                            Notification::make()
                                                ->title('Terjemahan awal EN berhasil diisi.')
                                                ->body('Silakan tinjau kembali sebelum menyimpan.')
                                                ->success()
                                                ->send();
                                        } catch (Throwable $exception) {
                                            Notification::make()
                                                ->title('Auto-translate gagal')
                                                ->body($exception->getMessage())
                                                ->danger()
                                                ->send();
                                        }
                                    }),
                            ])
                            ->schema([
                                TextInput::make('title_id')
                                    ->label('Judul (ID)')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, Set $set, ?Project $record): void {
                                        if ($record === null && filled($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                TextInput::make('title_en')
                                    ->label('Title (EN)')
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->columnSpanFull(),
                                Textarea::make('summary_id')
                                    ->label('Ringkasan (ID)')
                                    ->rows(3),
                                Textarea::make('summary_en')
                                    ->label('Summary (EN)')
                                    ->rows(3),
                                RichEditor::make('description_id')
                                    ->label('Deskripsi Lengkap (ID)')
                                    ->columnSpanFull(),
                                RichEditor::make('description_en')
                                    ->label('Full Description (EN)')
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Thumbnail, Status & Tautan')
                            ->columnSpan(1)
                            ->extraAttributes(['style' => 'position: sticky; top: 5.5rem;'])
                            ->schema([
                                Actions::make([
                                    Action::make('sidebar_save')
                                        ->label(fn (?Project $record): string => $record === null ? 'Buat' : 'Simpan')
                                        ->color('primary')
                                        ->action(function (Component $livewire, ?Project $record): void {
                                            if ($record === null && method_exists($livewire, 'create')) {
                                                $livewire->create();

                                                return;
                                            }

                                            if (method_exists($livewire, 'save')) {
                                                $livewire->save();
                                            }
                                        }),
                                    Action::make('sidebar_cancel')
                                        ->label('Batal')
                                        ->color('gray')
                                        ->url(fn (): string => static::getUrl('index')),
                                ])->fullWidth(),
                                Actions::make([
                                    Action::make('preview')
                                        ->label('Preview')
                                        ->icon(Heroicon::OutlinedEye)
                                        ->color('gray')
                                        ->url(fn (Project $record): string => route('admin.preview.project', $record))
                                        ->openUrlInNewTab(),
                                    DeleteAction::make(),
                                ])
                                    ->visible(fn (?Project $record): bool => $record !== null)
                                    ->fullWidth(),
                                Select::make('type')
                                    ->label('Kategori')
                                    ->options(ProjectType::class)
                                    ->default(ProjectType::App)
                                    ->required(),
                                Select::make('status')
                                    ->label('Status')
                                    ->options(ContentStatus::class)
                                    ->default(ContentStatus::Draft)
                                    ->required(),
                                DateTimePicker::make('published_at')
                                    ->label('Tanggal Terbit')
                                    ->seconds(false),
                                Toggle::make('is_featured')
                                    ->label('Tampilkan sebagai Unggulan')
                                    ->default(false),
                                TextInput::make('sort_order')
                                    ->label('Urutan Tampil')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                Select::make('featured_media_id')
                                    ->label('Thumbnail dari Pustaka Media')
                                    ->relationship('featuredMedia', 'filename')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                FileUpload::make('thumbnail_upload')
                                    ->label('Atau Unggah Thumbnail Baru')
                                    ->helperText('Otomatis disimpan ke Media & dibuatkan varian WebP.')
                                    ->image()
                                    ->disk('public')
                                    ->directory('media-uploads')
                                    ->preserveFilenames()
                                    ->maxSize(10240),
                                Repeater::make('links')
                                    ->label('Tautan Proyek (Web, GitHub, Play Store, Download)')
                                    ->schema([
                                        Select::make('type')
                                            ->label('Jenis Tautan')
                                            ->options(ProjectLinkType::class)
                                            ->default(ProjectLinkType::Web)
                                            ->required(),
                                        TextInput::make('url')
                                            ->label('URL')
                                            ->url()
                                            ->required()
                                            ->maxLength(500),
                                        TextInput::make('label')
                                            ->label('Label Tombol (opsional)')
                                            ->placeholder('Otomatis sesuai jenis tautan')
                                            ->maxLength(60),
                                    ])
                                    ->defaultItems(0)
                                    ->addActionLabel('Tambah Tautan')
                                    ->collapsible(),
                                TextInput::make('live_url')
                                    ->label('URL Web / Demo (Legacy)')
                                    ->url()
                                    ->maxLength(500),
                                TextInput::make('repo_url')
                                    ->label('URL GitHub / Repo (Legacy)')
                                    ->url()
                                    ->maxLength(500),
                                TagsInput::make('tech_stack')
                                    ->label('Tech Stack')
                                    ->placeholder('Tambah teknologi'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_preview')
                    ->label('Thumbnail')
                    ->getStateUsing(fn (Project $record): ?string => $record->featuredMedia?->variantUrl('thumbnail'))
                    ->checkFileExistence(false)
                    ->square(),
                TextColumn::make('title_id')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Kategori')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (ContentStatus $state): string => match ($state) {
                        ContentStatus::Published => 'success',
                        ContentStatus::Draft => 'gray',
                        ContentStatus::Archived => 'danger',
                    })
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('type')
                    ->label('Kategori')
                    ->options(ProjectType::class),
                SelectFilter::make('status')
                    ->options(ContentStatus::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->url(fn (Project $record): string => route('admin.preview.project', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
