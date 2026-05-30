<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Platform extends Model
{
    /** @use HasFactory<\Database\Factories\PlatformFactory> */
    use HasFactory;

    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
        'abbreviation',
    ];

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_platform');
    }
}
