<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProductRepositoryInterface;
use App\DTOs\ProductData;

final readonly class ProductCatalogueService
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    /**
     * @return list<array{
     *     id: int,
     *     title: string,
     *     description: string,
     *     category: string,
     *     price: float,
     *     image: string,
     *     rating: array{rate: float, count: int}
     * }>
     */
    public function featuredProducts(): array
    {
        $products = $this->sortByRating($this->products->all());

        return array_map(
            static fn (ProductData $product): array => $product->toArray(),
            array_slice($products, 0, 5),
        );
    }

    /**
     * @return list<string>
     */
    public function categories(): array
    {
        $categories = array_values(array_unique(array_map(
            static fn (ProductData $product): string => $product->category,
            $this->products->all(),
        )));

        sort($categories, SORT_STRING);

        return $categories;
    }

    /**
     * @return array{
     *     topRated: list<array{
     *         id: int,
     *         title: string,
     *         description: string,
     *         category: string,
     *         price: float,
     *         image: string,
     *         rating: array{rate: float, count: int}
     *     }>,
     *     additionalProducts: list<array{
     *         id: int,
     *         title: string,
     *         description: string,
     *         category: string,
     *         price: float,
     *         image: string,
     *         rating: array{rate: float, count: int}
     *     }>
     * }|null
     */
    public function productsForCategory(string $category): ?array
    {
        $products = array_values(array_filter(
            $this->products->all(),
            static fn (ProductData $product): bool => $product->category === $category,
        ));

        if ($products === []) {
            return null;
        }

        $products = $this->sortByRating($products);

        return [
            'topRated' => array_map(
                static fn (ProductData $product): array => $product->toArray(),
                array_slice($products, 0, 2),
            ),
            'additionalProducts' => array_map(
                static fn (ProductData $product): array => $product->toArray(),
                array_slice($products, 2),
            ),
        ];
    }

    /**
     * @param  list<ProductData>  $products
     * @return list<ProductData>
     */
    private function sortByRating(array $products): array
    {
        usort($products, static fn (ProductData $left, ProductData $right): int => ($right->ratingRate <=> $left->ratingRate)
            ?: ($right->ratingCount <=> $left->ratingCount)
            ?: ($left->id <=> $right->id));

        return $products;
    }
}
