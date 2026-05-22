<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameImage extends Model
{
    /** @use HasFactory<\Database\Factories\GameImageFactory> */
    use HasFactory;

    protected $fillable = [
        'game_id',
        'url',
        'type',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    // Relations
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
