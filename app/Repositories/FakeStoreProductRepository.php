<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ProductRepositoryInterface;
use App\DTOs\ProductData;
use App\Exceptions\ProductSourceUnavailable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use UnexpectedValueException;

final class FakeStoreProductRepository implements ProductRepositoryInterface
{
    private const string FRESH_CACHE_KEY = 'fake_store.products.fresh';

    private const string STALE_CACHE_KEY = 'fake_store.products.stale';

    /**
     * @return list<ProductData>
     */
    public function all(): array
    {
        try {
            /** @var list<ProductData> $products */
            $products = Cache::remember(
                self::FRESH_CACHE_KEY,
                (int) config('services.fake_store.cache_ttl', 3600),
                fn (): array => $this->fetchProducts(),
            );

            return $products;
        } catch (ConnectionException|RequestException|InvalidArgumentException|UnexpectedValueException $exception) {
            $staleProducts = Cache::get(self::STALE_CACHE_KEY);

            if ($this->isValidProductList($staleProducts)) {
                Log::warning('Fake Store API unavailable; serving stale product data.', [
                    'exception' => $exception::class,
                ]);

                return $staleProducts;
            }

            throw new ProductSourceUnavailable('Product data is unavailable and no valid cached copy exists.', $exception->getCode(), previous: $exception);
        }
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

    /**
     * @return list<ProductData>
     *
     * @throws ConnectionException
     * @throws RequestException
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    private function fetchProducts(): array
    {
        $response = Http::baseUrl((string) config('services.fake_store.base_url'))
            ->timeout(5)
            ->retry(2, 100)
            ->get('/products')
            ->throw();

        $payload = $response->json();

        if (! is_array($payload) || ! array_is_list($payload)) {
            throw new UnexpectedValueException('Fake Store API returned an invalid product list.');
        }

        $products = [];

        foreach ($payload as $product) {
            if (! is_array($product)) {
                throw new UnexpectedValueException('Fake Store API returned an invalid product.');
            }

            $products[] = ProductData::fromArray($product);
        }

        Cache::put(
            self::STALE_CACHE_KEY,
            $products,
            (int) config('services.fake_store.stale_ttl', 86400),
        );

        return $products;
    }

    /**
     * @return list<ProductData>
     */
    private function isValidProductList(mixed $products): bool
    {
        if (! is_array($products) || ! array_is_list($products)) {
            return false;
        }

        foreach ($products as $product) {
            if (! $product instanceof ProductData) {
                return false;
            }
        }

        return true;
    }
}
