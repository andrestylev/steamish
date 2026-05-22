import Header from '@/Components/Header';
import Footer from '@/Components/Footer';

export default function GuestLayout({ children }) {
    return (
        <div className="d-flex flex-column min-vh-100">
            <Header />

            <main className="flex-grow-1">
                {children}
            </main>

            <Footer />
        </div>
    );
}
