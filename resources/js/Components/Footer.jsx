import { Link } from '@inertiajs/react';

export default function Footer() {
    return (
        <footer className="footer py-5 mt-auto">
            <div className="container">
                <div className="row">
                    {/* ── Left: brand + copyright ── */}
                    <div className="col-lg-5 mb-4 mb-lg-0">
                        <div className="footer-brand mb-3">steamish</div>
                        <p className="footer-copyright small lh-lg">
                            &copy; 2026 Steamish Corporation. Todos los derechos reservados.
                            Todas las marcas registradas pertenecen a sus respectivos due&ntilde;os
                            en EE. UU. y otros pa&iacute;ses.
                            <br />
                            Todos los precios incluyen IVA (donde sea aplicable).
                        </p>
                        <div className="footer-social d-flex gap-3">
                            <a href="#" className="footer-social-link" aria-label="X">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                            <a href="#" className="footer-social-link" aria-label="Facebook">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12S0 5.446 0 12.073c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073"/>
                                </svg>
                            </a>
                            <a href="#" className="footer-social-link" aria-label="Instagram">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>
                                </svg>
                            </a>
                            <a href="#" className="footer-social-link" aria-label="YouTube">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    {/* ── Right: link columns ── */}
                    <div className="col-lg-7">
                        <div className="row">
                            {/* Store */}
                            <div className="col-6 col-md-3 mb-3">
                                <h6 className="footer-heading">STORE</h6>
                                <ul className="list-unstyled mb-0">
                                    <li><Link href={route('home')} className="footer-link">Inicio</Link></li>
                                    <li><Link href={route('catalog')} className="footer-link">Cat&aacute;logo</Link></li>
                                    <li><Link href={route('catalog')} className="footer-link">Novedades</Link></li>
                                    <li><Link href="#" className="footer-link">M&aacute;s vendidos</Link></li>
                                    <li><Link href="#" className="footer-link">Pr&oacute;ximos lanzamientos</Link></li>
                                    <li><Link href="#" className="footer-link">Ofertas</Link></li>
                                </ul>
                            </div>

                            {/* Community */}
                            <div className="col-6 col-md-3 mb-3">
                                <h6 className="footer-heading">COMUNIDAD</h6>
                                <ul className="list-unstyled mb-0">
                                    <li><Link href="#" className="footer-link">Discusiones</Link></li>
                                    <li><Link href="#" className="footer-link">Taller</Link></li>
                                    <li><Link href="#" className="footer-link">Mercado</Link></li>
                                    <li><Link href={route('wishlist.index')} className="footer-link">Lista de deseos</Link></li>
                                </ul>
                            </div>

                            {/* Account */}
                            <div className="col-6 col-md-3 mb-3">
                                <h6 className="footer-heading">CUENTA</h6>
                                <ul className="list-unstyled mb-0">
                                    <li><Link href="#" className="footer-link">Mi perfil</Link></li>
                                    <li><Link href={route('library.index')} className="footer-link">Biblioteca</Link></li>
                                    <li><Link href={route('cart.index')} className="footer-link">Carrito</Link></li>
                                    <li><Link href="#" className="footer-link">Soporte</Link></li>
                                </ul>
                            </div>

                            {/* Legal */}
                            <div className="col-6 col-md-3 mb-3">
                                <h6 className="footer-heading">LEGAL</h6>
                                <ul className="list-unstyled mb-0">
                                    <li><Link href="#" className="footer-link">Pol&iacute;tica de privacidad</Link></li>
                                    <li><Link href="#" className="footer-link">T&eacute;rminos del servicio</Link></li>
                                    <li><Link href="#" className="footer-link">Pol&iacute;tica de reembolsos</Link></li>
                                    <li><Link href="#" className="footer-link">Accesibilidad</Link></li>
                                    <li><Link href="#" className="footer-link">Cookies</Link></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    );
}
