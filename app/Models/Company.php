<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;

    protected $fillable = [
        'igdb_id',
        'name',
        'slug',
        'country',
    ];

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_company')
            ->withPivot('role');
    }
}
