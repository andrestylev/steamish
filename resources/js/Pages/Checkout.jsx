import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Checkout({ cartItems, subtotal, itemCount, checkoutUrl }) {
    // If we have a checkoutUrl, redirect to Stripe
    if (checkoutUrl) {
        window.location.href = checkoutUrl;
        return null;
    }

    return (
        <AuthenticatedLayout>
            <Head title="Checkout" />

            <div className="container py-5">
                <div className="row justify-content-center">
                    <div className="col-lg-6">
                        <div className="text-center py-5">
                            <h2 className="fw-bold mb-3">Checkout</h2>
                            <p className="text-secondary mb-4">
                                Review your order and proceed to payment.
                            </p>
                        </div>

                        {/* Order Summary */}
                        <div className="p-4 mb-4" style={{ backgroundColor: '#1e3040', borderRadius: 4 }}>
                            <h5 className="fw-bold mb-3">Order Summary</h5>

                            {cartItems.map((item) => (
                                <div key={item.id} className="d-flex justify-content-between align-items-center mb-2 small">
                                    <span>{item.game?.title || 'Unknown Game'}</span>
                                    <span className="fw-bold">
                                        ${item.game?.is_discounted
                                            ? parseFloat(item.game.discount_price).toFixed(2)
                                            : parseFloat(item.game?.price || 0).toFixed(2)}
                                    </span>
                                </div>
                            ))}

                            <hr style={{ borderColor: 'rgba(255,255,255,0.1)' }} />

                            <div className="d-flex justify-content-between fw-bold">
                                <span>Total</span>
                                <span className="fs-5 text-white">${parseFloat(subtotal).toFixed(2)}</span>
                            </div>
                        </div>

                        {/* Payment Button */}
                        <div className="d-grid gap-2">
                            <Link
                                href={route('checkout.index')}
                                method="get"
                                as="button"
                                className="btn btn-accent btn-lg fw-bold"
                            >
                                Pay with Stripe
                            </Link>
                            <div className="text-center mt-2">
                                <Link href={route('cart.index')} className="text-accent small text-decoration-none">
                                    Back to Cart
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
