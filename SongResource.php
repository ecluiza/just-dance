<?php

namespace App\Filament\Resources\Songs;

use App\Filament\Resources\Songs\Pages\CreateSong;
use App\Filament\Resources\Songs\Pages\EditSong;
use App\Filament\Resources\Songs\Pages\ListSongs;
use App\Models\Song;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class SongResource extends Resource
{
    protected static ?string $model = Song::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedMusicalNote;

    protected static string | UnitEnum | null $navigationGroup = 'Jogo';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'música';

    protected static ?string $pluralModelLabel = 'músicas';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Música')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->label('Nome')->required()->maxLength(100),
                    TextInput::make('artist')->label('Artista')->maxLength(100),
                    FileUpload::make('audio_path')
                        ->label('Arquivo de áudio')
                        ->disk('public')
                        ->directory('musicas')
                        ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/x-wav', 'audio/mp4', 'audio/x-m4a', 'audio/aac'])
                        ->maxSize(12 * 1024) // KB (limite padrão do Livewire; veja o README para aumentar)
                        ->required()
                        ->columnSpanFull(),
                    TextInput::make('bpm')
                        ->label('BPM (batidas por minuto)')
                        ->numeric()
                        ->minValue(40)
                        ->maxValue(220)
                        ->required()
                        ->helperText('Procure "nome da música bpm" na internet ou use um site de tap tempo.'),
                    TextInput::make('offset_ms')
                        ->label('Atraso até a primeira batida')
                        ->numeric()
                        ->default(0)
                        ->suffix('ms')
                        ->helperText('Se a música tem um silêncio ou introdução antes da batida começar, coloque quanto tempo é.'),
                    Toggle::make('active')->label('Aparece no menu do jogo')->default(true),
                ]),
            Callout::make('Dica')
                ->description('As danças são contadas em batidas, então qualquer dança funciona com qualquer música: o tempo das poses se ajusta pelo BPM. Músicas com batida bem marcada (pop, funk, eletrônica) funcionam melhor.')
                ->info(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')->label('Música')->searchable()->weight('bold')
                    ->description(fn (Song $record): ?string => $record->artist),
                TextColumn::make('bpm')->label('BPM'),
                TextColumn::make('offset_ms')->label('Atraso')->suffix(' ms')->color('gray'),
                TextColumn::make('dances_count')->label('Sugerida em')->counts('dances')->suffix(' danças'),
                ToggleColumn::make('active')->label('No menu'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSongs::route('/'),
            'create' => CreateSong::route('/create'),
            'edit' => EditSong::route('/{record}/edit'),
        ];
    }
}
