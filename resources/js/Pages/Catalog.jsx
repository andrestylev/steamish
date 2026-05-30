import { useState, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
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

const SORT_OPTIONS = [
    { value: '', label: 'Default' },
    { value: 'name_asc', label: 'Name A–Z' },
    { value: 'name_desc', label: 'Name Z–A' },
    { value: 'price_asc', label: 'Price: Low to High' },
    { value: 'price_desc', label: 'Price: High to Low' },
    { value: 'newest', label: 'Newest' },
    { value: 'coming_soon', label: 'Coming Soon' },
    { value: 'rating', label: 'Top Rated' },
];

export default function Catalog({ games = [], genres, platforms, priceRange, ratings, filters: serverFilters }) {
    const [search, setSearch] = useState(serverFilters?.search || '');
    const [filters, setFilters] = useState(DEFAULT_FILTERS);
    const [sortOrder, setSortOrder] = useState(serverFilters?.sort || '');

    const safeGames = Array.isArray(games) ? games : [];

    const hasActiveFilters =
        filters.genres.length > 0 ||
        filters.priceRange.min !== null ||
        filters.platforms.length > 0 ||
        filters.minRating > 0;

    // On Sale and Coming Soon from URL override client filters
    const serverOnSale = serverFilters?.on_sale;
    const serverComingSoon = serverFilters?.coming_soon;

    const filteredGames = useMemo(() => {
        let result = [...safeGames];

        // Search
        if (search) {
            const term = search.toLowerCase();
            result = result.filter((game) => game.title.toLowerCase().includes(term));
        }

        // Genre
        if (filters.genres.length > 0) {
            result = result.filter((game) => {
                const g = game.genre || '';
                return filters.genres.some((fg) => g.toLowerCase() === fg.toLowerCase());
            });
        }

        // Price
        const minP = filters.priceRange.min;
        const maxP = filters.priceRange.max;
        if (minP !== null || maxP !== null) {
            result = result.filter((game) => {
                const p = parseFloat(game.price);
                if (p === 0) return minP === null || minP <= 0;
                if (minP !== null && p < minP) return false;
                if (maxP !== null && p > maxP) return false;
                return true;
            });
        }

        // Platform
        if (filters.platforms.length > 0) {
            result = result.filter((game) => {
                const gamePlatforms = game.platforms || [];
                return filters.platforms.some((p) => gamePlatforms.includes(p));
            });
        }

        // Rating
        if (filters.minRating > 0) {
            result = result.filter((game) => game.rating_avg >= filters.minRating);
        }

        // Apply sort
        if (sortOrder) {
            switch (sortOrder) {
                case 'name_asc':
                    result.sort((a, b) => a.title.localeCompare(b.title));
                    break;
                case 'name_desc':
                    result.sort((a, b) => b.title.localeCompare(a.title));
                    break;
                case 'price_asc':
                    result.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
                    break;
                case 'price_desc':
                    result.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
                    break;
                case 'newest':
                    result.sort((a, b) => new Date(b.release_date) - new Date(a.release_date));
                    break;
                case 'coming_soon':
                    result.sort((a, b) => new Date(a.release_date) - new Date(b.release_date));
                    break;
                case 'rating':
                    result.sort((a, b) => parseFloat(b.rating_avg) - parseFloat(a.rating_avg));
                    break;
            }
        }

        return result;
    }, [safeGames, search, filters, sortOrder]);

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
                            priceRange={priceRange}
                            ratings={ratings}
                            filters={filters}
                            onFilterChange={setFilters}
                            onClearFilters={clearFilters}
                            hasActiveFilters={hasActiveFilters}
                        />
                    </div>

                    {/* Game Grid */}
                    <div className="col-lg-9">
                        <div className="d-flex align-items-center justify-content-between mb-3">
                            <p className="text-secondary small mb-0">
                                Showing {filteredGames.length} of {safeGames.length} games
                            </p>

                            {/* Sort Dropdown */}
                            <div className="d-flex align-items-center gap-2">
                                <label htmlFor="sort-select" className="small text-secondary mb-0">
                                    Sort by:
                                </label>
                                <select
                                    id="sort-select"
                                    className="form-select form-select-sm"
                                    style={{ width: 'auto' }}
                                    value={sortOrder}
                                    onChange={(e) => setSortOrder(e.target.value)}
                                >
                                    {SORT_OPTIONS.map((opt) => (
                                        <option key={opt.value} value={opt.value}>
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        {filteredGames.length > 0 ? (
                            <div className="row g-3">
                                {filteredGames.map((game) => (
                                    <div key={game.id} className="col-6 col-sm-4 col-lg-3">
                                        <GameCard game={game} />
                                    </div>
                                ))}
                            </div>
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
                                {(hasActiveFilters || search) && (
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
