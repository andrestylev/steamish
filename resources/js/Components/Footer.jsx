import { Link } from '@inertiajs/react';

export default function Footer() {
    const currentYear = new Date().getFullYear();

    return (
        <footer className="footer py-4 mt-auto">
            <div className="container">
                <div className="row align-items-center">
                    <div className="col-md-6 text-center text-md-start mb-2 mb-md-0">
                        <p className="mb-0 text-secondary small">
                            &copy; {currentYear} Steamish. All rights reserved.
                        </p>
                    </div>
                    <div className="col-md-6 text-center text-md-end">
                        <Link href="#" className="footer-link me-3">Support</Link>
                        <Link href="#" className="footer-link me-3">Privacy Policy</Link>
                        <Link href="#" className="footer-link">Terms of Service</Link>
                    </div>
                </div>
            </div>
        </footer>
    );
}
