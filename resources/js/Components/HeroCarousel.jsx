import { Link } from '@inertiajs/react';

export default function HeroCarousel({ games }) {
    if (!games || games.length === 0) {
        return null;
    }

    return (
        <div id="heroCarousel" className="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
            {/* Indicators */}
            <div className="carousel-indicators">
                {games.map((_, index) => (
                    <button
                        key={index}
                        type="button"
                        data-bs-target="#heroCarousel"
                        data-bs-slide-to={index}
                        className={index === 0 ? 'active' : ''}
                        aria-current={index === 0 ? 'true' : undefined}
                        aria-label={`Slide ${index + 1}`}
                    />
                ))}
            </div>

            {/* Slides */}
            <div className="carousel-inner">
                {games.map((game, index) => (
                    <div key={game.id} className={`carousel-item ${index === 0 ? 'active' : ''}`}>
                        <div
                            className="hero-slide d-flex align-items-center"
                            style={{
                                backgroundImage: `linear-gradient(rgba(23, 26, 33, 0.3), rgba(23, 26, 33, 0.9)), url(${game.header})`,
                                backgroundSize: 'cover',
                                backgroundPosition: 'center',
                                minHeight: '400px',
                            }}
                        >
                            <div className="container">
                                <div className="row align-items-center">
                                    <div className="col-lg-7">
                                        <h1 className="display-5 fw-bold text-white mb-2">{game.title}</h1>
                                        <p className="lead text-white-50 mb-3 d-none d-md-block">
                                            {game.description}
                                        </p>
                                        <div className="d-flex align-items-center gap-3 mb-3">
                                            <span className="badge bg-secondary">{game.genre}</span>
                                            <span className="text-warning small">
                                                {'★'.repeat(Math.round(game.rating_avg))}{'☆'.repeat(5 - Math.round(game.rating_avg))}
                                                <span className="text-white-50 ms-1">({game.rating_count})</span>
                                            </span>
                                        </div>
                                        <div className="d-flex align-items-center gap-2">
                                            {game.is_discounted ? (
                                                <>
                                                    <span className="text-decoration-line-through text-secondary small">
                                                        ${game.price}
                                                    </span>
                                                    <span className="fs-4 fw-bold text-white">
                                                        ${game.discount_price}
                                                    </span>
                                                    <span className="badge bg-success">-{game.discount_pct}%</span>
                                                </>
                                            ) : (
                                                <span className="fs-4 fw-bold text-white">
                                                    {parseFloat(game.price) === 0 ? 'Free to Play' : `$${game.price}`}
                                                </span>
                                            )}
                                        </div>
                                        <div className="mt-3">
                                            <Link href={route('catalog')} className="btn btn-accent btn-lg">
                                                View Game
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Controls */}
            <button className="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span className="carousel-control-prev-icon" aria-hidden="true" />
                <span className="visually-hidden">Previous</span>
            </button>
            <button className="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span className="carousel-control-next-icon" aria-hidden="true" />
                <span className="visually-hidden">Next</span>
            </button>
        </div>
    );
}
