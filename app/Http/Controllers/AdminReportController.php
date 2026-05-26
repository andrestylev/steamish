<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminReportController extends Controller
{
    /**
     * Show the admin reports dashboard with Chart.js analytics.
     */
    public function index(): Response
    {
        // Top 10 best-selling games by total revenue
        $topGames = Game::select('games.*')
            ->selectRaw('COALESCE((SELECT COUNT(*) FROM purchases WHERE purchases.game_id = games.id), 0) as total_purchases')
            ->selectRaw('COALESCE((SELECT SUM(amount_paid) FROM purchases WHERE purchases.game_id = games.id), 0) as total_revenue')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get()
            ->map(fn ($game) => [
                'title' => $game->title,
                'cover' => $game->cover,
                'total_purchases' => (int) $game->total_purchases,
                'total_revenue' => (float) ($game->total_revenue ?? 0),
            ]);

        // Revenue grouped by genre
        $revenueByGenre = DB::table('purchases')
            ->join('games', 'purchases.game_id', '=', 'games.id')
            ->select('games.genre', DB::raw('COALESCE(SUM(purchases.amount_paid), 0) as total_revenue'), DB::raw('COUNT(*) as total_sales'))
            ->groupBy('games.genre')
            ->get()
            ->map(fn ($row) => [
                'genre' => $row->genre,
                'total_revenue' => (float) $row->total_revenue,
                'total_sales' => (int) $row->total_sales,
            ]);

        // Monthly sales for the last 12 months
        $monthlySales = DB::table('purchases')
            ->select(
                DB::raw("strftime('%Y-%m', purchases.created_at) as month"),
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('COALESCE(SUM(amount_paid), 0) as total_revenue')
            )
            ->where('purchases.created_at', '>=', now()->subMonths(12))
            ->groupBy(DB::raw("strftime('%Y-%m', purchases.created_at)"))
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => $row->month,
                'total_sales' => (int) $row->total_sales,
                'total_revenue' => (float) $row->total_revenue,
            ]);

        return Inertia::render('Admin/Reports', [
            'topGames' => $topGames,
            'revenueByGenre' => $revenueByGenre,
            'monthlySales' => $monthlySales,
        ]);
    }
}
