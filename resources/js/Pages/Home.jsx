import { Head, Link } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import HeroCarousel from '@/Components/HeroCarousel';
import GameCard from '@/Components/GameCard';

export default function Home({ featuredGames, newReleases, topRated, comingSoon, onSale }) {
    const sections = [
        { title: 'New Releases', games: newReleases, id: 'new-releases' },
        { title: 'Top Rated', games: topRated, id: 'top-rated' },
        { title: 'Coming Soon', games: comingSoon, id: 'coming-soon' },
        { title: 'On Sale', games: onSale, id: 'on-sale' },
    ];

    return (
        <GuestLayout>
            <Head title="Home" />

            {/* Hero Carousel */}
            <HeroCarousel games={featuredGames} />

            {/* Game Sections */}
            <div className="container py-4">
                {sections.map((section) => (
                    <section key={section.id} className="mb-5">
                        <div className="d-flex align-items-center justify-content-between mb-3">
                            <h2 className="h4 fw-bold mb-0">{section.title}</h2>
                            <Link href={route('catalog')} className="text-accent text-decoration-none small">
                                View All &rarr;
                            </Link>
                        </div>
                        <div className="row g-3">
                            {section.games.map((game) => (
                                <div key={game.id} className="col-6 col-sm-4 col-lg-2">
                                    <GameCard game={game} />
                                </div>
                            ))}
                        </div>
                    </section>
                ))}
            </div>
        </GuestLayout>
    );
}
