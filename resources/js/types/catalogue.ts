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

export interface UnavailablePageProps {
    message: string;
}
