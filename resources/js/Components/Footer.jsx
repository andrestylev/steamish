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
                            &copy; 2026 Valve Corporation. Todos los derechos reservados.
                            Todas las marcas registradas pertenecen a sus respectivos due&ntilde;os
                            en EE. UU. y otros pa&iacute;ses.
                            <br />
                            Todos los precios incluyen IVA (donde sea aplicable).
                        </p>
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

                {/* ── Bottom legal bar ── */}
                <div className="footer-legal pt-3 mt-3 text-center text-md-start">
                    <span className="text-muted small">
                        &copy; 2026 Valve Corporation. Todos los derechos reservados.
                        Todas las marcas registradas pertenecen a sus respectivos due&ntilde;os
                        en EE. UU. y otros pa&iacute;ses.
                    </span>
                </div>
            </div>
        </footer>
    );
}
