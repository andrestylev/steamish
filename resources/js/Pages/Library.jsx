import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Library({ games, totalGames }) {
    return (
        <AuthenticatedLayout>
            <Head title="Library" />

            <div className="container py-4">
                <h1 className="h3 fw-bold mb-1">Your Library</h1>
                <p className="text-secondary small mb-4">
                    {totalGames > 0
                        ? `You own ${totalGames} game${totalGames !== 1 ? 's' : ''}`
                        : 'Your purchased games will appear here.'}
                </p>

                {totalGames > 0 ? (
                    <div className="row g-3">
                        {games.map((game) => (
                            <div key={game.id} className="col-6 col-sm-4 col-lg-3 col-xl-2">
                                <div className="card game-card h-100 border-0">
                                    <Link href={route('games.show', { game: game.slug })}>
                                        <img
                                            src={game.cover}
                                            alt={game.title}
                                            className="card-img-top game-card-img"
                                            loading="lazy"
                                        />
                                    </Link>
                                    <div className="card-body p-2">
                                        <Link
                                            href={route('games.show', { game: game.slug })}
                                            className="text-decoration-none"
                                        >
                                            <h6 className="card-title text-white mb-1 small text-truncate">
                                                {game.title}
                                            </h6>
                                        </Link>
                                        <span className="badge bg-secondary" style={{ fontSize: '0.6rem' }}>
                                            {game.genre}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-5">
                        <div className="mb-3" style={{ fontSize: '3rem', opacity: 0.3 }}>&#127918;</div>
                        <h5 className="text-secondary">Your library is empty</h5>
                        <p className="text-secondary small mb-3">
                            Purchase games from our catalog to build your library.
                        </p>
                        <Link href={route('catalog')} className="btn btn-accent">
                            Browse Catalog
                        </Link>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
