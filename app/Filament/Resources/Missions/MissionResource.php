<?php

namespace App\Filament\Resources\Missions;

use App\Filament\Resources\Missions\Pages\CreateMission;
use App\Filament\Resources\Missions\Pages\EditMission;
use App\Filament\Resources\Missions\Pages\ListMissions;
use App\Models\Mission;
use App\Models\Pose;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class MissionResource extends Resource
{
    protected static ?string $model = Mission::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string | UnitEnum | null $navigationGroup = 'Aula';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'missão';

    protected static ?string $pluralModelLabel = 'missões';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Missão')
                ->description('Cada missão é um experimento com a câmera. A turma vê as missões ativas na ordem da lista (arraste para reordenar).')
                ->columns(2)
                ->schema([
                    TextInput::make('title')->label('Título')->required()->maxLength(80),
                    Toggle::make('active')->label('Ativa')->default(true)->inline(false),
                    Select::make('type')
                        ->label('Tipo de experimento')
                        ->options(Mission::TIPOS)
                        ->required()
                        ->live()
                        ->columnSpanFull(),
                    Textarea::make('concept')
                        ->label('Conceito (o que a turma aprende)')
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('challenge')
                        ->label('Desafio (o que a pessoa tem que fazer)')
                        ->rows(3)
                        ->columnSpanFull(),
                    TextInput::make('unlock_message')
                        ->label('Mensagem quando completar')
                        ->prefix('🎉 Missão N completa:')
                        ->columnSpanFull(),
                ]),

            // ---------- alvos de cada tipo de experimento (ficam em settings) ----------
            Section::make('Alvo do desafio: pixels')
                ->visible(fn (Get $get): bool => $get('type') === 'pixels')
                ->description('A missão passa quando a pessoa toca num pixel em que o canal escolhido é alto e os outros dois são baixos.')
                ->columns(3)
                ->schema([
                    Select::make('settings.canal')
                        ->label('Cor procurada')
                        ->options(['r' => 'Vermelho', 'g' => 'Verde', 'b' => 'Azul'])
                        ->default('r')
                        ->required(),
                    TextInput::make('settings.minimo')->label('Valor mínimo desse canal')->numeric()->minValue(0)->maxValue(255)->default(170)->required(),
                    TextInput::make('settings.max_outros')->label('Valor máximo dos outros canais')->numeric()->minValue(0)->maxValue(255)->default(110)->required(),
                ]),

            Section::make('Alvo do desafio: cores')
                ->visible(fn (Get $get): bool => $get('type') === 'cores')
                ->description('A missão passa quando a pessoa usa cada filtro marcado por um tempo.')
                ->columns(2)
                ->schema([
                    CheckboxList::make('settings.modos')
                        ->label('Filtros que precisam ser experimentados')
                        ->options(['cinza' => 'Tons de cinza', 'vermelho' => 'Só vermelho', 'verde' => 'Só verde', 'azul' => 'Só azul', 'limiar' => 'Limiar (preto e branco)'])
                        ->default(['cinza', 'vermelho', 'verde', 'azul', 'limiar'])
                        ->required(),
                    TextInput::make('settings.segurar_ms')->label('Tempo em cada filtro')->numeric()->minValue(0)->suffix('ms')->default(1500)->required(),
                ]),

            Section::make('Alvo do desafio: bordas')
                ->visible(fn (Get $get): bool => $get('type') === 'bordas')
                ->description('A missão passa quando a porcentagem da imagem que é borda fica entre o mínimo e o máximo pelo tempo pedido (contorno marcado, mas sem virar borrão).')
                ->columns(3)
                ->schema([
                    TextInput::make('settings.min_bordas')->label('Bordas: mínimo')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(4)->required(),
                    TextInput::make('settings.max_bordas')->label('Bordas: máximo')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(18)->required(),
                    TextInput::make('settings.segurar_ms')->label('Segurar por')->numeric()->minValue(0)->suffix('ms')->default(2000)->required(),
                ]),

            Section::make('Alvo do desafio: pontos do corpo')
                ->visible(fn (Get $get): bool => $get('type') === 'pontos')
                ->description('Duas etapas: primeiro a confiança dos pontos marcados cai abaixo de "some" (a pessoa esconde), depois sobe acima de "aparece".')
                ->columns(2)
                ->schema([
                    CheckboxList::make('settings.pontos')
                        ->label('Pontos acompanhados')
                        ->options(Mission::PONTOS)
                        ->columns(2)
                        ->default([15, 16])
                        ->required()
                        ->columnSpanFull(),
                    TextInput::make('settings.baixo')->label('Confiança para "some"')->numeric()->minValue(0)->maxValue(1)->step(0.05)->default(0.3)->required(),
                    TextInput::make('settings.alto')->label('Confiança para "aparece"')->numeric()->minValue(0)->maxValue(1)->step(0.05)->default(0.8)->required(),
                    TextInput::make('settings.segurar_ms')->label('Segurar cada etapa por')->numeric()->minValue(0)->suffix('ms')->default(1000)->required(),
                ]),

            Section::make('Alvo do desafio: ângulos')
                ->visible(fn (Get $get): bool => $get('type') === 'angulo')
                ->description('A pessoa faz cada posição em ordem. Ângulos na imagem original: braço esquerdo esticado para o lado = 0°, para cima = -90°, para baixo = 90°; braço direito esticado = 180°.')
                ->columns(2)
                ->schema([
                    Repeater::make('settings.alvos')
                        ->label('Posições')
                        ->reorderable()
                        ->defaultItems(1)
                        ->addActionLabel('Adicionar posição')
                        ->columns(3)
                        ->columnSpanFull()
                        ->schema([
                            Select::make('osso')->label('Osso')->options(Mission::OSSOS)->required(),
                            TextInput::make('angulo')->label('Ângulo')->numeric()->minValue(-180)->maxValue(180)->suffix('°')->required(),
                            TextInput::make('rotulo')->label('Texto para a turma')->required(),
                        ]),
                    TextInput::make('settings.tolerancia')->label('Pode errar até')->numeric()->minValue(1)->maxValue(90)->suffix('°')->default(15)->required(),
                    TextInput::make('settings.segurar_ms')->label('Segurar cada posição por')->numeric()->minValue(0)->suffix('ms')->default(2000)->required(),
                ]),

            Section::make('Alvo do desafio: comparar poses')
                ->visible(fn (Get $get): bool => $get('type') === 'comparar')
                ->columns(2)
                ->schema([
                    Select::make('settings.pose_id')
                        ->label('Pose-alvo')
                        ->options(fn () => Pose::query()->orderBy('sort')->pluck('label', 'id'))
                        ->required(),
                    TextInput::make('settings.nota_minima')->label('Nota mínima')->numeric()->minValue(0)->maxValue(1)->step(0.05)->default(0.85)->required()
                        ->helperText('De 0 a 1. 0,85 = 85%.'),
                    TextInput::make('settings.tolerancia')->label('Tolerância inicial do slider')->numeric()->minValue(5)->maxValue(120)->suffix('°')->default(45)->required(),
                    TextInput::make('settings.segurar_ms')->label('Segurar por')->numeric()->minValue(0)->suffix('ms')->default(1500)->required(),
                ]),

            Section::make('Alvo do desafio: livre')
                ->visible(fn (Get $get): bool => $get('type') === 'livre')
                ->schema([
                    Callout::make('Sem verificação automática')
                        ->description('A câmera mostra o esqueleto normalmente e a pessoa clica em "Concluir" quando terminar. Bom para discussões, como "Engane a IA".')
                        ->info(),
                ]),

            Section::make('"Ver como é feito"')
                ->description('Trecho de código que aparece num botão da missão, para quem quiser ver por dentro. Deixe vazio para não mostrar.')
                ->collapsed()
                ->schema([
                    CodeEditor::make('code_snippet')
                        ->label('')
                        ->language(Language::JavaScript),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('sort')->label('#')->width('1%'),
                TextColumn::make('title')->label('Missão')->searchable()->weight('bold'),
                TextColumn::make('type')
                    ->label('Experimento')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str(Mission::TIPOS[$state] ?? $state)->before(':')->toString()),
                ToggleColumn::make('active')->label('Ativa'),
            ])
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
            'index' => ListMissions::route('/'),
            'create' => CreateMission::route('/create'),
            'edit' => EditMission::route('/{record}/edit'),
        ];
    }
}
