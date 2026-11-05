<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DanceStep extends Model
{
    public $timestamps = false;

    protected $fillable = ['dance_id', 'pose_id', 'beats', 'sort'];

    protected function casts(): array
    {
        return ['beats' => 'integer'];
    }

    public function dance(): BelongsTo
    {
        return $this->belongsTo(Dance::class);
    }

    public function pose(): BelongsTo
    {
        return $this->belongsTo(Pose::class);
    }
}
