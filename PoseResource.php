<?php

namespace App\Filament\Resources\Poses;

use App\Filament\Resources\Poses\Pages\CreatePose;
use App\Filament\Resources\Poses\Pages\EditPose;
use App\Filament\Resources\Poses\Pages\ListPoses;
use App\Models\Pose;
use App\Support\BonecoSvg;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class PoseResource extends Resource
{
    protected static ?string $model = Pose::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static string | UnitEnum | null $navigationGroup = 'Jogo';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'pose';

    protected static ?string $pluralModelLabel = 'poses';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pose')
                ->columns(2)
                ->schema([
                    Grid::make(1)->schema([
                        TextInput::make('label')
                            ->label('Nome da pose')
                            ->required()
                            ->maxLength(60),
                        self::angulo('arm_left', 'Braço esquerdo (ombro → cotovelo)'),
                        self::angulo('forearm_left', 'Antebraço esquerdo (cotovelo → pulso)'),
                        self::angulo('arm_right', 'Braço direito (ombro → cotovelo)'),
                        self::angulo('forearm_right', 'Antebraço direito (cotovelo → pulso)'),
                    ]),
                    Grid::make(1)->schema([
                        Html::make(fn (Get $get): HtmlString => new HtmlString(
                            '<div style="display:flex;flex-direction:column;align-items:center;gap:8px">'.
                            BonecoSvg::render([
                                'bracoE' => $get('arm_left'),
                                'antebracoE' => $get('forearm_left'),
                                'bracoD' => $get('arm_right'),
                                'antebracoD' => $get('forearm_right'),
                            ], 220).
                            '<small style="opacity:.7">Prévia: como a pessoa vê o boneco na tela (espelhado)</small></div>'
                        )),
                    ]),
                ]),
            Section::make('Como funcionam os ângulos')
                ->collapsed()
                ->schema([
                    Html::make(new HtmlString(
                        '<p>Os ângulos são medidos na imagem <b>original</b> da câmera (sem espelho), em graus:</p>'.
                        '<ul style="list-style:disc;margin-left:1.5rem">'.
                        '<li>apontando para a direita da imagem = <b>0°</b>, para baixo = <b>90°</b>, para cima = <b>-90°</b>, para a esquerda = <b>180°</b>;</li>'.
                        '<li>o lado esquerdo da pessoa aparece à direita da imagem: braço esquerdo esticado para o lado = <b>0°</b>, braço direito esticado = <b>180°</b>.</li>'.
                        '</ul><p>Na dúvida, mexa nos números e olhe a prévia.</p>'
                    )),
                ]),
        ]);
    }

    private static function angulo(string $campo, string $rotulo): TextInput
    {
        return TextInput::make($campo)
            ->label($rotulo)
            ->numeric()
            ->minValue(-180)
            ->maxValue(180)
            ->step(5)
            ->suffix('°')
            ->default(0)
            ->required()
            ->live(debounce: 300);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('preview')
                    ->label('')
                    ->state(fn (Pose $record): string => BonecoSvg::render($record->assinatura(), 56))
                    ->html(),
                TextColumn::make('label')->label('Pose')->searchable()->weight('bold'),
                TextColumn::make('angulos')
                    ->label('Ângulos (braço e antebraço esq. / dir.)')
                    ->state(fn (Pose $r): string => "{$r->arm_left}° {$r->forearm_left}° / {$r->arm_right}° {$r->forearm_right}°")
                    ->color('gray'),
                TextColumn::make('steps_count')->label('Usada em')->counts('steps')->suffix(' passos'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn (Pose $record): bool => $record->steps()->exists())
                    ->tooltip(fn (Pose $record): ?string => $record->steps()->exists() ? 'Está em uso numa dança' : null),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPoses::route('/'),
            'create' => CreatePose::route('/create'),
            'edit' => EditPose::route('/{record}/edit'),
        ];
    }
}
