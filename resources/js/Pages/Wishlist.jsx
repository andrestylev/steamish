import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Wishlist({ items, totalItems }) {
    const { post, processing } = useForm();

    const handleRemove = (gameId) => {
        post(route('wishlist.toggle', { gameId }));
    };

    const handleMoveToCart = (gameId) => {
        post(route('cart.add', { gameId }), {
            onSuccess: () => {
                // After adding to cart, remove from wishlist via toggle
                post(route('wishlist.toggle', { gameId }));
            },
        });
    };

    const formatPrice = (price) => {
        return parseFloat(price).toFixed(2);
    };

    return (
        <AuthenticatedLayout>
            <Head title="My Wishlist" />

            <div className="container py-4">
                <h1 className="h3 fw-bold mb-4">My Wishlist</h1>

                {totalItems > 0 ? (
                    <>
                        <p className="text-secondary small mb-4">
                            {totalItems} game{totalItems !== 1 ? 's' : ''} in your wishlist
                        </p>

                        <div className="row g-3">
                            {items.map((item) => (
                                <div key={item.id} className="col-12">
                                    <div
                                        className="d-flex align-items-center gap-3 p-3"
                                        style={{ backgroundColor: '#1e3040', borderRadius: 4 }}
                                    >
                                        {/* Cover */}
                                        <div className="flex-shrink-0" style={{ width: 80 }}>
                                            {item.game ? (
                                                <Link href={route('games.show', { game: item.game.slug })}>
                                                    <img
                                                        src={item.game.cover}
                                                        alt={item.game.title}
                                                        className="w-100"
                                                        style={{
                                                            borderRadius: 3,
                                                            aspectRatio: '3/4',
                                                            objectFit: 'cover',
                                                        }}
                                                    />
                                                </Link>
                                            ) : (
                                                <div
                                                    className="bg-secondary w-100 d-flex align-items-center justify-content-center text-secondary"
                                                    style={{ aspectRatio: '3/4', borderRadius: 3, fontSize: '0.7rem' }}
                                                >
                                                    N/A
                                                </div>
                                            )}
                                        </div>

                                        {/* Info */}
                                        <div className="flex-grow-1" style={{ minWidth: 0 }}>
                                            {item.game ? (
                                                <>
                                                    <Link
                                                        href={route('games.show', { game: item.game.slug })}
                                                        className="text-decoration-none"
                                                    >
                                                        <h6 className="mb-1 text-white fw-bold small text-truncate">
                                                            {item.game.title}
                                                        </h6>
                                                    </Link>
                                                    <span className="badge bg-secondary" style={{ fontSize: '0.65rem' }}>
                                                        {item.game.genre}
                                                    </span>
                                                </>
                                            ) : (
                                                <h6 className="mb-1 text-secondary small">Game unavailable</h6>
                                            )}
                                        </div>

                                        {/* Price */}
                                        <div className="text-end flex-shrink-0" style={{ minWidth: 80 }}>
                                            {item.game ? (
                                                item.game.is_discounted ? (
                                                    <>
                                                        <div
                                                            className="text-decoration-line-through text-secondary"
                                                            style={{ fontSize: '0.75rem' }}
                                                        >
                                                            ${formatPrice(item.game.price)}
                                                        </div>
                                                        <div className="fw-bold text-white">
                                                            ${formatPrice(item.game.discount_price)}
                                                        </div>
                                                    </>
                                                ) : (
                                                    <div className="fw-bold text-white">
                                                        ${formatPrice(item.game.price)}
                                                    </div>
                                                )
                                            ) : (
                                                <span className="text-secondary">&mdash;</span>
                                            )}
                                        </div>

                                        {/* Actions */}
                                        <div className="d-flex gap-2 flex-shrink-0">
                                            <button
                                                className="btn btn-accent btn-sm"
                                                onClick={() => handleMoveToCart(item.game_id)}
                                                disabled={processing}
                                                style={{ fontSize: '0.75rem', whiteSpace: 'nowrap' }}
                                            >
                                                Move to Cart
                                            </button>
                                            <button
                                                className="btn btn-sm btn-outline-danger"
                                                onClick={() => handleRemove(item.game_id)}
                                                disabled={processing}
                                                style={{ fontSize: '0.75rem' }}
                                                aria-label="Remove from wishlist"
                                            >
                                                &times;
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </>
                ) : (
                    /* Empty wishlist */
                    <div className="text-center py-5">
                        <div className="mb-3" style={{ fontSize: '3rem', opacity: 0.3 }}>&#10084;&#65039;</div>
                        <h5 className="text-secondary">Your wishlist is empty</h5>
                        <p className="text-secondary small mb-3">
                            Add games to your wishlist from the catalog or game detail pages.
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
