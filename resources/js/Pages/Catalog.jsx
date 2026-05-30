import { useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import GameCard from '@/Components/GameCard';
import SearchBar from '@/Components/SearchBar';
import FilterSidebar from '@/Components/FilterSidebar';

const DEFAULT_FILTERS = {
    genres: [],
    priceRange: { min: null, max: null },
    platforms: [],
    minRating: 0,
};

export default function Catalog({ games, genres, platforms, priceRanges, ratings, filters: serverFilters }) {
    const [search, setSearch] = useState(serverFilters?.search || '');
    const [filters, setFilters] = useState(DEFAULT_FILTERS);

    const hasActiveFilters =
        filters.genres.length > 0 ||
        filters.priceRange.min !== null ||
        filters.platforms.length > 0 ||
        filters.minRating > 0;

    const filteredGames = useMemo(() => {
        return games.filter((game) => {
            // Search by name (case-insensitive)
            if (search) {
                const term = search.toLowerCase();
                if (!game.title.toLowerCase().includes(term)) return false;
            }

            // Genre filter
            if (filters.genres.length > 0 && (!game.genre || !filters.genres.includes(game.genre))) {
                return false;
            }

            // Price filter
            const price = parseFloat(game.price);
            if (price === 0 && filters.priceRange.min !== null) {
                // Free games — only include if min is 0
                if (filters.priceRange.min > 0) return false;
            } else {
                if (filters.priceRange.min !== null && price < filters.priceRange.min) return false;
                if (filters.priceRange.max !== null && price > filters.priceRange.max) return false;
            }

            // Platform filter
            if (filters.platforms.length > 0) {
                const gamePlatforms = game.platforms || [];
                const hasPlatform = filters.platforms.some((p) => gamePlatforms.includes(p));
                if (!hasPlatform) return false;
            }

            // Rating filter
            if (filters.minRating > 0 && game.rating_avg < filters.minRating) return false;

            return true;
        });
    }, [games, search, filters]);

    const clearFilters = () => {
        setFilters(DEFAULT_FILTERS);
        setSearch('');
    };

    return (
        <GuestLayout>
            <Head title="Catalog" />

            <div className="container py-4">
                <h1 className="h3 fw-bold mb-4">Game Catalog</h1>

                {/* Search Bar */}
                <SearchBar value={search} onChange={setSearch} />

                <div className="row">
                    {/* Filter Sidebar */}
                    <div className="col-lg-3 mb-4">
                        <FilterSidebar
                            genres={genres}
                            platforms={platforms}
                            priceRanges={priceRanges}
                            ratings={ratings}
                            filters={filters}
                            onFilterChange={setFilters}
                            onClearFilters={clearFilters}
                            hasActiveFilters={hasActiveFilters}
                        />
                    </div>

                    {/* Game Grid */}
                    <div className="col-lg-9">
                        {filteredGames.length > 0 ? (
                            <>
                                <p className="text-secondary small mb-3">
                                    Showing {filteredGames.length} of {games.length} games
                                </p>
                                <div className="row g-3">
                                    {filteredGames.map((game) => (
                                        <div key={game.id} className="col-6 col-sm-4 col-lg-3">
                                            <GameCard game={game} />
                                        </div>
                                    ))}
                                </div>
                            </>
                        ) : (
                            <div className="text-center py-5">
                                <div className="mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" className="text-secondary" viewBox="0 0 16 16">
                                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                    </svg>
                                </div>
                                <h5 className="text-secondary">No games found</h5>
                                <p className="text-secondary small mb-3">
                                    Try adjusting your search or filters to find what you are looking for.
                                </p>
                                {hasActiveFilters && (
                                    <button className="btn btn-accent btn-sm" onClick={clearFilters}>
                                        Clear Filters
                                    </button>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}
