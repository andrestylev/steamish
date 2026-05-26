import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ChartWidget from '@/Components/ChartWidget';

const PLAY_COLORS = ['#1a9fff', '#66c0f4', '#2a475e', '#4b6b80', '#8f98a0'];

export default function Stats({ items, hasPlaytime }) {
    // Top 5 horizontal bar chart data
    const chartData = {
        labels: items.map((i) => i.title).reverse(),
        datasets: [
            {
                label: 'Hours Played',
                data: items.map((i) => i.hours_played).reverse(),
                backgroundColor: [...PLAY_COLORS].reverse(),
                borderRadius: 3,
            },
        ],
    };

    const chartOptions = {
        indexAxis: 'y',
        plugins: {
            legend: { display: false },
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Hours',
                    color: '#8f98a0',
                },
            },
        },
    };

    return (
        <AuthenticatedLayout>
            <Head title="My Stats" />

            <div className="container py-4">
                <h1 className="h3 fw-bold mb-1">My Gaming Stats</h1>
                <p className="text-secondary small mb-4">Your most-played games</p>

                {hasPlaytime ? (
                    <div className="row">
                        <div className="col-lg-8">
                            <div className="p-4" style={{ backgroundColor: '#1e3040', borderRadius: 4 }}>
                                <ChartWidget
                                    type="bar"
                                    data={chartData}
                                    options={chartOptions}
                                    title="Top 5 Most Played"
                                />
                            </div>
                        </div>

                        {/* Game list */}
                        <div className="col-lg-4">
                            <div className="p-4" style={{ backgroundColor: '#1e3040', borderRadius: 4 }}>
                                <h6 className="fw-bold mb-3 text-white">Details</h6>
                                {items.map((item, index) => (
                                    <div
                                        key={item.slug}
                                        className="d-flex align-items-center gap-2 mb-2 pb-2"
                                        style={{
                                            borderBottom: index < items.length - 1
                                                ? '1px solid rgba(255,255,255,0.05)'
                                                : 'none',
                                        }}
                                    >
                                        <div
                                            className="d-flex align-items-center justify-content-center fw-bold"
                                            style={{
                                                width: 28,
                                                height: 28,
                                                borderRadius: '50%',
                                                backgroundColor: PLAY_COLORS[index],
                                                fontSize: '0.75rem',
                                                color: '#171a21',
                                                flexShrink: 0,
                                            }}
                                        >
                                            {index + 1}
                                        </div>
                                        <div className="flex-grow-1" style={{ minWidth: 0 }}>
                                            {item.slug ? (
                                                <Link
                                                    href={route('games.show', { game: item.slug })}
                                                    className="text-decoration-none"
                                                >
                                                    <span className="text-white small text-truncate d-block">
                                                        {item.title}
                                                    </span>
                                                </Link>
                                            ) : (
                                                <span className="text-white small">{item.title}</span>
                                            )}
                                            <span className="text-accent small d-block">
                                                {item.hours_played} hrs
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                ) : (
                    /* No playtime data */
                    <div className="text-center py-5">
                        <div className="mb-3" style={{ fontSize: '3rem', opacity: 0.3 }}>&#128200;</div>
                        <h5 className="text-secondary">No playtime data yet</h5>
                        <p className="text-secondary small mb-3">
                            Playtime stats will appear here once you have played some games.
                        </p>
                        <Link href={route('catalog')} className="btn btn-accent">
                            Browse Games
                        </Link>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
