import { Link } from '@inertiajs/react';
import type { Product } from '../../types/catalogue';

interface ProductCardProps {
    product: Product;
}

export default function ProductCard({ product }: ProductCardProps) {
    return (
        <Link
            href={`/products/${product.id}`}
            className="block h-full rounded-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-700"
        >
            <article className="flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-slate-400">
                <div className="flex h-72 shrink-0 items-center justify-center bg-white p-4">
                    <img
                        src={product.image}
                        alt={product.title}
                        className="h-full w-full object-contain"
                        loading="lazy"
                    />
                </div>
                <div className="flex flex-1 flex-col gap-3 border-t border-slate-100 p-5">
                    <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                        {product.category}
                    </p>
                    <h2 className="line-clamp-2 min-h-12 font-medium text-slate-900">
                        {product.title}
                    </h2>
                    <div className="mt-auto space-y-3 pt-2">
                        <div className="flex items-center justify-between gap-3">
                            <p className="font-semibold">${product.price.toFixed(2)}</p>
                            <p
                                className="text-sm text-slate-600"
                                aria-label={`${product.rating.rate.toFixed(1)} out of 5 stars from ${product.rating.count} reviews`}
                            >
                                <span aria-hidden="true">
                                    {product.rating.rate.toFixed(1)} / 5
                                </span>
                            </p>
                        </div>
                        <span className="block text-sm font-medium text-slate-700">
                            View product <span aria-hidden="true">→</span>
                        </span>
                    </div>
                </div>
            </article>
        </Link>
    );
}
