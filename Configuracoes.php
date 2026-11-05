<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Configurações gerais do jogo (tabela settings).
 *
 * @property-read Schema $form
 */
class Configuracoes extends Page
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string | UnitEnum | null $navigationGroup = 'Ajustes';

    protected static ?string $navigationLabel = 'Configurações';

    protected static ?string $title = 'Configurações do jogo';

    protected static ?string $slug = 'configuracoes';

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::todas());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Site')
                    ->columns(2)
                    ->schema([
                        TextInput::make('site_title')->label('Nome do jogo')->required()->maxLength(60),
                        Toggle::make('allow_unlock_all')
                            ->label('Mostrar "liberar tudo com as respostas prontas"')
                            ->helperText('Deixa quem só quer dançar pular as missões.')
                            ->inline(false),
                        Textarea::make('welcome_text')->label('Texto no topo das missões')->rows(2)->columnSpanFull(),
                    ]),

                Section::make('Aula')
                    ->description('Valores iniciais dos sliders da aba Aula (cada pessoa pode mexer depois).')
                    ->columns(2)
                    ->schema([
                        TextInput::make('class_tolerance')->label('Tolerância inicial')->numeric()->minValue(5)->maxValue(120)->suffix('°')->required(),
                        TextInput::make('class_smoothing')->label('Suavização inicial')->numeric()->minValue(0)->maxValue(0.9)->step(0.1)->required()
                            ->helperText('0 = nenhuma, 0,9 = muita.'),
                    ]),

                Section::make('Pontuação')
                    ->description('A nota de cada pose vai de 0 a 1. Ela recebe a primeira classe cujo mínimo alcançar.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('label_perfect')->label('Nome da melhor classe')->required(),
                            TextInput::make('threshold_perfect')->label('Nota mínima')->numeric()->minValue(0)->maxValue(1)->step(0.05)->required(),
                            TextInput::make('points_perfect')->label('Pontos')->numeric()->minValue(0)->required(),

                            TextInput::make('label_great')->label('2ª classe')->required(),
                            TextInput::make('threshold_great')->label('Nota mínima')->numeric()->minValue(0)->maxValue(1)->step(0.05)->required(),
                            TextInput::make('points_great')->label('Pontos')->numeric()->minValue(0)->required(),

                            TextInput::make('label_good')->label('3ª classe')->required(),
                            TextInput::make('threshold_good')->label('Nota mínima')->numeric()->minValue(0)->maxValue(1)->step(0.05)->required(),
                            TextInput::make('points_good')->label('Pontos')->numeric()->minValue(0)->required(),

                            TextInput::make('label_miss')->label('Quando erra')->required(),
                        ]),
                    ]),

                Section::make('Combo')
                    ->description('A cada N acertos seguidos, o multiplicador de pontos sobe.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('combo_step')->label('A cada (acertos)')->numeric()->minValue(1)->required(),
                        TextInput::make('combo_increment')->label('Sobe')->numeric()->minValue(0)->step(0.1)->prefix('+')->required(),
                        TextInput::make('combo_max')->label('Até no máximo')->numeric()->minValue(1)->step(0.5)->prefix('×')->required(),
                    ]),

                Section::make('Técnico')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        Select::make('pose_model')
                            ->label('Modelo de pose do MediaPipe')
                            ->options([
                                'lite' => 'Lite (mais rápido, bom para celular)',
                                'full' => 'Full (mais preciso)',
                                'heavy' => 'Heavy (o mais preciso, pesado)',
                            ])
                            ->required(),
                        TextInput::make('mediapipe_version')->label('Versão do @mediapipe/tasks-vision')->required(),
                        TextInput::make('min_visibility')->label('Confiança mínima de um ponto')->numeric()->minValue(0)->maxValue(1)->step(0.05)->required()
                            ->helperText('Pontos com confiança menor são ignorados.'),
                        TextInput::make('lead_time_ms')->label('Antecedência da fila de poses')->numeric()->minValue(500)->maxValue(10000)->suffix('ms')->required()
                            ->helperText('Quanto antes a próxima pose aparece no jogo.'),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Salvar')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                        Action::make('jogo')
                            ->label('Abrir o jogo')
                            ->color('gray')
                            ->url('/', shouldOpenInNewTab: true),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        Setting::salvar($this->form->getState());

        Notification::make()->success()->title('Configurações salvas')->body('Quem recarregar o jogo já vê as mudanças.')->send();
    }
}
