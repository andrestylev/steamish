import { Head } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function Home() {
    return (
        <GuestLayout>
            <Head title="Home" />
            <div className="container py-5 text-center">
                <h1 className="display-4 fw-bold">Welcome to Steamish</h1>
                <p className="lead text-secondary">Your digital game marketplace</p>
            </div>
        </GuestLayout>
    );
}
