<?php

namespace App\Filament\Resources\Difficulties;

use App\Filament\Resources\Difficulties\Pages\CreateDifficulty;
use App\Filament\Resources\Difficulties\Pages\EditDifficulty;
use App\Filament\Resources\Difficulties\Pages\ListDifficulties;
use App\Models\Difficulty;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DifficultyResource extends Resource
{
    protected static ?string $model = Difficulty::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string | UnitEnum | null $navigationGroup = 'Jogo';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'dificuldade';

    protected static ?string $pluralModelLabel = 'dificuldades';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dificuldade')
                ->columns(3)
                ->schema([
                    TextInput::make('name')->label('Nome')->required()->maxLength(40),
                    TextInput::make('tolerance')
                        ->label('Tolerância')
                        ->numeric()->minValue(5)->maxValue(120)->suffix('°')->required()
                        ->helperText('Quanto cada osso pode errar antes de a nota zerar. Maior = mais fácil.'),
                    TextInput::make('window_ms')
                        ->label('Janela de tempo')
                        ->numeric()->minValue(100)->maxValue(2000)->suffix('ms')->required()
                        ->helperText('Quanto antes/depois da batida a pose ainda vale. Maior = mais fácil.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')->label('Dificuldade')->weight('bold'),
                TextColumn::make('tolerance')->label('Tolerância')->suffix('°'),
                TextColumn::make('window_ms')->label('Janela')->formatStateUsing(fn (int $state): string => '±'.$state.' ms'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDifficulties::route('/'),
            'create' => CreateDifficulty::route('/create'),
            'edit' => EditDifficulty::route('/{record}/edit'),
        ];
    }
}
