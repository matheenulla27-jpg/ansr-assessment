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
        $products = $this->products->all();

        usort($products, static fn (ProductData $left, ProductData $right): int => ($right->ratingRate <=> $left->ratingRate)
            ?: ($right->ratingCount <=> $left->ratingCount)
            ?: ($left->id <=> $right->id));

        return array_map(
            static fn (ProductData $product): array => $product->toArray(),
            array_slice($products, 0, 5),
        );
    }
}
