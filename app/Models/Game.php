<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'about',
        'price',
        'discount_price',
        'discount_pct',
        'is_discounted',
        'release_date',
        'developer',
        'publisher',
        'genre',
        'platforms',
        'cover',
        'header',
        'rating_avg',
        'rating_count',
        'min_req',
        'rec_req',
        // IGDB fields
        'igdb_id',
        'aggregated_rating',
        'storyline',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'discount_pct' => 'integer',
            'is_discounted' => 'boolean',
            'release_date' => 'date',
            'rating_avg' => 'decimal:2',
            'rating_count' => 'integer',
        ];
    }

    // Relations
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(GameImage::class);
    }

    // Normalized pivot relations
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'game_genre');
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class, 'game_platform');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'game_tag');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'game_company')
            ->withPivot('role');
    }

    // Scopes
    // Accessor: return loaded platforms relation instead of legacy column
    public function getPlatformsAttribute(mixed $value): mixed
    {
        if ($this->relationLoaded('platforms')) {
            return $this->getRelation('platforms');
        }

        return $value;
    }

    public function scopeDiscounted($query)
    {
        return $query->where('is_discounted', true);
    }

    public function scopeByGenre($query, string $genre)
    {
        if (DB::table('game_genre')->exists()) {
            return $query->whereHas('genres', fn ($q) => $q->where('name', $genre));
        }

        return $query->where('genre', $genre);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where('title', 'like', "%{$term}%");
    }

    public function scopeByPlatform($query, string $platform)
    {
        if (DB::table('game_platform')->exists()) {
            return $query->whereHas('platforms', fn ($q) => $q->where('name', $platform));
        }

        return $query->whereJsonContains('platforms', $platform);
    }

    public function scopeByPriceRange($query, ?float $min, ?float $max)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        return $query;
    }

    public function scopeByMinRating($query, float $rating)
    {
        return $query->where('rating_avg', '>=', $rating);
    }
}
