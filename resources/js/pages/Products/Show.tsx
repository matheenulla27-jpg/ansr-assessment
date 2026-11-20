import { useCallback, useEffect, useRef, useState } from 'react';
import EnquiryForm from '../../components/catalogue/EnquiryForm';
import AppLayout from '../../layouts/AppLayout';
import type { ProductDetailPageProps } from '../../types/catalogue';

export default function Show({ product }: ProductDetailPageProps) {
    const [isEnquiryFormVisible, setIsEnquiryFormVisible] = useState(false);
    const enquiryButtonRef = useRef<HTMLButtonElement>(null);
    const enquiryDialogRef = useRef<HTMLDivElement>(null);

    const closeEnquiryDialog = useCallback(() => {
        setIsEnquiryFormVisible(false);
    }, []);

    useEffect(() => {
        if (!isEnquiryFormVisible) {
            return;
        }

        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        enquiryDialogRef.current?.focus();

        function handleKeyDown(event: KeyboardEvent) {
            if (event.key === 'Escape') {
                event.preventDefault();
                closeEnquiryDialog();
                return;
            }

            if (event.key !== 'Tab') {
                return;
            }

            const focusableElements =
                enquiryDialogRef.current?.querySelectorAll<HTMLElement>(
                    'a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])',
                );
            const firstElement = focusableElements?.[0];
            const lastElement = focusableElements?.[focusableElements.length - 1];

            if (!firstElement || !lastElement) {
                event.preventDefault();
                enquiryDialogRef.current?.focus();
                return;
            }

            if (
                event.shiftKey &&
                (document.activeElement === firstElement ||
                    !enquiryDialogRef.current?.contains(document.activeElement))
            ) {
                event.preventDefault();
                lastElement.focus();
            } else if (
                !event.shiftKey &&
                (document.activeElement === lastElement ||
                    !enquiryDialogRef.current?.contains(document.activeElement))
            ) {
                event.preventDefault();
                firstElement.focus();
            }
        }

        document.addEventListener('keydown', handleKeyDown);

        return () => {
            document.removeEventListener('keydown', handleKeyDown);
            document.body.style.overflow = previousOverflow;
            enquiryButtonRef.current?.focus();
        };
    }, [closeEnquiryDialog, isEnquiryFormVisible]);

    return (
        <AppLayout>
            <article className="grid gap-10 lg:grid-cols-2">
                <div className="flex aspect-square items-center justify-center rounded-xl bg-white p-8">
                    <img
                        src={product.image}
                        alt={product.title}
                        className="h-full w-full object-contain"
                    />
                </div>

                <div className="space-y-6">
                    <p className="text-sm font-semibold uppercase tracking-wide text-slate-500">
                        {product.category}
                    </p>
                    <h1 className="text-3xl font-semibold tracking-tight sm:text-4xl">
                        {product.title}
                    </h1>
                    <p className="text-2xl font-semibold">
                        ${product.price.toFixed(2)}
                    </p>
                    <p className="leading-7 text-slate-700">{product.description}</p>
                    <p className="text-sm text-slate-600">
                        Rated {product.rating.rate.toFixed(1)} out of 5 from{' '}
                        {product.rating.count} reviews
                    </p>

                    <button
                        ref={enquiryButtonRef}
                        type="button"
                        onClick={() => setIsEnquiryFormVisible(true)}
                        className="rounded-lg bg-slate-900 px-5 py-3 font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
                    >
                        Enquire
                    </button>
                </div>

                {isEnquiryFormVisible && (
                    <div
                        className="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm sm:p-6"
                        onMouseDown={(event) => {
                            if (event.target === event.currentTarget) {
                                closeEnquiryDialog();
                            }
                        }}
                    >
                        <div
                            ref={enquiryDialogRef}
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="product-enquiry-heading"
                            tabIndex={-1}
                            className="relative my-auto w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl outline-none sm:p-8"
                        >
                            <div className="mb-6 flex items-start justify-between gap-4">
                                <h2
                                    id="product-enquiry-heading"
                                    className="text-2xl font-semibold tracking-tight"
                                >
                                    Enquire about this product
                                </h2>
                                <button
                                    type="button"
                                    onClick={closeEnquiryDialog}
                                    aria-label="Close enquiry dialog"
                                    className="rounded-lg px-3 py-2 font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-500"
                                >
                                    Close
                                </button>
                            </div>
                            <EnquiryForm
                                productId={product.id}
                                onClose={closeEnquiryDialog}
                            />
                        </div>
                    </div>
                )}
            </article>
        </AppLayout>
    );
}
