import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <GuestLayout>
            <Head title="Log in" />
            <div className="container py-5" style={{ maxWidth: 440 }}>
                <h2 className="mb-4 fw-bold">Sign In</h2>
                <form onSubmit={submit}>
                    <div className="mb-3">
                        <label htmlFor="email" className="form-label">Email</label>
                        <input
                            id="email"
                            type="email"
                            className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            required
                            autoFocus
                        />
                        {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                    </div>

                    <div className="mb-3">
                        <label htmlFor="password" className="form-label">Password</label>
                        <input
                            id="password"
                            type="password"
                            className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            required
                        />
                        {errors.password && <div className="invalid-feedback">{errors.password}</div>}
                    </div>

                    <div className="mb-3 form-check">
                        <input
                            id="remember"
                            type="checkbox"
                            className="form-check-input"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        <label htmlFor="remember" className="form-check-label">Remember me</label>
                    </div>

                    <button type="submit" className="btn btn-accent w-100" disabled={processing}>
                        Sign In
                    </button>

                    <p className="mt-3 text-center text-secondary">
                        Don&apos;t have an account? <Link href={route('register')} className="text-accent">Register</Link>
                    </p>
                </form>
            </div>
        </GuestLayout>
    );
}
