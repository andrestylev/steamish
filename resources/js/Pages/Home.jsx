import { Head, Link } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import HeroCarousel from '@/Components/HeroCarousel';
import GameCarousel from '@/Components/GameCarousel';
import GenreCarousel from '@/Components/GenreCarousel';

export default function Home({ featuredGames, newReleases, topRated, comingSoon, onSale, genres }) {
    const sections = [
        { title: 'On Sale', games: onSale, id: 'on-sale', params: { on_sale: 1 } },
        { title: 'Top Rated', games: topRated, id: 'top-rated', params: { min_rating: 4 } },
        { title: 'Coming Soon', games: comingSoon, id: 'coming-soon', params: { coming_soon: 1 } },
        { title: 'New Releases', games: newReleases, id: 'new-releases', params: { sort: 'newest' } },
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
                            <Link
                                href={route('catalog', section.params)}
                                className="text-accent text-decoration-none small"
                            >
                                View All &rarr;
                            </Link>
                        </div>
                        <GameCarousel games={section.games} />
                    </section>
                ))}

                {/* Genre Carousel — always below game sections */}
                {genres?.length > 0 && (
                    <section className="mb-5">
                        <div className="d-flex align-items-center justify-content-between mb-3">
                            <h2 className="h4 fw-bold mb-0">Browse by Genre</h2>
                        </div>
                        <GenreCarousel genres={genres} />
                    </section>
                )}
            </div>
        </GuestLayout>
    );
}
