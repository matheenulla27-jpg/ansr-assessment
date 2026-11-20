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

export interface SharedPageProps extends Record<string, unknown> {
    categories: CategoryNavigationItem[];
}

export interface CategoryPageProps {
    category: string;
    topRated: Product[];
    additionalProducts: Product[];
}

export interface SearchPageProps {
    query: string;
    products: Product[];
}

export interface ProductDetailPageProps {
    product: Product;
}

export interface EnquiryFormData {
    product_id: number;
    name: string;
    email: string;
    phone: string;
    message: string;
}

export interface UnavailablePageProps {
    message: string;
}
