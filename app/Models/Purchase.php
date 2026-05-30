<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_id',
        'stripe_session_id',
        'amount_paid',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
        ];
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    // Scopes
    public function scopeMonthlySales($query)
    {
        $driver = DB::connection()->getDriverName();
        $dateTrunc = $driver === 'pgsql'
            ? "DATE_TRUNC('month', created_at)"
            : "strftime('%Y-%m', created_at)";

        return $query
            ->select(DB::raw("{$dateTrunc} as month"), DB::raw('SUM(amount_paid) as total'))
            ->groupBy('month')
            ->orderBy('month');
    }
}
