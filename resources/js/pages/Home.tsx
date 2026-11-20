import AppLayout from '../layouts/AppLayout';
import type { HomePageProps } from '../types/catalogue';
import ProductGrid from '../components/catalogue/ProductGrid';

export default function Home({ featuredProducts }: HomePageProps) {
    return (
        <AppLayout>
            <section aria-labelledby="featured-heading" className="space-y-8">
                <div className="max-w-2xl space-y-3">
                    <p className="text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Customer favourites
                    </p>
                    <h1
                        id="featured-heading"
                        className="text-3xl font-semibold tracking-tight sm:text-4xl"
                    >
                        Top-rated products
                    </h1>
                    <p className="text-slate-600">
                        Explore the five products with the highest customer
                        ratings.
                    </p>
                </div>
                <ProductGrid products={featuredProducts} />
            </section>
        </AppLayout>
    );
}
