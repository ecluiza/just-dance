<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dance extends Model
{
    protected $fillable = ['name', 'bpm', 'beats_per_pose', 'intro_beats', 'song_id', 'active', 'sort'];

    protected function casts(): array
    {
        return [
            'bpm' => 'integer',
            'beats_per_pose' => 'integer',
            'intro_beats' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(DanceStep::class)->orderBy('sort');
    }

    /** Música sugerida: já vem marcada no menu do jogo (a pessoa pode trocar). */
    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    /** Total de batidas (introdução + todas as poses). */
    public function totalBatidas(): int
    {
        $batidas = $this->intro_beats;
        foreach ($this->steps as $passo) {
            $batidas += $passo->beats ?: $this->beats_per_pose;
        }

        return $batidas;
    }

    /** Duração em segundos num certo BPM (padrão: o da batida sintetizada). */
    public function duracaoSegundos(?int $bpm = null): float
    {
        $bpm ??= $this->bpm;

        return $bpm > 0 ? round($this->totalBatidas() * 60 / $bpm, 1) : 0;
    }
}
