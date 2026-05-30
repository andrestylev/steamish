import { Head, Link } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import HeroCarousel from '@/Components/HeroCarousel';
import GameCarousel from '@/Components/GameCarousel';

export default function Home({ featuredGames, newReleases, topRated, comingSoon, onSale }) {
    const sections = [
        { title: 'On Sale', games: onSale, id: 'on-sale' },
        { title: 'Top Rated', games: topRated, id: 'top-rated' },
        { title: 'Coming Soon', games: comingSoon, id: 'coming-soon' },
        { title: 'New Releases', games: newReleases, id: 'new-releases' },
    ];

    return (
        <GuestLayout>
            <Head title="Home" />

            {/* Hero Carousel */}
            <HeroCarousel games={featuredGames} />

            {/* Game Sections — only show sections with games */}
            <div className="container py-4">
                {sections.filter((s) => s.games.length > 0).map((section) => (
                    <section key={section.id} className="mb-5">
                        <div className="d-flex align-items-center justify-content-between mb-3">
                            <h2 className="h4 fw-bold mb-0">{section.title}</h2>
                            <Link href={route('catalog')} className="text-accent text-decoration-none small">
                                View All &rarr;
                            </Link>
                        </div>
                        <GameCarousel games={section.games} />
                    </section>
                ))}
            </div>
        </GuestLayout>
    );
}
