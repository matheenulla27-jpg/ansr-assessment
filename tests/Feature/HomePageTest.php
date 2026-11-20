<?php

declare(strict_types=1);

use App\Contracts\ProductRepositoryInterface;
use App\DTOs\ProductData;
use App\Exceptions\ProductSourceUnavailable;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

final readonly class HomePageProductRepositoryFixture implements ProductRepositoryInterface
{
    /**
     * @param  list<ProductData>  $products
     */
    public function __construct(
        private array $products = [],
        private ?ProductSourceUnavailable $exception = null,
    ) {}

    /**
     * @return list<ProductData>
     */
    public function all(): array
    {
        if ($this->exception instanceof ProductSourceUnavailable) {
            throw $this->exception;
        }

        return $this->products;
    }

    public function find(int $id): ?ProductData
    {
        foreach ($this->all() as $product) {
            if ($product->id === $id) {
                return $product;
            }
        }

        return null;
    }
}

function homePageProduct(int $id, float $rate, int $count): ProductData
{
    return new ProductData(
        id: $id,
        title: "Product {$id}",
        description: "Description for product {$id}",
        category: 'electronics',
        price: 19.99,
        image: "https://example.test/products/{$id}.png",
        ratingRate: $rate,
        ratingCount: $count,
    );
}

it('renders exactly five featured products in deterministic rating order', function (): void {
    Http::preventStrayRequests();

    app()->instance(ProductRepositoryInterface::class, new HomePageProductRepositoryFixture([
        homePageProduct(9, 4.0, 90),
        homePageProduct(4, 4.0, 90),
        homePageProduct(3, 4.0, 100),
        homePageProduct(8, 4.8, 10),
        homePageProduct(7, 4.2, 200),
        homePageProduct(2, 4.0, 90),
        homePageProduct(1, 3.9, 500),
    ]));

    $response = $this->get('/');

    $response->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Home')
            ->has('featuredProducts', 5)
            ->where('featuredProducts.0.id', 8)
            ->where('featuredProducts.1.id', 7)
            ->where('featuredProducts.2.id', 3)
            ->where('featuredProducts.3.id', 2)
            ->where('featuredProducts.4.id', 4)
            ->etc());
});

it('renders the unavailable Inertia page with status 503 when the product source fails', function (): void {
    Http::preventStrayRequests();

    app()->instance(
        ProductRepositoryInterface::class,
        new HomePageProductRepositoryFixture(exception: new ProductSourceUnavailable('No product source is available.')),
    );

    $response = $this->get('/');

    $response->assertStatus(503)
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Errors/Unavailable')
            ->where('message', 'Product data is temporarily unavailable. Please try again later.')
            ->etc());
});
