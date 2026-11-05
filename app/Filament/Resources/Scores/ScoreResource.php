<?php

namespace App\Filament\Resources\Scores;

use App\Filament\Resources\Scores\Pages\ListScores;
use App\Models\Score;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Ranking da competição: só listar e apagar (quem cria as linhas é o jogo). */
class ScoreResource extends Resource
{
    protected static ?string $model = Score::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static string | UnitEnum | null $navigationGroup = 'Jogo';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Ranking';

    protected static ?string $modelLabel = 'pontuação';

    protected static ?string $pluralModelLabel = 'ranking';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('points', 'desc')
            ->poll('5s')
            ->columns([
                TextColumn::make('posicao')->label('#')->rowIndex()->width('1%'),
                TextColumn::make('name')->label('Nome')->searchable()->weight('bold'),
                TextColumn::make('points')->label('Pontos')->numeric(thousandsSeparator: '.')->sortable(),
                TextColumn::make('perfect')->label('Perfeitos')->formatStateUsing(fn (Score $record) => "{$record->perfect} de {$record->poses}"),
                TextColumn::make('combo_max')->label('Maior combo'),
                TextColumn::make('dance.name')->label('Dança')->placeholder('(dança apagada)'),
                TextColumn::make('difficulty')->label('Dificuldade')->badge(),
                TextColumn::make('updated_at')->label('Quando')->since()->sortable(),
            ])
            ->recordActions([
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
            'index' => ListScores::route('/'),
        ];
    }
}
