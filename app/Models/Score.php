<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Uma pessoa no ranking (melhor resultado dela na dança da competição). */
class Score extends Model
{
    protected $fillable = ['name', 'name_key', 'points', 'perfect', 'combo_max', 'poses', 'dance_id', 'difficulty'];

    protected function casts(): array
    {
        return ['points' => 'integer', 'perfect' => 'integer', 'combo_max' => 'integer', 'poses' => 'integer'];
    }

    public function dance(): BelongsTo
    {
        return $this->belongsTo(Dance::class);
    }

    public static function chaveDoNome(string $nome): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $nome)));
    }
}
