import type { ReactNode } from 'react';
import { Link, usePage } from '@inertiajs/react';
import CategoryNavigation from '../components/catalogue/CategoryNavigation';
import type { SharedPageProps } from '../types/catalogue';

interface AppLayoutProps {
    children: ReactNode;
}

export default function AppLayout({ children }: AppLayoutProps) {
    const categories = usePage<SharedPageProps>().props.categories ?? [];

    return (
        <div className="min-h-screen bg-slate-50 text-slate-950">
            <header className="border-b border-slate-200 bg-white">
                <nav
                    aria-label="Main navigation"
                    className="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8"
                >
                    <Link
                        href="/"
                        className="text-xl font-semibold tracking-tight"
                    >
                        Product Catalogue
                    </Link>
                    <span className="text-sm text-slate-600">
                        Discover customer favourites
                    </span>
                </nav>
                <CategoryNavigation categories={categories} />
            </header>
            <main className="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                {children}
            </main>
        </div>
    );
}
