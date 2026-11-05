<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionTest extends Model
{
    public $timestamps = false;

    protected $fillable = ['mission_id', 'description', 'expression', 'sort'];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }
}
