import { Link } from '@inertiajs/react';
import type { CategoryNavigationItem } from '../../types/catalogue';

interface CategoryNavigationProps {
    categories: CategoryNavigationItem[];
}

export default function CategoryNavigation({
    categories,
}: CategoryNavigationProps) {
    if (categories.length === 0) {
        return null;
    }

    return (
        <nav
            aria-label="Product categories"
            className="mx-auto flex max-w-7xl flex-wrap gap-x-5 gap-y-2 px-4 pb-5 sm:px-6 lg:px-8"
        >
            {categories.map((category) => (
                <Link
                    key={category.name}
                    href={category.href}
                    className="text-sm font-medium text-slate-600 transition hover:text-slate-950"
                >
                    {category.name}
                </Link>
            ))}
        </nav>
    );
}
