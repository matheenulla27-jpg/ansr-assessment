export interface ProductRating {
    rate: number;
    count: number;
}

export interface Product {
    id: number;
    title: string;
    description: string;
    category: string;
    price: number;
    image: string;
    rating: ProductRating;
}

export interface HomePageProps {
    featuredProducts: Product[];
}

export interface CategoryNavigationItem {
    name: string;
    href: string;
}

export interface SharedPageProps {
    categories: CategoryNavigationItem[];
}

export interface CategoryPageProps {
    category: string;
    topRated: Product[];
    additionalProducts: Product[];
}

export interface UnavailablePageProps {
    message: string;
}
