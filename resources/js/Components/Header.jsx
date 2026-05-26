import { Link, usePage } from '@inertiajs/react';

export default function Header() {
    const { auth, cartCount } = usePage().props;

    return (
        <header className="header">
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

                        <form className="d-flex me-3" role="search">
                            <input
                                className="form-control form-control-sm search-input"
                                type="search"
                                placeholder="Search games..."
                                aria-label="Search"
                            />
                        </form>

                        <ul className="navbar-nav">
                            {auth.user ? (
                                <>
                                    <li className="nav-item">
                                        <Link href={route('library.index')} className="nav-link">
                                            Library
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
        </header>
    );
}
