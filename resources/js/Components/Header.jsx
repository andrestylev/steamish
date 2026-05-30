import { Link, router, usePage } from '@inertiajs/react';

export default function Header() {
    const { auth, cartCount } = usePage().props;

    function handleSearch(e) {
        e.preventDefault();
        const form = new FormData(e.target);
        const q = form.get('q')?.trim();
        if (q) {
            router.visit(route('catalog', { search: q }));
        }
    }

    return (
        <header className="header">
            {/* Main Navbar */}
            <nav className="navbar navbar-expand-lg">
                <div className="container">
                    <Link href={route('home')} className="navbar-brand fw-bold text-accent">
                        Steamish
                    </Link>

                    <button
                        className="navbar-toggler"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#navbarNav"
                        aria-controls="navbarNav"
                        aria-expanded="false"
                        aria-label="Toggle navigation"
                    >
                        <span className="navbar-toggler-icon"></span>
                    </button>

                    <div className="collapse navbar-collapse" id="navbarNav">
                        <ul className="navbar-nav me-auto">
                            <li className="nav-item">
                                <Link href={route('home')} className="nav-link">
                                    Home
                                </Link>
                            </li>
                            <li className="nav-item">
                                <Link href={route('catalog')} className="nav-link">
                                    Catalog
                                </Link>
                            </li>
                        </ul>

                        <ul className="navbar-nav">
                            {auth.user ? (
                                <>
                                    <li className="nav-item">
                                        <Link href={route('library.index')} className="nav-link">
                                            Library
                                        </Link>
                                    </li>
                                    <li className="nav-item">
                                        <Link href={route('wishlist.index')} className="nav-link">
                                            Wishlist
                                        </Link>
                                    </li>
                                    <li className="nav-item">
                                        <Link href={route('cart.index')} className="nav-link position-relative">
                                            Cart
                                            {cartCount > 0 && (
                                                <span className="badge bg-accent ms-1">{cartCount}</span>
                                            )}
                                        </Link>
                                    </li>
                                    <li className="nav-item">
                                        <Link href={route('profile.edit')} className="nav-link">
                                            Profile
                                        </Link>
                                    </li>
                                    <li className="nav-item">
                                        <Link href={route('logout')} method="post" as="button" className="nav-link btn btn-link text-decoration-none">
                                            Logout
                                        </Link>
                                    </li>
                                </>
                            ) : (
                                <>
                                    <li className="nav-item">
                                        <Link href={route('login')} className="nav-link">
                                            Login
                                        </Link>
                                    </li>
                                    <li className="nav-item">
                                        <Link href={route('register')} className="nav-link">
                                            Register
                                        </Link>
                                    </li>
                                </>
                            )}
                        </ul>
                    </div>
                </div>
            </nav>

            {/* Sub Navbar — Wishlist + Search */}
            <div className="subnav py-1">
                <div className="container d-flex align-items-center gap-3">
                    <Link href={route('wishlist.index')} className="subnav-link d-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                            <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                        </svg>
                        Wishlist
                    </Link>

                    <form className="d-flex flex-grow-1" role="search" onSubmit={handleSearch}>
                        <div className="input-group input-group-sm">
                            <input
                                className="form-control subnav-search"
                                type="search"
                                name="q"
                                placeholder="Search the store..."
                                aria-label="Search"
                            />
                            <button className="btn btn-accent text-white" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </header>
    );
}
