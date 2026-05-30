import { useRef, useState, useEffect } from 'react';
import GameCard from '@/Components/GameCard';

export default function GameCarousel({ games }) {
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
    }, [games]);

    function scroll(dir) {
        const el = scrollRef.current;
        if (!el) return;
        const card = el.querySelector('.game-carousel-card');
        const step = card ? card.offsetWidth + 16 : 232; // card + gap
        el.scrollBy({ left: dir * step, behavior: 'smooth' });
    }

    return (
        <div className="game-carousel position-relative">
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

            <div className="game-carousel-track" ref={scrollRef}>
                {games.map((game) => (
                    <div key={game.id} className="game-carousel-card">
                        <GameCard game={game} />
                    </div>
                ))}
            </div>
        </div>
    );
}
