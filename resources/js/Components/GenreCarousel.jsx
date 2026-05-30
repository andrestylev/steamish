import { useRef, useState, useEffect } from 'react';
import { router } from '@inertiajs/react';

const GRADIENTS = {
    Action: 'linear-gradient(135deg, #3d1a1a 0%, #6b2a2a 50%, #8a3a3a 100%)',
    RPG: 'linear-gradient(135deg, #1a1a3d 0%, #2a2a6b 50%, #3a3a8a 100%)',
    Adventure: 'linear-gradient(135deg, #1a3d1a 0%, #2a6b2a 50%, #3a8a3a 100%)',
    Strategy: 'linear-gradient(135deg, #1a3d3d 0%, #2a6b6b 50%, #3a8a8a 100%)',
    Shooter: 'linear-gradient(135deg, #3d2a1a 0%, #6b4a2a 50%, #8a5a3a 100%)',
    Simulation: 'linear-gradient(135deg, #1a2a3d 0%, #2a4a6b 50%, #3a6a8a 100%)',
    Sports: 'linear-gradient(135deg, #2a3d1a 0%, #4a6b2a 50%, #6a8a3a 100%)',
    Puzzle: 'linear-gradient(135deg, #3d1a3d 0%, #6b2a6b 50%, #8a3a8a 100%)',
    Racing: 'linear-gradient(135deg, #1a3d2a 0%, #2a6b4a 50%, #3a8a6a 100%)',
    Horror: 'linear-gradient(135deg, #2a1a1a 0%, #4a2a2a 50%, #6a2a2a 100%)',
    Fighting: 'linear-gradient(135deg, #3d2a2a 0%, #6b3a3a 50%, #8a4a4a 100%)',
    Indie: 'linear-gradient(135deg, #2a1a3d 0%, #4a2a6b 50%, #6a3a8a 100%)',
    Arcade: 'linear-gradient(135deg, #1a2a2a 0%, #2a4a4a 50%, #3a6a6a 100%)',
    FPS: 'linear-gradient(135deg, #3d1a0a 0%, #6b2a1a 50%, #8a3a2a 100%)',
    MOBA: 'linear-gradient(135deg, #1a1a2a 0%, #2a2a5a 50%, #3a3a7a 100%)',
};

const FALLBACK = 'linear-gradient(135deg, #1e3040 0%, #2a475e 50%, #3a5a7a 100%)';

export default function GenreCarousel({ genres }) {
    const scrollRef = useRef(null);
    const [canScrollLeft, setCanScrollLeft] = useState(false);
    const [canScrollRight, setCanScrollRight] = useState(false);

    const checkScroll = () => {
        const el = scrollRef.current;
        if (!el) return;
        setCanScrollLeft(el.scrollLeft > 4);
        setCanScrollRight(el.scrollLeft < el.scrollWidth - el.clientWidth - 4);
    };

    useEffect(() => {
        const el = scrollRef.current;
        if (!el) return;
        checkScroll();
        el.addEventListener('scroll', checkScroll, { passive: true });
        window.addEventListener('resize', checkScroll);
        return () => {
            el.removeEventListener('scroll', checkScroll);
            window.removeEventListener('resize', checkScroll);
        };
    }, [genres]);

    function scroll(dir) {
        const el = scrollRef.current;
        if (!el) return;
        const card = el.querySelector('.genre-card');
        const step = card ? card.offsetWidth + 12 : 140;
        el.scrollBy({ left: dir * step, behavior: 'smooth' });
    }

    const handleClick = (slug) => {
        router.visit(route('catalog', { genre: slug }));
    };

    return (
        <div className="genre-carousel position-relative">
            {canScrollLeft && (
                <button
                    className="game-carousel-arrow game-carousel-arrow-left"
                    onClick={() => scroll(-1)}
                    aria-label="Scroll left"
                >
                    <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                </button>
            )}
            {canScrollRight && (
                <button
                    className="game-carousel-arrow game-carousel-arrow-right"
                    onClick={() => scroll(1)}
                    aria-label="Scroll right"
                >
                    <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>
            )}

            <div className="genre-carousel-track" ref={scrollRef}>
                {genres.map((genre) => (
                    <button
                        key={genre.slug}
                        className="genre-card"
                        onClick={() => handleClick(genre.slug)}
                        style={{
                            background: GRADIENTS[genre.name] || FALLBACK,
                        }}
                    >
                        <span className="genre-card-label">{genre.name}</span>
                    </button>
                ))}
            </div>
        </div>
    );
}
