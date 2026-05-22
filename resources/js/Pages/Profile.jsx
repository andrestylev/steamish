import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Profile({ user }) {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name || '',
        username: user.username || '',
        avatar: user.avatar || '',
        bio: user.bio || '',
        timezone: user.timezone || '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('profile.update'));
    };

    return (
        <AuthenticatedLayout>
            <Head title="Profile" />
            <div className="container py-4" style={{ maxWidth: 600 }}>
                <h2 className="mb-4 fw-bold">Edit Profile</h2>
                <form onSubmit={submit}>
                    <div className="mb-3">
                        <label htmlFor="name" className="form-label">Display Name</label>
                        <input
                            id="name"
                            type="text"
                            className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                        />
                        {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                    </div>

                    <div className="mb-3">
                        <label htmlFor="username" className="form-label">Username</label>
                        <input
                            id="username"
                            type="text"
                            className={`form-control ${errors.username ? 'is-invalid' : ''}`}
                            value={data.username}
                            onChange={(e) => setData('username', e.target.value)}
                            required
                        />
                        {errors.username && <div className="invalid-feedback">{errors.username}</div>}
                    </div>

                    <div className="mb-3">
                        <label htmlFor="avatar" className="form-label">Avatar URL</label>
                        <input
                            id="avatar"
                            type="url"
                            className={`form-control ${errors.avatar ? 'is-invalid' : ''}`}
                            value={data.avatar}
                            onChange={(e) => setData('avatar', e.target.value)}
                        />
                        {errors.avatar && <div className="invalid-feedback">{errors.avatar}</div>}
                    </div>

                    <div className="mb-3">
                        <label htmlFor="bio" className="form-label">Bio</label>
                        <textarea
                            id="bio"
                            className={`form-control ${errors.bio ? 'is-invalid' : ''}`}
                            rows={4}
                            value={data.bio}
                            onChange={(e) => setData('bio', e.target.value)}
                            maxLength={1000}
                        />
                        {errors.bio && <div className="invalid-feedback">{errors.bio}</div>}
                    </div>

                    <div className="mb-3">
                        <label htmlFor="timezone" className="form-label">Timezone</label>
                        <select
                            id="timezone"
                            className={`form-select ${errors.timezone ? 'is-invalid' : ''}`}
                            value={data.timezone}
                            onChange={(e) => setData('timezone', e.target.value)}
                        >
                            <option value="">Select timezone</option>
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">America/New_York</option>
                            <option value="America/Chicago">America/Chicago</option>
                            <option value="America/Denver">America/Denver</option>
                            <option value="America/Los_Angeles">America/Los_Angeles</option>
                            <option value="Europe/London">Europe/London</option>
                            <option value="Europe/Paris">Europe/Paris</option>
                            <option value="Europe/Berlin">Europe/Berlin</option>
                            <option value="Asia/Tokyo">Asia/Tokyo</option>
                            <option value="Asia/Shanghai">Asia/Shanghai</option>
                            <option value="Australia/Sydney">Australia/Sydney</option>
                            <option value="Pacific/Auckland">Pacific/Auckland</option>
                        </select>
                        {errors.timezone && <div className="invalid-feedback">{errors.timezone}</div>}
                    </div>

                    <button type="submit" className="btn btn-accent" disabled={processing}>
                        Save Changes
                    </button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
