import { Link, usePage } from '@inertiajs/react';

export default function AddToCartModal({ game, onClose }) {
    const { cartCount } = usePage().props;

    const discountPrice = game.is_discounted ? game.discount_price : null;
    const discountPct = game.discount_pct;

    return (
        <div className="modal-backdrop fade show" onClick={onClose}>
            <div
                className="modal d-block"
                tabIndex="-1"
                role="dialog"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="modal-dialog modal-dialog-centered">
                    <div className="modal-content" style={{ backgroundColor: '#1e3040', border: '1px solid #2a475e' }}>
                        {/* Header */}
                        <div className="modal-header border-secondary pb-2">
                            <h5 className="modal-title text-white fw-bold">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="18"
                                    height="18"
                                    fill="currentColor"
                                    className="text-accent me-2"
                                    viewBox="0 0 16 16"
                                    style={{ verticalAlign: '-3px' }}
                                >
                                    <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5zM3.14 5l1.25 5h8.22l1.25-5H3.14zM5 13a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"/>
                                </svg>
                                Added to Cart
                            </h5>
                            <button
                                type="button"
                                className="btn-close btn-close-white"
                                aria-label="Close"
                                onClick={onClose}
                            />
                        </div>

                        {/* Body — Game info */}
                        <div className="modal-body py-3">
                            <div className="d-flex gap-3">
                                {/* Cover */}
                                <div
                                    style={{
                                        width: 120,
                                        height: 68,
                                        borderRadius: 3,
                                        overflow: 'hidden',
                                        flexShrink: 0,
                                        backgroundColor: '#2a475e',
                                    }}
                                >
                                    <img
                                        src={game.cover}
                                        alt={game.title}
                                        className="w-100 h-100"
                                        style={{ objectFit: 'cover' }}
                                    />
                                </div>

                                {/* Details */}
                                <div className="flex-grow-1">
                                    <h6 className="fw-bold text-white mb-1">{game.title}</h6>
                                    {discountPrice ? (
                                        <div className="d-flex align-items-center gap-2">
                                            <span className="text-decoration-line-through text-secondary small">
                                                ${game.price}
                                            </span>
                                            <span className="fw-bold text-white">${discountPrice}</span>
                                            <span className="badge bg-success">-{discountPct}%</span>
                                        </div>
                                    ) : (
                                        <span className="fw-bold text-white">
                                            {parseFloat(game.price) === 0 ? 'Free to Play' : `$${game.price}`}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Footer — Actions */}
                        <div className="modal-footer border-secondary pt-2 d-flex justify-content-between">
                            <button
                                type="button"
                                className="btn btn-outline-secondary"
                                onClick={onClose}
                            >
                                Continue Shopping
                            </button>
                            <Link
                                href={route('cart.index')}
                                className="btn btn-accent"
                                onClick={onClose}
                            >
                                View Cart ({cartCount})
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
