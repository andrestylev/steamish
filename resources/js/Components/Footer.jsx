export default function Footer() {
    const currentYear = new Date().getFullYear();

    return (
        <footer className="footer py-4 mt-auto">
            <div className="container text-center">
                <p className="mb-0 text-secondary small">
                    &copy; {currentYear} Steamish. All rights reserved. This is a demo project.
                </p>
            </div>
        </footer>
    );
}
