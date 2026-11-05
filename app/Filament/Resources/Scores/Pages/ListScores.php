<?php

namespace App\Filament\Resources\Scores\Pages;

use App\Filament\Resources\Scores\ScoreResource;
use App\Models\Score;
use App\Support\Ranking;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ListScores extends ListRecords
{
    protected static string $resource = ScoreResource::class;

    public function getSubheading(): string | Htmlable | null
    {
        $c = Ranking::competicao();

        return $c
            ? "Valendo agora: {$c['danca']->name} · {$c['dificuldade']->name}. Cada nome fica com o melhor resultado. Troque a dança em Ajustes → Configurações."
            : '⚠️ Ranking desligado: escolha a dança da competição em Ajustes → Configurações.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('telao')
                ->label('Abrir no telão')
                ->icon(Heroicon::OutlinedTv)
                ->color('gray')
                ->url('/ranking', shouldOpenInNewTab: true),
            Action::make('zerar')
                ->label('Zerar ranking')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Zerar o ranking?')
                ->modalDescription('Apaga todas as pontuações, de todas as danças. Não dá para desfazer.')
                ->modalSubmitActionLabel('Zerar')
                ->action(function (): void {
                    $quantas = Score::query()->count();
                    Score::query()->delete();
                    Notification::make()->success()->title("Ranking zerado ({$quantas} apagadas)")->send();
                }),
        ];
    }
}
