<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\CreateMedia;
use App\Filament\Resources\Media\Pages\EditMedia;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Media';

    protected static ?string $modelLabel = 'Media';

    protected static ?string $pluralModelLabel = 'Media';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'filename';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('uploaded_file')
                    ->label('Unggah Gambar')
                    ->image()
                    ->disk('public')
                    ->directory('media-uploads')
                    ->preserveFilenames()
                    ->maxSize(10240)
                    ->required(fn (?Media $record): bool => $record === null)
                    ->visible(fn (?Media $record): bool => $record === null)
                    ->columnSpanFull(),
                Placeholder::make('preview')
                    ->label('Pratinjau Gambar')
                    ->content(function (?Media $record): HtmlString {
                        if (! $record) {
                            return new HtmlString('');
                        }

                        $src = '/storage/'.ltrim($record->path, '/');
                        $alt = e($record->alt_text ?: $record->filename);

                        return new HtmlString(
                            '<img src="'.e($src).'" alt="'.$alt.'" class="max-h-64 rounded-lg border border-gray-700 object-contain" />'
                        );
                    })
                    ->visible(fn (?Media $record): bool => $record !== null)
                    ->columnSpanFull(),
                TextInput::make('filename')
                    ->label('Nama File')
                    ->disabled()
                    ->visible(fn (?Media $record): bool => $record !== null),
                TextInput::make('mime_type')
                    ->label('Tipe MIME')
                    ->disabled()
                    ->visible(fn (?Media $record): bool => $record !== null),
                TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('caption')
                    ->label('Keterangan (Caption)')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('preview_url')
                    ->label('Pratinjau')
                    ->getStateUsing(fn (Media $record): string => $record->variantUrl('thumbnail'))
                    ->checkFileExistence(false)
                    ->square(),
                TextColumn::make('filename')
                    ->label('Nama File')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('alt_text')
                    ->label('Alt Text')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('mime_type')
                    ->label('Tipe')
                    ->toggleable(),
                TextColumn::make('size_bytes')
                    ->label('Ukuran')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 1).' KB' : '—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Diunggah')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CreateMedia::route('/create'),
            'edit' => EditMedia::route('/{record}/edit'),
        ];
    }
}
