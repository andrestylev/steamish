import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Cart({ cartItems, subtotal, itemCount }) {
    const handleRemove = (itemId) => {
        router.delete(route('cart.remove', { item: itemId }));
    };

    const formatPrice = (price) => {
        return parseFloat(price).toFixed(2);
    };

    return (
        <AuthenticatedLayout>
            <Head title="Shopping Cart" />

            <div className="container py-4">
                <h1 className="h3 fw-bold mb-4">Shopping Cart</h1>

                {itemCount > 0 ? (
                    <div className="row">
                        {/* Cart Items */}
                        <div className="col-lg-8 mb-4">
                            <div className="d-flex justify-content-between mb-2">
                                <span className="text-secondary small">{itemCount} item{itemCount !== 1 ? 's' : ''} in your cart</span>
                            </div>

                            {cartItems.map((item) => (
                                <div
                                    key={item.id}
                                    className="d-flex align-items-center gap-3 p-3 mb-2"
                                    style={{ backgroundColor: '#1e3040', borderRadius: 4 }}
                                >
                                    {/* Game Cover */}
                                    <div className="flex-shrink-0" style={{ width: 80 }}>
                                        {item.game ? (
                                            <Link href={route('games.show', { game: item.game.slug })}>
                                                <img
                                                    src={item.game.cover}
                                                    alt={item.game.title}
                                                    className="w-100"
                                                    style={{ borderRadius: 3, aspectRatio: '3/4', objectFit: 'cover' }}
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

                                    {/* Game Info */}
                                    <div className="flex-grow-1" style={{ minWidth: 0 }}>
                                        {item.game ? (
                                            <>
                                                <Link
                                                    href={route('games.show', { game: item.game.slug })}
                                                    className="text-decoration-none"
                                                >
                                                    <h6 className="mb-1 text-white fw-bold small text-truncate">{item.game.title}</h6>
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
                                                    <div className="text-decoration-line-through text-secondary" style={{ fontSize: '0.75rem' }}>
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
                                            <span className="text-secondary">—</span>
                                        )}
                                    </div>

                                    {/* Remove Button */}
                                    <div className="flex-shrink-0">
                                        <button
                                            className="btn btn-sm btn-outline-danger"
                                            onClick={() => handleRemove(item.id)}
                                            style={{ fontSize: '0.75rem' }}
                                            aria-label="Remove from cart"
                                        >
                                            &times;
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Order Summary */}
                        <div className="col-lg-4 mb-4">
                            <div className="p-4" style={{ backgroundColor: '#1e3040', borderRadius: 4 }}>
                                <h5 className="fw-bold mb-3">Order Summary</h5>

                                <div className="d-flex justify-content-between mb-2 small">
                                    <span className="text-secondary">Items ({itemCount})</span>
                                    <span>${formatPrice(subtotal)}</span>
                                </div>

                                <hr style={{ borderColor: 'rgba(255,255,255,0.1)' }} />

                                <div className="d-flex justify-content-between mb-3">
                                    <span className="fw-bold">Total</span>
                                    <span className="fw-bold fs-5 text-white">${formatPrice(subtotal)}</span>
                                </div>

                                <Link
                                    href={route('checkout.index')}
                                    className="btn btn-accent w-100 fw-bold"
                                >
                                    Proceed to Checkout
                                </Link>

                                <div className="text-center mt-2">
                                    <Link href={route('catalog')} className="text-accent small text-decoration-none">
                                        Continue Shopping
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                ) : (
                    /* Empty Cart */
                    <div className="text-center py-5">
                        <div className="mb-3" style={{ fontSize: '3rem', opacity: 0.3 }}>&#128722;</div>
                        <h5 className="text-secondary">Your cart is empty</h5>
                        <p className="text-secondary small mb-3">
                            Looks like you have not added any games to your cart yet.
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
