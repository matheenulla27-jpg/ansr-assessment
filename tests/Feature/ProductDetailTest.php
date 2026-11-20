<?php

declare(strict_types=1);

use App\Contracts\ProductRepositoryInterface;
use App\DTOs\ProductData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

final readonly class ProductDetailRepositoryFixture implements ProductRepositoryInterface
{
    /**
     * @param  list<ProductData>  $products
     */
    public function __construct(private array $products = []) {}

    /**
     * @return list<ProductData>
     */
    public function all(): array
    {
        return $this->products;
    }

    public function find(int $id): ?ProductData
    {
        foreach ($this->products as $product) {
            if ($product->id === $id) {
                return $product;
            }
        }

        return null;
    }
}

function productDetailProduct(): ProductData
{
    return new ProductData(
        id: 8,
        title: 'Canvas Backpack',
        description: 'A practical daily backpack.',
        category: 'bags',
        price: 42.5,
        image: 'https://example.test/products/8.png',
        ratingRate: 4.7,
        ratingCount: 29,
    );
}

it('renders product detail props at the product-card detail route', function (): void {
    Http::preventStrayRequests();

    $product = productDetailProduct();
    app()->instance(ProductRepositoryInterface::class, new ProductDetailRepositoryFixture([$product]));

    $this->get(route('products.show', ['id' => $product->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Products/Show')
            ->where('product', $product->toArray())
            ->etc());
});

it('returns 404 for an unknown product detail and sends no email', function (): void {
    Http::preventStrayRequests();
    Mail::fake();
    app()->instance(ProductRepositoryInterface::class, new ProductDetailRepositoryFixture);

    $this->get(route('products.show', ['id' => 999]))
        ->assertNotFound();

    Mail::assertNothingSent();
});
