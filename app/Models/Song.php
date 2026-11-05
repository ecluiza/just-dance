<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends Model
{
    protected $fillable = ['name', 'artist', 'audio_path', 'bpm', 'offset_ms', 'active', 'sort'];

    protected function casts(): array
    {
        return [
            'bpm' => 'integer',
            'offset_ms' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function dances(): HasMany
    {
        return $this->hasMany(Dance::class);
    }

    /** Endereço público do arquivo (precisa do `php artisan storage:link`). Usa o domínio de quem acessou. */
    public function url(): ?string
    {
        return $this->audio_path ? asset('storage/'.$this->audio_path) : null;
    }
}
