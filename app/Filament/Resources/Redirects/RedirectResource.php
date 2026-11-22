<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\Pages\ManageRedirects;
use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan & SEO';

    protected static ?string $navigationLabel = 'Redirect 301';

    protected static ?string $modelLabel = 'Redirect';

    protected static ?string $pluralModelLabel = 'Redirect';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'source_path';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('source_path')
                    ->label('Path Asal')
                    ->placeholder('/contoh-url-lama')
                    ->required()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (string $state): string => Redirect::normalizePath($state))
                    ->unique(ignoreRecord: true),
                TextInput::make('target_url')
                    ->label('URL Tujuan')
                    ->placeholder('/contoh-url-baru atau https://...')
                    ->required()
                    ->maxLength(500),
                Select::make('status_code')
                    ->label('Kode Status')
                    ->options([
                        301 => '301 (Permanen)',
                        302 => '302 (Sementara)',
                    ])
                    ->default(301)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_path')
                    ->label('Path Asal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_url')
                    ->label('URL Tujuan')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('status_code')
                    ->label('Kode')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('hits')
                    ->label('Hits')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_hit_at')
                    ->label('Hit Terakhir')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
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
            'index' => ManageRedirects::route('/'),
        ];
    }
}
