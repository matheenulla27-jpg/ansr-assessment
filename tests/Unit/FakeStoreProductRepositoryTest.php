<?php

declare(strict_types=1);

use App\DTOs\ProductData;
use App\Exceptions\ProductSourceUnavailable;
use App\Repositories\FakeStoreProductRepository;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

function fakeStoreProduct(int $id = 1): array
{
    return [
        'id' => $id,
        'title' => 'Product '.$id,
        'description' => 'Product description',
        'category' => 'electronics',
        'price' => 19.99,
        'image' => 'https://example.test/product.png',
        'rating' => [
            'rate' => 4.2,
            'count' => 18,
        ],
    ];
}

beforeEach(function () {
    config()->set('services.fake_store.base_url', 'https://fakestoreapi.com');
    config()->set('services.fake_store.cache_ttl', 3600);
    config()->set('services.fake_store.stale_ttl', 86400);
});

it('retrieves products from the Fake Store endpoint and reuses the fresh cache', function () {
    Http::preventStrayRequests();
    Http::fake([
        'fakestoreapi.com/products' => Http::response([fakeStoreProduct()], 200),
    ]);

    $repository = new FakeStoreProductRepository;
    $firstRead = $repository->all();
    $secondRead = $repository->all();

    expect($firstRead)->toHaveCount(1)
        ->and($firstRead[0])->toBeInstanceOf(ProductData::class)
        ->and($firstRead[0]->id)->toBe(1)
        ->and($secondRead)->toEqual($firstRead);

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://fakestoreapi.com/products');
});

it('rehydrates products from serialized file cache entries', function (): void {
    config()->set('cache.default', 'file');

    $fileCache = Cache::store('file');
    $fileCache->forget('fake_store.products.fresh');
    $fileCache->forget('fake_store.products.stale');

    Http::preventStrayRequests();
    Http::fake([
        'fakestoreapi.com/products' => Http::response([fakeStoreProduct()], 200),
    ]);

    try {
        $firstRead = (new FakeStoreProductRepository)->all();
        $cachedProducts = $fileCache->get('fake_store.products.fresh');
        $secondRead = (new FakeStoreProductRepository)->all();

        expect($cachedProducts)->toBe([fakeStoreProduct()])
            ->and($secondRead)->toHaveCount(1)
            ->and($secondRead[0])->toBeInstanceOf(ProductData::class)
            ->and($secondRead[0])->toEqual($firstRead[0]);

        Http::assertSentCount(1);
    } finally {
        $fileCache->forget('fake_store.products.fresh');
        $fileCache->forget('fake_store.products.stale');
    }
});

it('finds an existing product and returns null for a missing ID', function () {
    Http::preventStrayRequests();
    Http::fake([
        'fakestoreapi.com/products' => Http::response([
            fakeStoreProduct(2),
            fakeStoreProduct(4),
        ], 200),
    ]);

    $repository = new FakeStoreProductRepository;

    expect($repository->find(4)?->title)->toBe('Product 4')
        ->and($repository->find(8))->toBeNull();

    Http::assertSentCount(1);
});

it('retries a transient upstream failure before returning the successful response', function () {
    Http::preventStrayRequests();
    Http::fake([
        'fakestoreapi.com/products' => Http::sequence()
            ->pushStatus(503)
            ->push([fakeStoreProduct()], 200),
    ]);

    $products = (new FakeStoreProductRepository)->all();

    expect($products)->toHaveCount(1)
        ->and($products[0]->id)->toBe(1);

    Http::assertSentCount(2);
});

it('serves valid stale products and logs when a refresh fails', function () {
    config()->set('services.fake_store.cache_ttl', 1);
    Http::preventStrayRequests();
    Http::fake([
        'fakestoreapi.com/products' => Http::sequence()
            ->push([fakeStoreProduct()], 200)
            ->pushStatus(503)
            ->whenEmpty(Http::response([], 503)),
    ]);

    $repository = new FakeStoreProductRepository;
    $cachedProducts = $repository->all();
    Cache::forget('fake_store.products.fresh');
    Log::shouldReceive('warning')
        ->once()
        ->with('Fake Store API unavailable; serving stale product data.', [
            'exception' => RequestException::class,
        ]);

    $staleProducts = $repository->all();

    expect($staleProducts)->toEqual($cachedProducts);
    Http::assertSentCount(3);
});

it('throws a domain exception when a non-success response has no stale cache', function () {
    Http::preventStrayRequests();
    Http::fake([
        'fakestoreapi.com/products' => Http::response([], 503),
    ]);

    expect(fn () => (new FakeStoreProductRepository)->all())
        ->toThrow(ProductSourceUnavailable::class);

    Http::assertSentCount(2);
});

it('throws a domain exception when a connection fails and no stale cache exists', function () {
    Http::preventStrayRequests();
    Http::fake([
        'fakestoreapi.com/products' => Http::failedConnection(),
    ]);

    expect(fn () => (new FakeStoreProductRepository)->all())
        ->toThrow(ProductSourceUnavailable::class);

    Http::assertSentCount(2);
});

it('rejects malformed product payloads instead of mapping them', function () {
    Http::preventStrayRequests();
    Http::fake([
        'fakestoreapi.com/products' => Http::response([
            array_merge(fakeStoreProduct(), ['rating' => ['rate' => 'excellent', 'count' => 18]]),
        ], 200),
    ]);

    expect(fn () => (new FakeStoreProductRepository)->all())
        ->toThrow(ProductSourceUnavailable::class);

    Http::assertSentCount(1);
});
