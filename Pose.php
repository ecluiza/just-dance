<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pose extends Model
{
    protected $fillable = ['label', 'arm_left', 'forearm_left', 'arm_right', 'forearm_right', 'sort'];

    protected function casts(): array
    {
        return [
            'arm_left' => 'integer',
            'forearm_left' => 'integer',
            'arm_right' => 'integer',
            'forearm_right' => 'integer',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(DanceStep::class);
    }

    /** Ângulos no formato que o jogo usa. */
    public function assinatura(): array
    {
        return self::assinaturaDe($this->toArray());
    }

    /** @param array{arm_left:int|float|string|null, forearm_left:mixed, arm_right:mixed, forearm_right:mixed} $dados */
    public static function assinaturaDe(array $dados): array
    {
        return [
            'bracoE' => (float) ($dados['arm_left'] ?? 0),
            'antebracoE' => (float) ($dados['forearm_left'] ?? 0),
            'bracoD' => (float) ($dados['arm_right'] ?? 0),
            'antebracoD' => (float) ($dados['forearm_right'] ?? 0),
        ];
    }
}
