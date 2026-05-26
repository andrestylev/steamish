import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ChartWidget from '@/Components/ChartWidget';

// Steam palette chart colors
const COLORS = ['#1a9fff', '#2a475e', '#66c0f4', '#c6d4df', '#4b6b80', '#8f98a0', '#1999e0', '#3d6b8a', '#7dc6f0', '#b0d0e0'];
const GENRE_COLORS = {
    Action: '#1a9fff',
    'Sci-Fi': '#66c0f4',
    RPG: '#2a475e',
    Simulation: '#4b6b80',
    Strategy: '#8f98a0',
    Sports: '#c6d4df',
    Horror: '#3d6b8a',
    Adventure: '#7dc6f0',
};

export default function Reports({ topGames, revenueByGenre, monthlySales }) {
    // Top 10 bar chart data
    const topGamesData = {
        labels: topGames.map((g) => g.title),
        datasets: [
            {
                label: 'Revenue ($)',
                data: topGames.map((g) => g.total_revenue),
                backgroundColor: COLORS.slice(0, topGames.length),
                borderRadius: 3,
            },
        ],
    };

    const topGamesOptions = {
        indexAxis: 'y',
        plugins: {
            legend: { display: false },
        },
    };

    // Revenue by genre pie chart data
    const revenueByGenreData = {
        labels: revenueByGenre.map((r) => r.genre),
        datasets: [
            {
                data: revenueByGenre.map((r) => r.total_revenue),
                backgroundColor: revenueByGenre.map(
                    (r) => GENRE_COLORS[r.genre] || '#8f98a0',
                ),
                borderColor: '#171a21',
                borderWidth: 2,
            },
        ],
    };

    // Monthly sales line chart data
    const monthlySalesData = {
        labels: monthlySales.map((m) => m.month),
        datasets: [
            {
                label: 'Revenue ($)',
                data: monthlySales.map((m) => m.total_revenue),
                borderColor: '#1a9fff',
                backgroundColor: 'rgba(26, 159, 255, 0.1)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#1a9fff',
                pointRadius: 4,
            },
            {
                label: 'Sales (units)',
                data: monthlySales.map((m) => m.total_sales),
                borderColor: '#66c0f4',
                backgroundColor: 'rgba(102, 192, 244, 0.1)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#66c0f4',
                pointRadius: 4,
                yAxisID: 'y1',
            },
        ],
    };

    const monthlySalesOptions = {
        scales: {
            y: {
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue ($)',
                    color: '#8f98a0',
                },
            },
            y1: {
                position: 'right',
                grid: { display: false },
                title: {
                    display: true,
                    text: 'Units Sold',
                    color: '#8f98a0',
                },
            },
        },
    };

    return (
        <AuthenticatedLayout>
            <Head title="Admin Reports" />

            <div className="container py-4">
                <h1 className="h3 fw-bold mb-1">Sales Reports</h1>
                <p className="text-secondary small mb-4">Analytics dashboard with sales and revenue data</p>

                <div className="row g-4">
                    {/* Top 10 Sellers */}
                    <div className="col-lg-6">
                        <div className="p-4 h-100" style={{ backgroundColor: '#1e3040', borderRadius: 4 }}>
                            <ChartWidget
                                type="bar"
                                data={topGamesData}
                                options={topGamesOptions}
                                title="Top 10 Best-Selling Games"
                                isEmpty={topGames.length === 0}
                                emptyMessage="No sales data yet. Purchase data will appear here once games are sold."
                            />
                        </div>
                    </div>

                    {/* Revenue by Genre */}
                    <div className="col-lg-6">
                        <div className="p-4 h-100" style={{ backgroundColor: '#1e3040', borderRadius: 4 }}>
                            <ChartWidget
                                type="pie"
                                data={revenueByGenreData}
                                title="Revenue by Genre"
                                isEmpty={revenueByGenre.length === 0}
                                emptyMessage="No revenue data yet. Revenue by genre will appear here once games are sold."
                            />
                        </div>
                    </div>

                    {/* Monthly Sales */}
                    <div className="col-12">
                        <div className="p-4" style={{ backgroundColor: '#1e3040', borderRadius: 4 }}>
                            <ChartWidget
                                type="line"
                                data={monthlySalesData}
                                options={monthlySalesOptions}
                                title="Monthly Sales (Last 12 Months)"
                                isEmpty={monthlySales.length === 0}
                                emptyMessage="No monthly data yet. Sales trends will appear here once games are sold."
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
