<?php

namespace App\Filament\Resources\Posts;

use App\Enums\ContentStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Media;
use App\Models\Post;
use App\Services\Seo\PostSeoAnalyzer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use UnitEnum;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Artikel';

    protected static ?string $modelLabel = 'Artikel';

    protected static ?string $pluralModelLabel = 'Artikel';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Konten Artikel')
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, Set $set, ?Post $record): void {
                                        if ($record === null && filled($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->unique(ignoreRecord: true),
                                Textarea::make('excerpt')
                                    ->label('Ringkasan')
                                    ->rows(3),
                                RichEditor::make('content_html')
                                    ->label('Isi Artikel')
                                    ->live(onBlur: true)
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Publikasi & Relasi')
                            ->columnSpan(1)
                            ->extraAttributes(['style' => 'position: sticky; top: 5.5rem;'])
                            ->schema([
                                Actions::make([
                                    Action::make('preview')
                                        ->label('Preview')
                                        ->icon(Heroicon::OutlinedEye)
                                        ->color('gray')
                                        ->url(fn (Post $record): string => route('admin.preview.post', $record))
                                        ->openUrlInNewTab(),
                                    DeleteAction::make(),
                                ])
                                    ->visible(fn (?Post $record): bool => $record !== null)
                                    ->fullWidth(),
                                Placeholder::make('draft_autosave')
                                    ->hiddenLabel()
                                    ->visible(fn (?Post $record): bool => $record !== null && $record->status === ContentStatus::Draft)
                                    ->content(fn (EditPost $livewire): HtmlString => new HtmlString(
                                        '<div wire:poll.25s="autoSaveDraft" style="font-size:0.78rem;opacity:0.78;">'
                                        .($livewire->lastAutoSavedAt
                                            ? 'Draf tersimpan otomatis pada '.e($livewire->lastAutoSavedAt)
                                            : 'Auto-save draf aktif (setiap 25 detik)')
                                        .'</div>'
                                    )),
                                Select::make('status')
                                    ->label('Status')
                                    ->options(ContentStatus::class)
                                    ->default(ContentStatus::Draft)
                                    ->required(),
                                DateTimePicker::make('published_at')
                                    ->label('Tanggal Terbit')
                                    ->seconds(false),
                                Select::make('author_id')
                                    ->label('Penulis')
                                    ->relationship('author', 'name')
                                    ->default(fn () => auth()->id())
                                    ->searchable()
                                    ->preload(),
                                Select::make('featured_media_id')
                                    ->label('Gambar Utama')
                                    ->relationship('featuredMedia', 'filename')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                Select::make('categories')
                                    ->label('Kategori')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload(),
                                Select::make('tags')
                                    ->label('Tag')
                                    ->relationship('tags', 'name')
                                    ->multiple()
                                    ->searchable(),
                                TextInput::make('reading_time_minutes')
                                    ->label('Waktu Baca (menit)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->nullable(),
                            ]),
                    ]),
                Section::make('SEO & Analisis Keyword')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('focus_keyword')
                            ->label('Focus Keyword')
                            ->placeholder('Contoh: pilot drone bersertifikat')
                            ->maxLength(255)
                            ->live(onBlur: true),
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(255)
                            ->live(onBlur: true),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(2)
                            ->live(onBlur: true),
                        TextInput::make('canonical_url')
                            ->label('Canonical URL')
                            ->url()
                            ->maxLength(500),
                        Placeholder::make('seo_analysis')
                            ->label('Skor SEO')
                            ->columnSpanFull()
                            ->content(function (Get $get): HtmlString {
                                $featuredMediaId = $get('featured_media_id');
                                $featuredImageAlt = filled($featuredMediaId)
                                    ? Media::query()->whereKey($featuredMediaId)->value('alt_text')
                                    : null;

                                $analysis = app(PostSeoAnalyzer::class)->analyze(
                                    focusKeyword: $get('focus_keyword'),
                                    title: $get('title'),
                                    slug: $get('slug'),
                                    metaTitle: $get('meta_title'),
                                    metaDescription: $get('meta_description'),
                                    contentHtml: $get('content_html'),
                                    hasFeaturedImage: filled($featuredMediaId),
                                    featuredImageAlt: $featuredImageAlt,
                                );

                                $toneColors = [
                                    'success' => ['#10b981', 'rgba(16, 185, 129, 0.14)', 'rgba(16, 185, 129, 0.35)'],
                                    'warning' => ['#f59e0b', 'rgba(245, 158, 11, 0.14)', 'rgba(245, 158, 11, 0.35)'],
                                    'danger' => ['#ef4444', 'rgba(239, 68, 68, 0.14)', 'rgba(239, 68, 68, 0.35)'],
                                ];

                                [$dotColor, $badgeBg, $badgeBorder] = $toneColors[$analysis['tone']];

                                $itemsHtml = '';
                                foreach ($analysis['checks'] as $check) {
                                    $checkColor = match ($check['status']) {
                                        'pass' => '#10b981',
                                        'warn' => '#f59e0b',
                                        default => '#ef4444',
                                    };

                                    $itemsHtml .= '<li style="display:flex;align-items:flex-start;gap:0.6rem;font-size:0.875rem;line-height:1.5;">'
                                        .'<span aria-hidden="true" style="display:inline-block;width:10px;height:10px;border-radius:9999px;background:'.$checkColor.';margin-top:0.4rem;flex-shrink:0;"></span>'
                                        .'<span>'.e($check['message']).'</span>'
                                        .'</li>';
                                }

                                return new HtmlString(
                                    '<div style="border:1px solid rgba(156,163,175,0.25);border-radius:0.5rem;padding:1rem;background:rgba(255,255,255,0.02);">'
                                    .'<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;margin-bottom:0.85rem;padding-bottom:0.75rem;border-bottom:1px solid rgba(156,163,175,0.18);">'
                                    .'<span style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.25rem 0.65rem;border-radius:9999px;border:1px solid '.$badgeBorder.';background:'.$badgeBg.';color:'.$dotColor.';font-size:0.8rem;font-weight:600;">'
                                    .'<span aria-hidden="true" style="display:inline-block;width:8px;height:8px;border-radius:9999px;background:'.$dotColor.';"></span>'
                                    .'Skor SEO: '.$analysis['score'].' / 100 — '.e($analysis['label'])
                                    .'</span>'
                                    .'<span style="font-size:0.8rem;opacity:0.75;">'.$analysis['word_count'].' kata</span>'
                                    .'</div>'
                                    .'<ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:0.45rem;">'.$itemsHtml.'</ul>'
                                    .'</div>'
                                );
                            }),
                    ]),
                Section::make('Riwayat Revisi (Maks. 2)')
                    ->columnSpanFull()
                    ->collapsed()
                    ->visible(fn (?Post $record): bool => $record !== null)
                    ->schema([
                        Placeholder::make('revisions_list')
                            ->hiddenLabel()
                            ->content(function (?Post $record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('');
                                }

                                $revisions = $record->revisions()->with('user:id,name')->get();
                                if ($revisions->isEmpty()) {
                                    return new HtmlString('<p style="font-size:0.875rem;opacity:0.75;">Belum ada revisi tersimpan. Revisi dibuat otomatis saat artikel diperbarui.</p>');
                                }

                                $rows = '';
                                foreach ($revisions as $rev) {
                                    $author = e($rev->user?->name ?? 'Sistem');
                                    $time = e($rev->created_at?->format('d M Y H:i') ?? '—');
                                    $title = e($rev->title);
                                    $rows .= '<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;padding:0.65rem 0;border-bottom:1px solid rgba(156,163,175,0.16);">'
                                        .'<div><div style="font-size:0.875rem;font-weight:600;">'.$title.'</div>'
                                        .'<div style="font-size:0.78rem;opacity:0.7;">'.$time.' · '.$author.'</div></div>'
                                        .'<button type="button" wire:click="restoreRevision('.(int) $rev->id.')" style="padding:0.35rem 0.75rem;border-radius:0.375rem;border:1px solid rgba(245,158,11,0.45);background:rgba(245,158,11,0.12);color:#f59e0b;font-size:0.8rem;font-weight:600;cursor:pointer;">Pulihkan</button>'
                                        .'</div>';
                                }

                                return new HtmlString('<div>'.$rows.'</div>');
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_preview')
                    ->label('Gambar')
                    ->getStateUsing(fn (Post $record): ?string => $record->featuredMedia?->variantUrl('thumbnail'))
                    ->checkFileExistence(false)
                    ->square(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(55),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (ContentStatus $state): string => match ($state) {
                        ContentStatus::Published => 'success',
                        ContentStatus::Draft => 'gray',
                        ContentStatus::Archived => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('categories.name')
                    ->label('Kategori')
                    ->badge()
                    ->limitList(2),
                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(ContentStatus::class),
                SelectFilter::make('categories')
                    ->label('Kategori')
                    ->relationship('categories', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->url(fn (Post $record): string => route('admin.preview.post', $record))
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
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
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
