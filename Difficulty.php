<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Difficulty extends Model
{
    protected $fillable = ['name', 'tolerance', 'window_ms', 'sort'];

    protected function casts(): array
    {
        return ['tolerance' => 'integer', 'window_ms' => 'integer'];
    }
}
