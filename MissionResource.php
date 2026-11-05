<?php

namespace App\Filament\Resources\Missions;

use App\Filament\Resources\Missions\Pages\CreateMission;
use App\Filament\Resources\Missions\Pages\EditMission;
use App\Filament\Resources\Missions\Pages\ListMissions;
use App\Models\Mission;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
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
                ->description('Cada missão é uma etapa da aula. A ordem é a da lista de missões (arraste para reordenar).')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->maxLength(80),
                    Toggle::make('active')
                        ->label('Ativa')
                        ->default(true)
                        ->inline(false),
                    Select::make('check_type')
                        ->label('Como verificar')
                        ->options([
                            'tests' => 'Testes automáticos (lista abaixo)',
                            'camera' => 'Abrir a câmera de verdade',
                        ])
                        ->default('tests')
                        ->required()
                        ->live()
                        ->helperText('"Câmera" chama a primeira função da lista abaixo passando o <video> e confere se o vídeo começou.'),
                    TagsInput::make('exports')
                        ->label('Funções que a missão define')
                        ->placeholder('nome exato da função + Enter')
                        ->required()
                        ->helperText('O jogo precisa que alguma missão ativa defina: '.implode(', ', array_keys(Mission::FUNCOES_DO_JOGO)).'.'),
                    Textarea::make('concept')
                        ->label('Conceito (o que a turma aprende)')
                        ->rows(2)
                        ->columnSpanFull(),
                    TextInput::make('unlock_message')
                        ->label('Mensagem quando completar')
                        ->prefix('🎉 Missão N completa:')
                        ->placeholder('o esqueleto apareceu!')
                        ->columnSpanFull(),
                ]),

            Section::make('Código')
                ->description('JavaScript. O código inicial aparece no quadro da turma; a resposta aparece no botão "Ver resposta". Uma missão pode usar funções de missões anteriores.')
                ->schema([
                    CodeEditor::make('starter_code')
                        ->label('Código inicial (com as dicas)')
                        ->language(Language::JavaScript)
                        ->required(),
                    CodeEditor::make('answer_code')
                        ->label('Resposta')
                        ->language(Language::JavaScript)
                        ->required(),
                ]),

            Section::make('Testes')
                ->description('Rodam no navegador quando alguém clica em "Testar". A missão passa quando todos devolvem true.')
                ->visible(fn (Get $get): bool => $get('check_type') !== 'camera')
                ->schema([
                    Callout::make('Como escrever um teste')
                        ->description(
                            'Use f.nomeDaFuncao(...) para chamar a função escrita pela pessoa. Ajudantes: perto(a, b) (diferença menor que 0,01) e pt(x, y) (cria um ponto do corpo). '.
                            'Uma linha só é uma expressão, ex.: perto(f.diferenca(30, 10), 20). Com várias linhas, termine com return.'
                        )
                        ->info(),
                    Repeater::make('tests')
                        ->label('')
                        ->relationship()
                        ->orderColumn('sort')
                        ->reorderable()
                        ->collapsible()
                        ->defaultItems(0)
                        ->addActionLabel('Adicionar teste')
                        ->itemLabel(fn (array $state): ?string => $state['description'] ?? null)
                        ->schema([
                            TextInput::make('description')
                                ->label('O que o teste confere (aparece para a turma)')
                                ->required()
                                ->maxLength(120),
                            CodeEditor::make('expression')
                                ->label('Verificação (JavaScript)')
                                ->language(Language::JavaScript)
                                ->required(),
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
                TextColumn::make('sort')->label('#')->width('1%'),
                TextColumn::make('title')->label('Missão')->searchable()->weight('bold'),
                TextColumn::make('check_type')
                    ->label('Verificação')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'camera' ? 'Câmera' : 'Testes')
                    ->color(fn (string $state): string => $state === 'camera' ? 'warning' : 'info'),
                TextColumn::make('exports')->label('Funções')->badge()->color('gray'),
                TextColumn::make('tests_count')->label('Testes')->counts('tests'),
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
