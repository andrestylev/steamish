import { Link } from '@inertiajs/react';

export default function GameCard({ game }) {
    const displayPrice = () => {
        if (parseFloat(game.price) === 0) return 'Free to Play';

        if (game.is_discounted) {
            return (
                <>
                    <span className="text-decoration-line-through text-secondary small me-1">
                        ${game.price}
                    </span>
                    <span className="fw-bold text-white">${game.discount_price}</span>
                </>
            );
        }

        return <span className="fw-bold text-white">${game.price}</span>;
    };

    return (
        <div className="card game-card h-100 border-0">
            <div className="position-relative">
                <Link href={route('games.show', { game: game.slug })}>
                    <img
                        src={game.cover}
                        alt={game.title}
                        className="card-img-top game-card-img"
                        loading="lazy"
                    />
                </Link>
                {game.is_discounted && (
                    <span className="badge bg-success position-absolute top-0 start-0 m-2">
                        -{game.discount_pct}%
                    </span>
                )}
            </div>
            <div className="card-body p-2">
                <Link href={route('games.show', { game: game.slug })} className="text-decoration-none">
                    <h6 className="card-title text-white mb-1 small text-truncate">{game.title}</h6>
                </Link>
                <div className="d-flex align-items-center gap-1 mb-1">
                    <span className="text-warning" style={{ fontSize: '0.7rem' }}>
                        {'★'.repeat(Math.round(game.rating_avg))}
                    </span>
                    <span className="text-secondary" style={{ fontSize: '0.65rem' }}>
                        ({game.rating_count})
                    </span>
                </div>
                <div className="d-flex align-items-center justify-content-between">
                    <div className="small">{displayPrice()}</div>
                    <span className="badge bg-secondary" style={{ fontSize: '0.6rem' }}>{game.genre}</span>
                </div>
            </div>
        </div>
    );
}
