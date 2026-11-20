import ProductGrid from '../../components/catalogue/ProductGrid';
import AppLayout from '../../layouts/AppLayout';
import type { CategoryPageProps } from '../../types/catalogue';

export default function Show({
    category,
    topRated,
    additionalProducts,
}: CategoryPageProps) {
    return (
        <AppLayout>
            <div className="space-y-12">
                <section
                    aria-labelledby="top-rated-heading"
                    className="space-y-8"
                >
                    <div className="space-y-3">
                        <p className="text-sm font-semibold uppercase tracking-wide text-slate-500">
                            {category}
                        </p>
                        <h1
                            id="top-rated-heading"
                            className="text-3xl font-semibold tracking-tight sm:text-4xl"
                        >
                            Top Rated
                        </h1>
                    </div>
                    <ProductGrid products={topRated} />
                </section>

                <section
                    aria-labelledby="additional-products-heading"
                    className="space-y-8"
                >
                    <div className="space-y-3">
                        <h2
                            id="additional-products-heading"
                            className="text-2xl font-semibold tracking-tight sm:text-3xl"
                        >
                            More in this category
                        </h2>
                    </div>
                    {additionalProducts.length > 0 ? (
                        <ProductGrid products={additionalProducts} />
                    ) : (
                        <p className="text-slate-600">
                            There are no more products in this category.
                        </p>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
