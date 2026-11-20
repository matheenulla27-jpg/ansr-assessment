<?php

declare(strict_types=1);

use App\Contracts\ProductRepositoryInterface;
use App\DTOs\ProductData;
use App\Exceptions\ProductSourceUnavailable;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

final readonly class ProductSearchRepositoryFixture implements ProductRepositoryInterface
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

function productSearchProduct(
    int $id,
    string $title,
    string $description,
    string $category,
    float $price = 19.99,
    float $ratingRate = 4.0,
    int $ratingCount = 10,
): ProductData {
    return new ProductData(
        id: $id,
        title: $title,
        description: $description,
        category: $category,
        price: $price,
        image: "https://example.test/products/{$id}.png",
        ratingRate: $ratingRate,
        ratingCount: $ratingCount,
    );
}

it('matches titles, descriptions, and categories case-insensitively after trimming whitespace', function (string $query, string $normalizedQuery): void {
    Http::preventStrayRequests();

    app()->instance(ProductRepositoryInterface::class, new ProductSearchRepositoryFixture([
        productSearchProduct(10, 'Leather Tote', 'Durable carry bag', 'Accessories'),
        productSearchProduct(11, 'Canvas Backpack', 'Everyday travel', 'Bags'),
    ]));

    $this->get(route('products.search', ['q' => $query]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Products/Search')
            ->where('query', $normalizedQuery)
            ->has('products', 1)
            ->where('products.0.id', 10)
            ->etc());
})->with([
    'title match' => ['  LEATHER  ', 'LEATHER'],
    'description match' => ["\tDURABLE\n", 'DURABLE'],
    'category match' => [' ACCESSORIES ', 'ACCESSORIES'],
]);

it('excludes non-matches and terms found only in price or product ID while ordering matches deterministically', function (): void {
    Http::preventStrayRequests();

    app()->instance(ProductRepositoryInterface::class, new ProductSearchRepositoryFixture([
        productSearchProduct(42, 'Everyday Tote', 'Durable carry bag', 'Accessories'),
        productSearchProduct(7, 'Evening Bag', 'Soft lining', 'Bags', price: 42.0),
        productSearchProduct(19, '42-Inch Display', 'High-resolution screen', 'Electronics', ratingRate: 4.8),
        productSearchProduct(20, '42-Inch Notebook', 'Large writing surface', 'Stationery', ratingRate: 4.5, ratingCount: 80),
        productSearchProduct(10, '42-Inch Planner', 'Weekly schedule pages', 'Stationery', ratingRate: 4.5, ratingCount: 80),
        productSearchProduct(15, 'Desk Lamp', 'Adjustable lighting', 'Home'),
    ]));

    $this->get(route('products.search', ['q' => '42']))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Products/Search')
            ->has('products', 3)
            ->where('products.0.id', 19)
            ->where('products.1.id', 10)
            ->where('products.2.id', 20)
            ->etc());
});

it('renders an empty-results state when a valid query matches no products', function (): void {
    Http::preventStrayRequests();

    app()->instance(ProductRepositoryInterface::class, new ProductSearchRepositoryFixture([
        productSearchProduct(1, 'Leather Tote', 'Durable carry bag', 'Accessories'),
    ]));

    $this->get(route('products.search', ['q' => 'camera']))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Products/Search')
            ->where('query', 'camera')
            ->has('products', 0)
            ->etc());
});

it('rejects blank and overlong search queries', function (string $query, string $message): void {
    Http::preventStrayRequests();

    app()->instance(ProductRepositoryInterface::class, new ProductSearchRepositoryFixture);

    $this->get(route('products.search', ['q' => $query]))
        ->assertSessionHasErrors(['q' => $message]);
})->with([
    'blank after trimming' => [" \t\n ", 'The q field is required.'],
    'over 100 characters' => [str_repeat('a', 101), 'The q field must not be greater than 100 characters.'],
]);

it('returns HTTP 503 when the product source is unavailable', function (): void {
    Http::preventStrayRequests();

    app()->instance(
        ProductRepositoryInterface::class,
        new ProductSearchRepositoryFixture(exception: new ProductSourceUnavailable('No product source is available.')),
    );

    $this->get(route('products.search', ['q' => 'camera']))
        ->assertStatus(503)
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Errors/Unavailable')
            ->where('message', 'Product data is temporarily unavailable. Please try again later.')
            ->etc());
});
