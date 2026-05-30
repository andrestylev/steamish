<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    /** @use HasFactory<\Database\Factories\GenreFactory> */
    use HasFactory;

    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
    ];

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_genre');
    }
}
