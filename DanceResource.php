<?php

namespace App\Filament\Resources\Dances;

use App\Filament\Resources\Dances\Pages\CreateDance;
use App\Filament\Resources\Dances\Pages\EditDance;
use App\Filament\Resources\Dances\Pages\ListDances;
use App\Models\Dance;
use App\Models\Pose;
use App\Models\Song;
use App\Support\BonecoSvg;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class DanceResource extends Resource
{
    protected static ?string $model = Dance::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string | UnitEnum | null $navigationGroup = 'Jogo';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'dança';

    protected static ?string $pluralModelLabel = 'danças';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dança')
                ->description('A dança é contada em batidas, então funciona com qualquer música. A pessoa escolhe a música no menu do jogo.')
                ->columns(3)
                ->schema([
                    TextInput::make('name')->label('Nome')->required()->maxLength(80)->columnSpan(2),
                    Toggle::make('active')->label('Aparece no jogo')->default(true)->inline(false),
                    Select::make('beats_per_pose')
                        ->label('Cada pose dura')
                        ->options([1 => '1 batida', 2 => '2 batidas', 4 => '4 batidas (1 compasso)', 8 => '8 batidas (2 compassos)'])
                        ->default(4)
                        ->required(),
                    TextInput::make('intro_beats')
                        ->label('Introdução antes da 1ª pose')
                        ->numeric()->minValue(0)->maxValue(32)->default(8)->suffix('batidas')->required(),
                    TextInput::make('bpm')
                        ->label('BPM da "Batida do jogo"')
                        ->numeric()->minValue(40)->maxValue(220)->default(120)->required()
                        ->helperText('Usado quando a música escolhida é a batida sintetizada.'),
                    Select::make('song_id')
                        ->label('Música sugerida')
                        ->options(fn () => Song::query()->orderBy('sort')->pluck('name', 'id'))
                        ->placeholder('Batida do jogo (sintetizada)')
                        ->helperText('Já vem marcada no menu quando escolhem esta dança.')
                        ->columnSpan(3),
                ]),

            Section::make('Sequência de poses')
                ->description('Arraste para mudar a ordem. Deixe "batidas" vazio para usar o padrão da dança.')
                ->schema([
                    Repeater::make('steps')
                        ->label('')
                        ->relationship()
                        ->orderColumn('sort')
                        ->reorderable()
                        ->defaultItems(0)
                        ->addActionLabel('Adicionar pose')
                        ->grid(2)
                        ->columns(3)
                        ->schema([
                            Select::make('pose_id')
                                ->label('Pose')
                                ->options(fn () => Pose::query()->orderBy('sort')->pluck('label', 'id'))
                                ->required()
                                ->live()
                                ->columnSpan(2),
                            TextInput::make('beats')
                                ->label('Batidas')
                                ->numeric()->minValue(1)->maxValue(16)
                                ->placeholder('padrão'),
                            Html::make(function (Get $get): HtmlString {
                                $pose = $get('pose_id') ? Pose::query()->find($get('pose_id')) : null;

                                return new HtmlString($pose ? BonecoSvg::render($pose->assinatura(), 72) : '');
                            })->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')->label('Dança')->searchable()->weight('bold'),
                TextColumn::make('steps_count')->label('Poses')->counts('steps'),
                TextColumn::make('duracao')
                    ->label('Duração (batida do jogo)')
                    ->state(fn (Dance $record): string => $record->duracaoSegundos().' s · '.$record->bpm.' bpm'),
                TextColumn::make('song.name')->label('Música sugerida')->placeholder('Batida do jogo'),
                ToggleColumn::make('active')->label('No jogo'),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Duplicar')
                    ->excludeAttributes(['steps_count'])
                    ->beforeReplicaSaved(function (Dance $replica): void {
                        $replica->name .= ' (cópia)';
                        $replica->sort = (int) Dance::query()->max('sort') + 1;
                    })
                    ->after(function (Dance $replica, Dance $record): void {
                        foreach ($record->steps as $passo) {
                            $replica->steps()->create($passo->only(['pose_id', 'beats', 'sort']));
                        }
                    }),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDances::route('/'),
            'create' => CreateDance::route('/create'),
            'edit' => EditDance::route('/{record}/edit'),
        ];
    }
}
