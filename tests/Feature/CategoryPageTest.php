<?php

declare(strict_types=1);

use App\Contracts\ProductRepositoryInterface;
use App\DTOs\ProductData;
use App\Exceptions\ProductSourceUnavailable;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

final readonly class CategoryPageProductRepositoryFixture implements ProductRepositoryInterface
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

function categoryPageProduct(int $id, string $category, float $rate, int $count): ProductData
{
    return new ProductData(
        id: $id,
        title: "Product {$id}",
        description: "Description for product {$id}",
        category: $category,
        price: 19.99,
        image: "https://example.test/products/{$id}.png",
        ratingRate: $rate,
        ratingCount: $count,
    );
}

it('shares sorted category links generated from product data', function (): void {
    Http::preventStrayRequests();

    app()->instance(ProductRepositoryInterface::class, new CategoryPageProductRepositoryFixture([
        categoryPageProduct(1, 'toys', 4.0, 20),
        categoryPageProduct(2, 'electronics', 4.5, 10),
        categoryPageProduct(3, 'books', 4.2, 15),
        categoryPageProduct(4, 'toys', 3.9, 30),
    ]));

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Home')
            ->where('categories', [
                [
                    'name' => 'books',
                    'href' => route('categories.show', ['category' => 'books']),
                ],
                [
                    'name' => 'electronics',
                    'href' => route('categories.show', ['category' => 'electronics']),
                ],
                [
                    'name' => 'toys',
                    'href' => route('categories.show', ['category' => 'toys']),
                ],
            ])
            ->etc());
});

it('renders two top-rated products followed by the remaining category products in deterministic order', function (): void {
    Http::preventStrayRequests();

    app()->instance(ProductRepositoryInterface::class, new CategoryPageProductRepositoryFixture([
        categoryPageProduct(6, 'electronics', 4.7, 50),
        categoryPageProduct(90, 'books', 5.0, 100),
        categoryPageProduct(10, 'electronics', 4.8, 10),
        categoryPageProduct(4, 'electronics', 4.7, 50),
        categoryPageProduct(8, 'electronics', 4.9, 1),
        categoryPageProduct(1, 'electronics', 4.7, 50),
    ]));

    $topRatedIds = [];
    $additionalProductIds = [];

    $this->get(route('categories.show', ['category' => 'electronics']))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$topRatedIds, &$additionalProductIds): Assert {
            $props = $page->toArray()['props'];
            $topRatedIds = array_column($props['topRated'], 'id');
            $additionalProductIds = array_column($props['additionalProducts'], 'id');

            return $page
                ->component('Categories/Show')
                ->where('category', 'electronics')
                ->has('topRated', 2)
                ->has('additionalProducts', 3)
                ->etc();
        });

    expect($topRatedIds)->toBe([8, 10]);
    expect($additionalProductIds)->toBe([1, 4, 6]);
    expect(array_intersect($topRatedIds, $additionalProductIds))->toBeEmpty();
});

it('renders an empty additional-products list when a category has fewer than two products', function (): void {
    Http::preventStrayRequests();

    app()->instance(ProductRepositoryInterface::class, new CategoryPageProductRepositoryFixture([
        categoryPageProduct(1, 'books', 4.5, 25),
    ]));

    $this->get(route('categories.show', ['category' => 'books']))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Categories/Show')
            ->has('topRated', 1)
            ->where('topRated.0.id', 1)
            ->has('additionalProducts', 0)
            ->etc());
});

it('returns 404 for an unknown category', function (): void {
    Http::preventStrayRequests();

    app()->instance(ProductRepositoryInterface::class, new CategoryPageProductRepositoryFixture([
        categoryPageProduct(1, 'books', 4.5, 25),
    ]));

    $this->get(route('categories.show', ['category' => 'unknown']))
        ->assertNotFound();
});

it('returns 503 instead of an empty category page when the product source is unavailable', function (): void {
    Http::preventStrayRequests();

    app()->instance(
        ProductRepositoryInterface::class,
        new CategoryPageProductRepositoryFixture(exception: new ProductSourceUnavailable('No product source is available.')),
    );

    $this->get(route('categories.show', ['category' => 'books']))
        ->assertStatus(503)
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Errors/Unavailable')
            ->where('message', 'Product data is temporarily unavailable. Please try again later.')
            ->etc());
});
