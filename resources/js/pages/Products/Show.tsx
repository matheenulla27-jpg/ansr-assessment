import { useState } from 'react';
import EnquiryForm from '../../components/catalogue/EnquiryForm';
import AppLayout from '../../layouts/AppLayout';
import type { ProductDetailPageProps } from '../../types/catalogue';

export default function Show({ product }: ProductDetailPageProps) {
    const [isEnquiryFormVisible, setIsEnquiryFormVisible] = useState(false);

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

                    {!isEnquiryFormVisible && (
                        <button
                            type="button"
                            onClick={() => setIsEnquiryFormVisible(true)}
                            className="rounded-lg bg-slate-900 px-5 py-3 font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
                        >
                            Enquire
                        </button>
                    )}
                </div>

                {isEnquiryFormVisible && (
                    <section
                        aria-labelledby="product-enquiry-heading"
                        className="space-y-5 lg:col-span-2"
                    >
                        <h2
                            id="product-enquiry-heading"
                            className="text-2xl font-semibold tracking-tight"
                        >
                            Enquire about this product
                        </h2>
                        <EnquiryForm productId={product.id} />
                    </section>
                )}
            </article>
        </AppLayout>
    );
}
