import StarRating from '@/Components/StarRating';

export default function ReviewCard({ review }) {
    const formatDate = (dateStr) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    };

    return (
        <div className="review-card p-3 mb-3">
            <div className="d-flex align-items-start gap-3">
                {/* Avatar */}
                <div
                    className="rounded-circle bg-secondary d-flex align-items-center justify-content-center flex-shrink-0"
                    style={{ width: 40, height: 40, fontSize: '0.85rem' }}
                >
                    {review.user.avatar ? (
                        <img
                            src={review.user.avatar}
                            alt={review.user.name}
                            className="rounded-circle w-100 h-100 object-fit-cover"
                        />
                    ) : (
                        <span className="text-white fw-bold">
                            {review.user.name.charAt(0).toUpperCase()}
                        </span>
                    )}
                </div>

                {/* Content */}
                <div className="flex-grow-1" style={{ minWidth: 0 }}>
                    <div className="d-flex align-items-center gap-2 flex-wrap">
                        <span className="fw-bold text-white small">{review.user.name}</span>
                        <span className="text-secondary" style={{ fontSize: '0.75rem' }}>
                            {review.hours_played} hrs on record
                        </span>
                    </div>

                    <div className="d-flex align-items-center gap-2 mt-1">
                        <StarRating rating={review.rating} size="sm" />
                        {review.is_recommended ? (
                            <span className="badge bg-success" style={{ fontSize: '0.65rem' }}>Recommended</span>
                        ) : (
                            <span className="badge bg-danger" style={{ fontSize: '0.65rem' }}>Not Recommended</span>
                        )}
                    </div>

                    <p className="mt-2 mb-0 small" style={{ color: '#acb8c4', lineHeight: 1.5 }}>
                        {review.body}
                    </p>

                    <span className="text-secondary mt-1 d-block" style={{ fontSize: '0.7rem' }}>
                        Posted: {formatDate(review.created_at)}
                    </span>
                </div>
            </div>
        </div>
    );
}
