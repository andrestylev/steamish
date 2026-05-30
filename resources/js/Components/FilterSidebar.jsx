export default function FilterSidebar({
    genres,
    platforms,
    priceRange,
    ratings,
    filters,
    onFilterChange,
    onClearFilters,
    hasActiveFilters,
}) {
    const toggleGenre = (genre) => {
        const updated = filters.genres.includes(genre)
            ? filters.genres.filter((g) => g !== genre)
            : [...filters.genres, genre];
        onFilterChange({ ...filters, genres: updated });
    };

    const togglePlatform = (platform) => {
        const updated = filters.platforms.includes(platform)
            ? filters.platforms.filter((p) => p !== platform)
            : [...filters.platforms, platform];
        onFilterChange({ ...filters, platforms: updated });
    };

    const setMinRating = (rating) => {
        onFilterChange({ ...filters, minRating: filters.minRating === rating ? 0 : rating });
    };

    const handlePriceChange = (e) => {
        const value = parseFloat(e.target.value);
        const side = e.target.dataset.side;
        if (side === 'min') {
            const max = filters.priceRange.max ?? priceRange.max;
            onFilterChange({
                ...filters,
                priceRange: { min: Math.min(value, max), max: filters.priceRange.max },
            });
        } else {
            const min = filters.priceRange.min ?? priceRange.min;
            onFilterChange({
                ...filters,
                priceRange: { min: filters.priceRange.min, max: Math.max(value, min) },
            });
        }
    };

    const priceMin = filters.priceRange.min ?? priceRange.min;
    const priceMax = filters.priceRange.max ?? priceRange.max;

    return (
        <div className="filter-sidebar">
            <div className="d-flex align-items-center justify-content-between mb-3">
                <h5 className="fw-bold mb-0">Filters</h5>
                {hasActiveFilters && (
                    <button
                        className="btn btn-sm btn-link text-accent text-decoration-none p-0"
                        onClick={onClearFilters}
                    >
                        Clear all
                    </button>
                )}
            </div>

            {/* Genre Filter */}
            <div className="mb-4">
                <h6 className="text-uppercase small fw-bold text-light mb-2">Genre</h6>
                <div className="d-flex flex-column gap-1">
                    {genres.map((genre) => (
                        <label key={genre} className="filter-checkbox d-flex align-items-center gap-2">
                            <input
                                type="checkbox"
                                className="form-check-input m-0"
                                checked={filters.genres.includes(genre)}
                                onChange={() => toggleGenre(genre)}
                            />
                            <span className="small">{genre}</span>
                        </label>
                    ))}
                </div>
            </div>

            {/* Price Range Slider */}
            <div className="mb-4">
                <h6 className="text-uppercase small fw-bold text-light mb-2">
                    Price: ${priceMin} — ${priceMax}
                </h6>
                <div className="price-slider-container">
                    <input
                        type="range"
                        className="price-slider price-slider-min"
                        min={priceRange.min}
                        max={priceRange.max}
                        step={1}
                        value={priceMin}
                        data-side="min"
                        onChange={handlePriceChange}
                    />
                    <input
                        type="range"
                        className="price-slider price-slider-max"
                        min={priceRange.min}
                        max={priceRange.max}
                        step={1}
                        value={priceMax}
                        data-side="max"
                        onChange={handlePriceChange}
                    />
                    <div className="price-slider-track">
                        <div
                            className="price-slider-fill"
                            style={{
                                left: `${((priceMin - priceRange.min) / (priceRange.max - priceRange.min)) * 100}%`,
                                width: `${((priceMax - priceMin) / (priceRange.max - priceRange.min)) * 100}%`,
                            }}
                        />
                    </div>
                </div>
            </div>

            {/* Platform Filter */}
            <div className="mb-4">
                <h6 className="text-uppercase small fw-bold text-light mb-2">Platform</h6>
                <div className="d-flex flex-column gap-1">
                    {platforms.map((platform) => (
                        <label key={platform.value} className="filter-checkbox d-flex align-items-center gap-2">
                            <input
                                type="checkbox"
                                className="form-check-input m-0"
                                checked={filters.platforms.includes(platform.value)}
                                onChange={() => togglePlatform(platform.value)}
                            />
                            <span className="small">{platform.label}</span>
                        </label>
                    ))}
                </div>
            </div>

            {/* Rating Filter */}
            <div className="mb-3">
                <h6 className="text-uppercase small fw-bold text-light mb-2">Minimum Rating</h6>
                <div className="d-flex flex-column gap-1">
                    {ratings.map((rating) => (
                        <button
                            key={rating}
                            className={`filter-btn btn btn-sm text-start ${filters.minRating === rating ? 'btn-accent' : 'btn-outline-secondary'}`}
                            onClick={() => setMinRating(rating)}
                        >
                            <span className="text-warning">
                                {'★'.repeat(rating)}{'☆'.repeat(5 - rating)}
                            </span>
                            <span className="text-secondary ms-1">& up</span>
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}
