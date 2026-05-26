export default function StarRating({ rating, max = 5, size = 'sm', interactive = false, onChange = null }) {
    const stars = [];

    const handleClick = (value) => {
        if (interactive && onChange) {
            onChange(value);
        }
    };

    for (let i = 1; i <= max; i++) {
        const filled = i <= rating;
        const half = !filled && i - 0.5 <= rating;

        stars.push(
            <span
                key={i}
                className={`${interactive ? 'cursor-pointer' : ''} ${interactive ? 'star-interactive' : ''}`}
                style={{
                    color: filled ? '#ffc107' : half ? '#ffc107' : '#4a5a6a',
                    fontSize: size === 'lg' ? '1.4rem' : size === 'md' ? '1.1rem' : '0.85rem',
                    cursor: interactive ? 'pointer' : 'default',
                    transition: 'color 0.1s ease',
                }}
                onClick={() => handleClick(i)}
                onMouseEnter={(e) => {
                    if (interactive) e.target.style.color = '#ffc107';
                }}
                onMouseLeave={(e) => {
                    if (interactive) e.target.style.color = i <= rating ? '#ffc107' : '#4a5a6a';
                }}
                role={interactive ? 'button' : 'presentation'}
                aria-label={interactive ? `${i} star${i > 1 ? 's' : ''}` : undefined}
                tabIndex={interactive ? 0 : undefined}
                onKeyDown={(e) => {
                    if (interactive && (e.key === 'Enter' || e.key === ' ')) {
                        e.preventDefault();
                        handleClick(i);
                    }
                }}
            >
                {filled ? '★' : half ? '★' : '☆'}
            </span>
        );
    }

    return <span className="star-rating d-inline-flex align-items-center gap-0">{stars}</span>;
}
