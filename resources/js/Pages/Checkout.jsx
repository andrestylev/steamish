import { useEffect, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

const STEPS = ['Payment', 'Details', 'Confirm', 'Done'];

export default function Checkout({ items, user: userData }) {
    const { flash } = usePage().props;
    const [step, setStep] = useState(0);
    const [selectedIds, setSelectedIds] = useState(items.map((i) => i.game_id));
    const [paymentMethod, setPaymentMethod] = useState('');
    const [errors, setErrors] = useState({});
    const [processing, setProcessing] = useState(false);
    const [orderNumber, setOrderNumber] = useState(flash?.order_number || '');
    const [orderCreated, setOrderCreated] = useState(flash?.order_created || false);

    // Card form
    const [card, setCard] = useState({
        number: '',
        holder: '',
        expiry: '',
        cvv: '',
    });

    // Personal info
    const [personal, setPersonal] = useState({
        name: userData.name || '',
        email: userData.email || '',
        address: '',
    });

    // If order was just created, jump to step 4
    useEffect(() => {
        if (orderCreated) {
            setStep(3);
            setOrderNumber(flash.order_number);
        }
    }, [orderCreated]);

    // ── Computed values ──
    const selectedItems = items.filter((i) => selectedIds.includes(i.game_id));
    const subtotal = selectedItems.reduce((sum, i) => sum + i.price, 0);
    const tax = subtotal * 0.19;
    const total = subtotal + tax;

    // ── Helpers ──
    const toggleItem = (gameId) => {
        setSelectedIds((prev) =>
            prev.includes(gameId) ? prev.filter((id) => id !== gameId) : [...prev, gameId]
        );
    };

    const toggleAll = () => {
        if (selectedIds.length === items.length) {
            setSelectedIds([]);
        } else {
            setSelectedIds(items.map((i) => i.game_id));
        }
    };

    const validateStep = (s) => {
        const errs = {};
        if (s === 0 && !paymentMethod) errs.payment = 'Select a payment method.';
        if (s === 1 && paymentMethod === 'card') {
            if (!card.number.trim()) errs.card_number = 'Card number is required.';
            else if (card.number.replace(/\s/g, '').length < 13) errs.card_number = 'Invalid card number.';
            if (!card.holder.trim()) errs.card_holder = 'Cardholder name is required.';
            if (!card.expiry.trim()) errs.card_expiry = 'Expiry date is required.';
            else if (!/^\d{2}\/\d{2}$/.test(card.expiry)) errs.card_expiry = 'Use MM/YY format.';
            if (!card.cvv.trim()) errs.card_cvv = 'CVV is required.';
            else if (!/^\d{3,4}$/.test(card.cvv)) errs.card_cvv = 'Invalid CVV.';
            if (!personal.name.trim()) errs.personal_name = 'Name is required.';
            if (!personal.email.trim()) errs.personal_email = 'Email is required.';
            else if (!/\S+@\S+\.\S+/.test(personal.email)) errs.personal_email = 'Invalid email.';
            if (!personal.address.trim()) errs.personal_address = 'Address is required.';
        }
        if (s === 2 && selectedIds.length === 0) errs.selection = 'Select at least one game.';
        setErrors(errs);
        return Object.keys(errs).length === 0;
    };

    const nextStep = () => {
        if (validateStep(step)) {
            setErrors({});
            if (step === 2) {
                // Submit order
                setProcessing(true);
                router.post(route('checkout.process'), { game_ids: selectedIds }, {
                    onSuccess: () => {
                        setProcessing(false);
                    },
                    onError: () => {
                        setProcessing(false);
                        setErrors({ submit: 'Failed to process payment. Try again.' });
                    },
                });
            } else {
                setStep((s) => s + 1);
            }
        }
    };

    const prevStep = () => setStep((s) => Math.max(0, s - 1));

    // ── Format card number with spaces ──
    const formatCardNumber = (val) => {
        const digits = val.replace(/\D/g, '').slice(0, 16);
        return digits.replace(/(\d{4})(?=\d)/g, '$1 ');
    };

    // ── Render helpers ──

    const renderStepIndicator = () => (
        <div className="d-flex align-items-center gap-2 mb-4">
            {STEPS.map((label, idx) => (
                <div key={label} className="d-flex align-items-center gap-2">
                    <div
                        className="d-flex align-items-center justify-content-center rounded-circle fw-bold small"
                        style={{
                            width: 28,
                            height: 28,
                            backgroundColor: idx <= step ? '#1a9fff' : '#2a475e',
                            color: idx <= step ? '#fff' : '#8f98a0',
                            fontSize: 12,
                        }}
                    >
                        {idx < step ? '✓' : idx + 1}
                    </div>
                    <span className="small" style={{ color: idx <= step ? '#c7d5e0' : '#8f98a0' }}>
                        {label}
                    </span>
                    {idx < STEPS.length - 1 && (
                        <div style={{ width: 24, height: 1, backgroundColor: '#2a475e' }} />
                    )}
                </div>
            ))}
        </div>
    );

    // ── Step content ──

    const renderStep1 = () => (
        <div>
            <h4 className="fw-bold text-white mb-3">Payment Method</h4>
            <p className="small text-secondary mb-3">Select how you want to pay.</p>

            <div className="d-flex flex-column gap-2 mb-4">
                <label
                    className="d-flex align-items-center gap-3 p-3"
                    style={{
                        backgroundColor: paymentMethod === 'card' ? '#1a9fff22' : '#1e3040',
                        borderRadius: 4,
                        border: paymentMethod === 'card' ? '1px solid #1a9fff' : '1px solid #2a475e',
                        cursor: 'pointer',
                    }}
                >
                    <input
                        type="radio"
                        name="payment"
                        value="card"
                        checked={paymentMethod === 'card'}
                        onChange={() => setPaymentMethod('card')}
                        className="form-check-input m-0"
                    />
                    <div>
                        <div className="fw-bold text-white small">Credit / Debit Card</div>
                        <div className="text-secondary" style={{ fontSize: 11 }}>Visa, Mastercard, American Express</div>
                    </div>
                </label>

                <label
                    className="d-flex align-items-center gap-3 p-3"
                    style={{
                        backgroundColor: paymentMethod === 'paypal' ? '#1a9fff22' : '#1e3040',
                        borderRadius: 4,
                        border: paymentMethod === 'paypal' ? '1px solid #1a9fff' : '1px solid #2a475e',
                        cursor: 'pointer',
                    }}
                >
                    <input
                        type="radio"
                        name="payment"
                        value="paypal"
                        checked={paymentMethod === 'paypal'}
                        onChange={() => setPaymentMethod('paypal')}
                        className="form-check-input m-0"
                    />
                    <div>
                        <div className="fw-bold text-white small">PayPal</div>
                        <div className="text-secondary" style={{ fontSize: 11 }}>Pay with your PayPal account</div>
                    </div>
                </label>
            </div>

            {errors.payment && <div className="text-danger small mb-3">{errors.payment}</div>}
        </div>
    );

    const renderCardFields = () => (
        <div className="row g-3 mb-3">
            <div className="col-12">
                <label className="form-label small text-secondary">Card Number</label>
                <input
                    type="text"
                    className={`form-control ${errors.card_number ? 'is-invalid' : ''}`}
                    placeholder="1234 5678 9012 3456"
                    value={card.number}
                    onChange={(e) => setCard({ ...card, number: formatCardNumber(e.target.value) })}
                    maxLength={19}
                />
                {errors.card_number && <div className="invalid-feedback">{errors.card_number}</div>}
            </div>
            <div className="col-12">
                <label className="form-label small text-secondary">Cardholder Name</label>
                <input
                    type="text"
                    className={`form-control ${errors.card_holder ? 'is-invalid' : ''}`}
                    placeholder="John Doe"
                    value={card.holder}
                    onChange={(e) => setCard({ ...card, holder: e.target.value })}
                />
                {errors.card_holder && <div className="invalid-feedback">{errors.card_holder}</div>}
            </div>
            <div className="col-6">
                <label className="form-label small text-secondary">Expiry Date</label>
                <input
                    type="text"
                    className={`form-control ${errors.card_expiry ? 'is-invalid' : ''}`}
                    placeholder="MM/YY"
                    value={card.expiry}
                    onChange={(e) => {
                        const v = e.target.value.replace(/[^\d]/g, '').slice(0, 4);
                        if (v.length > 2) {
                            setCard({ ...card, expiry: v.slice(0, 2) + '/' + v.slice(2) });
                        } else {
                            setCard({ ...card, expiry: v });
                        }
                    }}
                    maxLength={5}
                />
                {errors.card_expiry && <div className="invalid-feedback">{errors.card_expiry}</div>}
            </div>
            <div className="col-6">
                <label className="form-label small text-secondary">CVV</label>
                <input
                    type="text"
                    className={`form-control ${errors.card_cvv ? 'is-invalid' : ''}`}
                    placeholder="123"
                    value={card.cvv}
                    onChange={(e) => setCard({ ...card, cvv: e.target.value.replace(/\D/g, '').slice(0, 4) })}
                    maxLength={4}
                />
                {errors.card_cvv && <div className="invalid-feedback">{errors.card_cvv}</div>}
            </div>
        </div>
    );

    const renderPersonalFields = () => (
        <div className="row g-3">
            <div className="col-12">
                <h6 className="fw-bold text-white mb-2">Personal Information</h6>
            </div>
            <div className="col-6">
                <label className="form-label small text-secondary">Full Name</label>
                <input
                    type="text"
                    className={`form-control ${errors.personal_name ? 'is-invalid' : ''}`}
                    value={personal.name}
                    onChange={(e) => setPersonal({ ...personal, name: e.target.value })}
                />
                {errors.personal_name && <div className="invalid-feedback">{errors.personal_name}</div>}
            </div>
            <div className="col-6">
                <label className="form-label small text-secondary">Email</label>
                <input
                    type="email"
                    className={`form-control ${errors.personal_email ? 'is-invalid' : ''}`}
                    value={personal.email}
                    onChange={(e) => setPersonal({ ...personal, email: e.target.value })}
                />
                {errors.personal_email && <div className="invalid-feedback">{errors.personal_email}</div>}
            </div>
            <div className="col-12">
                <label className="form-label small text-secondary">Address</label>
                <input
                    type="text"
                    className={`form-control ${errors.personal_address ? 'is-invalid' : ''}`}
                    placeholder="Street, City, ZIP, Country"
                    value={personal.address}
                    onChange={(e) => setPersonal({ ...personal, address: e.target.value })}
                />
                {errors.personal_address && <div className="invalid-feedback">{errors.personal_address}</div>}
            </div>
        </div>
    );

    const renderStep2Card = () => (
        <div>
            <h4 className="fw-bold text-white mb-3">Card Details</h4>
            {renderCardFields()}
            <hr style={{ borderColor: '#2a475e' }} />
            {renderPersonalFields()}
        </div>
    );

    const renderStep2PayPal = () => (
        <div className="text-center py-5">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#0070ba" viewBox="0 0 16 16">
                <path d="M12.5 0H5.514a1.5 1.5 0 0 0-1.481 1.236L2.259 11.002a.5.5 0 0 0 .492.498h2.966l.776-3.97h2.942a3.664 3.664 0 0 0 3.468-2.562A3.162 3.162 0 0 0 12.5 0z"/>
            </svg>
            <h5 className="fw-bold text-white mt-3 mb-2">Redirecting to PayPal</h5>
            <p className="text-secondary small mb-0" style={{ maxWidth: 360, margin: '0 auto' }}>
                You will be redirected to PayPal to complete your payment securely.
                Please do not close this window.
            </p>
            <div className="mt-4 text-secondary small">
                <div className="spinner-border spinner-border-sm me-2" role="status" />
                Preparing secure connection...
            </div>
        </div>
    );

    const renderStep3 = () => {
        const selItems = items.filter((i) => selectedIds.includes(i.game_id));
        const sub = selItems.reduce((s, i) => s + i.price, 0);
        const tx = sub * 0.19;
        const tot = sub + tx;

        return (
            <div>
                <h4 className="fw-bold text-white mb-3">Confirm Your Order</h4>
                <p className="small text-secondary mb-3">Review your order before paying.</p>

                <div className="mb-3">
                    <div className="fw-bold small text-secondary mb-2">Payment Method</div>
                    <div className="text-white small">
                        {paymentMethod === 'card' ? '💳 Credit / Debit Card' : '💸 PayPal'}
                    </div>
                </div>

                <div className="fw-bold small text-secondary mb-2">Items ({selItems.length})</div>
                {selItems.map((item) => (
                    <div key={item.game_id} className="d-flex align-items-center gap-2 mb-2">
                        <div style={{ width: 60, height: 34, borderRadius: 2, overflow: 'hidden', flexShrink: 0, backgroundColor: '#2a475e' }}>
                            <img src={item.cover} alt={item.title} className="w-100 h-100" style={{ objectFit: 'cover' }} />
                        </div>
                        <div className="flex-grow-1 small text-white">{item.title}</div>
                        <div className="small text-white fw-bold">${item.price.toFixed(2)}</div>
                    </div>
                ))}

                <hr style={{ borderColor: '#2a475e' }} />

                <div className="d-flex justify-content-between small mb-1">
                    <span className="text-secondary">Subtotal</span>
                    <span className="text-white">${sub.toFixed(2)}</span>
                </div>
                <div className="d-flex justify-content-between small mb-1">
                    <span className="text-secondary">IVA (19%)</span>
                    <span className="text-white">${tx.toFixed(2)}</span>
                </div>
                <div className="d-flex justify-content-between fw-bold">
                    <span className="text-white">Total</span>
                    <span className="text-accent fs-5">${tot.toFixed(2)}</span>
                </div>
            </div>
        );
    };

    const renderStep4 = () => (
        <div className="text-center py-4">
            <div
                className="d-flex align-items-center justify-content-center rounded-circle mx-auto mb-3"
                style={{ width: 64, height: 64, backgroundColor: '#1a9fff22' }}
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#1a9fff" viewBox="0 0 16 16">
                    <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                </svg>
            </div>
            <h4 className="fw-bold text-white mb-2">Payment Successful!</h4>
            <p className="text-secondary small mb-1">Thank you for your purchase.</p>
            <p className="small mb-4">
                <span className="text-secondary">Order number: </span>
                <span className="text-accent fw-bold">{orderNumber}</span>
            </p>
            <Link href={route('library.index')} className="btn btn-accent">
                View My Library
            </Link>
        </div>
    );

    const renderStepBody = () => {
        if (step === 0) return renderStep1();
        if (step === 1) return paymentMethod === 'card' ? renderStep2Card() : renderStep2PayPal();
        if (step === 2) return renderStep3();
        if (step === 3) return renderStep4();
        return null;
    };

    const canContinue = () => {
        if (step === 0) return !!paymentMethod;
        if (step === 1) {
            if (paymentMethod === 'paypal') return true;
            return (
                card.number.replace(/\s/g, '').length >= 13 &&
                card.holder.trim() &&
                /^\d{2}\/\d{2}$/.test(card.expiry) &&
                /^\d{3,4}$/.test(card.cvv) &&
                personal.name.trim() &&
                /\S+@\S+\.\S+/.test(personal.email) &&
                personal.address.trim()
            );
        }
        return true;
    };

    return (
        <GuestLayout>
            <Head title="Checkout" />

            <div className="container py-4">
                {orderCreated ? (
                    /* ── Success full-width ── */
                    <div className="row justify-content-center">
                        <div className="col-lg-6">{renderStep4()}</div>
                    </div>
                ) : (
                    /* ── Two-column layout ── */
                    <div className="row g-4">
                        {/* Left: Multi-step form */}
                        <div className="col-lg-7">
                            <div
                                className="p-4"
                                style={{ backgroundColor: '#1e3040', borderRadius: 4 }}
                            >
                                {renderStepIndicator()}
                                {renderStepBody()}

                                {step < 3 && (
                                    <div className="d-flex justify-content-between mt-4 pt-3" style={{ borderTop: '1px solid #2a475e' }}>
                                        <div>
                                            {step > 0 && (
                                                <button className="btn btn-outline-secondary btn-sm" onClick={prevStep}>
                                                    Back
                                                </button>
                                            )}
                                        </div>
                                        <button
                                            className="btn btn-accent btn-sm px-4"
                                            onClick={nextStep}
                                            disabled={!canContinue() || processing}
                                        >
                                            {processing ? (
                                                <span>
                                                    <span className="spinner-border spinner-border-sm me-1" role="status" />
                                                    Processing...
                                                </span>
                                            ) : step === 2 ? (
                                                'Pay Now'
                                            ) : (
                                                'Continue'
                                            )}
                                        </button>
                                    </div>
                                )}

                                {errors.selection && (
                                    <div className="text-danger small mt-2">{errors.selection}</div>
                                )}
                                {errors.submit && (
                                    <div className="text-danger small mt-2">{errors.submit}</div>
                                )}
                            </div>
                        </div>

                        {/* Right: Cart summary */}
                        <div className="col-lg-5">
                            <div
                                className="p-4"
                                style={{ backgroundColor: '#1e3040', borderRadius: 4, position: 'sticky', top: 80 }}
                            >
                                <h5 className="fw-bold text-white mb-3">Order Summary</h5>

                                {/* Select All */}
                                <div className="d-flex align-items-center gap-2 mb-2 pb-2" style={{ borderBottom: '1px solid #2a475e' }}>
                                    <input
                                        type="checkbox"
                                        className="form-check-input m-0"
                                        checked={selectedIds.length === items.length}
                                        onChange={toggleAll}
                                    />
                                    <span className="small text-secondary">Select all ({items.length})</span>
                                </div>

                                {/* Items */}
                                <div style={{ maxHeight: 260, overflowY: 'auto' }}>
                                    {items.map((item) => (
                                        <label
                                            key={item.game_id}
                                            className="d-flex align-items-center gap-2 py-2"
                                            style={{ cursor: 'pointer', borderBottom: '1px solid rgba(42,71,94,0.5)' }}
                                        >
                                            <input
                                                type="checkbox"
                                                className="form-check-input m-0"
                                                checked={selectedIds.includes(item.game_id)}
                                                onChange={() => toggleItem(item.game_id)}
                                            />
                                            <div style={{ width: 60, height: 34, borderRadius: 2, overflow: 'hidden', flexShrink: 0, backgroundColor: '#2a475e' }}>
                                                <img src={item.cover} alt={item.title} className="w-100 h-100" style={{ objectFit: 'cover' }} />
                                            </div>
                                            <div className="flex-grow-1" style={{ minWidth: 0 }}>
                                                <div className="small text-white text-truncate">{item.title}</div>
                                            </div>
                                            <div className="small text-white fw-bold text-nowrap">${item.price.toFixed(2)}</div>
                                        </label>
                                    ))}
                                </div>

                                {selectedIds.length === 0 && (
                                    <p className="text-secondary small text-center py-2 mb-0">No games selected</p>
                                )}

                                {/* Totals */}
                                {selectedIds.length > 0 && (
                                    <>
                                        <hr style={{ borderColor: '#2a475e' }} />
                                        <div className="d-flex justify-content-between small mb-1">
                                            <span className="text-secondary">Subtotal</span>
                                            <span className="text-white">${subtotal.toFixed(2)}</span>
                                        </div>
                                        <div className="d-flex justify-content-between small mb-1">
                                            <span className="text-secondary">IVA (19%)</span>
                                            <span className="text-white">${tax.toFixed(2)}</span>
                                        </div>
                                        <div className="d-flex justify-content-between fw-bold mt-2">
                                            <span className="text-white">Total</span>
                                            <span className="text-accent fs-5">${total.toFixed(2)}</span>
                                        </div>
                                    </>
                                )}

                                <div className="mt-3">
                                    <Link href={route('cart.index')} className="text-accent small text-decoration-none">
                                        ← Back to Cart
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </GuestLayout>
    );
}
