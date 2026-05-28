import { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import StarRating from '@/Components/StarRating';
import ReviewCard from '@/Components/ReviewCard';

export default function GameDetail({ game, reviews }) {
    const { auth } = usePage().props;
    const [activeScreenshot, setActiveScreenshot] = useState(0);
    const [showReviewForm, setShowReviewForm] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        rating: 5,
        body: '',
    });

    const handleAddToCart = () => {
        post(route('cart.add', { gameId: game.id }))
    };

    const handleToggleWishlist = () => {
        post(route('wishlist.toggle', { gameId: game.id }))
    };

    const handleSubmitReview = (e) => {
        e.preventDefault();
        post(route('reviews.store', { game: game.id }), {
            onSuccess: () => {
                reset();
                setShowReviewForm(false);
            },
        });
    };

    const displayPrice = () => {
        if (parseFloat(game.price) === 0) return 'Free to Play';

        if (game.is_discounted) {
            return (
                <>
                    <span className="text-decoration-line-through text-secondary me-2 fs-5">${game.price}</span>
                    <span className="fw-bold text-white fs-3">${game.discount_price}</span>
                    <span className="badge bg-success ms-2 fs-6">-{game.discount_pct}%</span>
                </>
            );
        }

        return <span className="fw-bold text-white fs-3">${game.price}</span>;
    };

    const renderPlatformIcons = (platforms) => {
        const labels = {
            windows: 'Windows',
            mac: 'Mac',
            linux: 'Linux',
            playstation: 'PlayStation',
            xbox: 'Xbox',
            nintendo: 'Nintendo',
        };
        return platforms.map((p) => (
            <span key={p} className="badge bg-secondary me-1" style={{ fontSize: '0.7rem' }}>
                {labels[p] || p}
            </span>
        ));
    };

    return (
        <GuestLayout>
            <Head title={game.title} />

            {/* Header Banner */}
            <div className="position-relative" style={{ height: 300, overflow: 'hidden' }}>
                <img
                    src={game.header}
                    alt={game.title}
                    className="w-100 h-100"
                    style={{ objectFit: 'cover', objectPosition: 'center top' }}
                />
                <div className="position-absolute bottom-0 start-0 w-100 p-4" style={{
                    background: 'linear-gradient(transparent, rgba(27, 40, 56, 0.95))',
                }}>
                    <div className="container">
                        <h1 className="fw-bold mb-1">{game.title}</h1>
                        <div className="d-flex align-items-center gap-3">
                            <StarRating rating={game.rating_avg} size="md" />
                            <span className="text-secondary small">
                                {parseFloat(game.rating_avg).toFixed(1)} ({game.rating_count.toLocaleString()} reviews)
                            </span>
                            {renderPlatformIcons(game.platforms)}
                        </div>
                    </div>
                </div>
            </div>

            <div className="container py-4">
                <div className="row">
                    {/* Main Content */}
                    <div className="col-lg-8">
                        {/* Screenshot Gallery */}
                        {game.gallery && game.gallery.length > 0 && (
                            <div className="mb-4">
                                <div className="position-relative mb-2" style={{ borderRadius: 4, overflow: 'hidden' }}>
                                    <img
                                        src={game.gallery[activeScreenshot]}
                                        alt={`Screenshot ${activeScreenshot + 1}`}
                                        className="w-100"
                                        style={{ aspectRatio: '16/9', objectFit: 'cover', backgroundColor: '#2a475e' }}
                                    />
                                </div>
                                {game.gallery.length > 1 && (
                                    <div className="d-flex gap-2 overflow-auto pb-1">
                                        {game.gallery.map((ss, idx) => (
                                            <button
                                                key={idx}
                                                className={`btn p-0 border-0 flex-shrink-0 ${idx === activeScreenshot ? 'ring-2 ring-accent' : ''}`}
                                                style={{
                                                    width: 120,
                                                    height: 68,
                                                    borderRadius: 3,
                                                    overflow: 'hidden',
                                                    opacity: idx === activeScreenshot ? 1 : 0.6,
                                                    outline: idx === activeScreenshot ? '2px solid var(--steam-accent)' : 'none',
                                                }}
                                                onClick={() => setActiveScreenshot(idx)}
                                            >
                                                <img
                                                    src={ss}
                                                    alt={`Thumb ${idx + 1}`}
                                                    className="w-100 h-100"
                                                    style={{ objectFit: 'cover' }}
                                                />
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* About */}
                        <div className="mb-4">
                            <h3 className="h5 fw-bold mb-2">About This Game</h3>
                            <p style={{ color: '#acb8c4', lineHeight: 1.7 }}>{game.about}</p>
                        </div>

                        {/* System Requirements */}
                        <div className="mb-4">
                            <h3 className="h5 fw-bold mb-2">System Requirements</h3>
                            <div className="row g-3">
                                <div className="col-md-6">
                                    <div className="p-3" style={{ backgroundColor: '#1e3040', borderRadius: 4 }}>
                                        <h6 className="text-accent fw-bold mb-2" style={{ fontSize: '0.85rem' }}>Minimum</h6>
                                        <ul className="list-unstyled mb-0" style={{ fontSize: '0.8rem', color: '#acb8c4', lineHeight: 1.8 }}>
                                            {game.min_req.split('|').map((req, idx) => (
                                                <li key={idx}>{req.trim()}</li>
                                            ))}
                                        </ul>
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <div className="p-3" style={{ backgroundColor: '#1e3040', borderRadius: 4 }}>
                                        <h6 className="text-accent fw-bold mb-2" style={{ fontSize: '0.85rem' }}>Recommended</h6>
                                        <ul className="list-unstyled mb-0" style={{ fontSize: '0.8rem', color: '#acb8c4', lineHeight: 1.8 }}>
                                            {game.rec_req.split('|').map((req, idx) => (
                                                <li key={idx}>{req.trim()}</li>
                                            ))}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Reviews Section */}
                        <div className="mb-4">
                            <div className="d-flex align-items-center justify-content-between mb-3">
                                <h3 className="h5 fw-bold mb-0">Reviews</h3>
                                {auth?.user && (
                                    <button
                                        className="btn btn-accent btn-sm"
                                        onClick={() => setShowReviewForm(!showReviewForm)}
                                    >
                                        {showReviewForm ? 'Cancel' : 'Write a Review'}
                                    </button>
                                )}
                            </div>

                            {/* Review Form */}
                            {showReviewForm && (
                                <div className="p-3 mb-3" style={{ backgroundColor: '#1e3040', borderRadius: 4 }}>
                                    <form onSubmit={handleSubmitReview}>
                                        <div className="mb-3">
                                            <label className="form-label small fw-bold">Your Rating</label>
                                            <div>
                                                <StarRating
                                                    rating={data.rating}
                                                    size="lg"
                                                    interactive={true}
                                                    onChange={(val) => setData('rating', val)}
                                                />
                                            </div>
                                            {errors.rating && (
                                                <div className="text-danger small mt-1">{errors.rating}</div>
                                            )}
                                        </div>
                                        <div className="mb-3">
                                            <label htmlFor="reviewBody" className="form-label small fw-bold">Your Review</label>
                                            <textarea
                                                id="reviewBody"
                                                className={`form-control ${errors.body ? 'is-invalid' : ''}`}
                                                rows={4}
                                                value={data.body}
                                                onChange={(e) => setData('body', e.target.value)}
                                                placeholder="Share your thoughts about this game..."
                                                maxLength={2000}
                                            />
                                            {errors.body && (
                                                <div className="invalid-feedback">{errors.body}</div>
                                            )}
                                        </div>
                                        <button type="submit" className="btn btn-accent btn-sm" disabled={processing}>
                                            Submit Review
                                        </button>
                                    </form>
                                </div>
                            )}

                            {/* Review List */}
                            {reviews.length > 0 ? (
                                reviews.map((review) => (
                                    <ReviewCard key={review.id} review={review} />
                                ))
                            ) : (
                                <p className="text-secondary small">No reviews yet. Be the first to review!</p>
                            )}
                        </div>
                    </div>

                    {/* Sidebar */}
                    <div className="col-lg-4">
                        <div className="p-4" style={{ backgroundColor: '#1e3040', borderRadius: 4 }}>
                            {/* Price */}
                            <div className="mb-3">{displayPrice()}</div>

                            {/* Action Buttons */}
                            <div className="d-grid gap-2 mb-4">
                                <button className="btn btn-accent btn-lg fw-bold" onClick={handleAddToCart}>
                                    Add to Cart
                                </button>
                                <button className="btn btn-outline-secondary" onClick={handleToggleWishlist}>
                                    Add to Wishlist
                                </button>
                            </div>

                            {/* Game Info */}
                            <div style={{ fontSize: '0.8rem' }}>
                                <div className="d-flex justify-content-between mb-2">
                                    <span className="text-secondary">Genre</span>
                                    <span>{game.genre}</span>
                                </div>
                                <div className="d-flex justify-content-between mb-2">
                                    <span className="text-secondary">Developer</span>
                                    <span>{game.developer}</span>
                                </div>
                                <div className="d-flex justify-content-between mb-2">
                                    <span className="text-secondary">Publisher</span>
                                    <span>{game.publisher}</span>
                                </div>
                                <div className="d-flex justify-content-between mb-2">
                                    <span className="text-secondary">Release Date</span>
                                    <span>{game.release_date}</span>
                                </div>
                                <div className="d-flex justify-content-between">
                                    <span className="text-secondary">Platforms</span>
                                    <span>{renderPlatformIcons(game.platforms)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}
