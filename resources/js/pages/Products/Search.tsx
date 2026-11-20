import ProductGrid from '../../components/catalogue/ProductGrid';
import AppLayout from '../../layouts/AppLayout';
import type { SearchPageProps } from '../../types/catalogue';

export default function Search({ query, products }: SearchPageProps) {
    return (
        <AppLayout>
            <section aria-labelledby="search-results-heading" className="space-y-8">
                <div className="space-y-3">
                    <p className="text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Search results
                    </p>
                    <h1
                        id="search-results-heading"
                        className="text-3xl font-semibold tracking-tight sm:text-4xl"
                    >
                        Results for &ldquo;{query}&rdquo;
                    </h1>
                </div>
                {products.length > 0 ? (
                    <ProductGrid products={products} />
                ) : (
                    <p role="status" className="text-slate-600">
                        No products found for &ldquo;{query}&rdquo;.
                    </p>
                )}
            </section>
        </AppLayout>
    );
}
