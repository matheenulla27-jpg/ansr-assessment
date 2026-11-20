<?php

declare(strict_types=1);

use App\DTOs\ProductData;
use Tests\TestCase;

uses(TestCase::class);

it('maps a product payload into a readonly typed DTO and serializes its rating', function () {
    $payload = [
        'id' => 7,
        'title' => 'Gold Necklace',
        'description' => 'A simple necklace',
        'category' => 'jewelery',
        'price' => 129.95,
        'image' => 'https://example.test/necklace.png',
        'rating' => [
            'rate' => 4.6,
            'count' => 82,
        ],
    ];

    $product = ProductData::fromArray($payload);

    expect($product)
        ->toBeInstanceOf(ProductData::class)
        ->and($product->id)->toBe(7)
        ->and($product->price)->toBe(129.95)
        ->and($product->ratingRate)->toBe(4.6)
        ->and($product->ratingCount)->toBe(82)
        ->and((new ReflectionClass($product))->isReadOnly())->toBeTrue()
        ->and($product->toArray())->toBe($payload);
});

it('rejects a product payload with an invalid nested rating', function () {
    $payload = [
        'id' => 7,
        'title' => 'Gold Necklace',
        'description' => 'A simple necklace',
        'category' => 'jewelery',
        'price' => 129.95,
        'image' => 'https://example.test/necklace.png',
        'rating' => [
            'rate' => 'high',
            'count' => 82,
        ],
    ];

    expect(fn () => ProductData::fromArray($payload))
        ->toThrow(InvalidArgumentException::class, 'Product rating.rate must be a finite number.');
});
