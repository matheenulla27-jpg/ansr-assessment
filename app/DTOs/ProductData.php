<?php

declare(strict_types=1);

namespace App\DTOs;

use InvalidArgumentException;

final readonly class ProductData
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public string $category,
        public float $price,
        public string $image,
        public float $ratingRate,
        public int $ratingCount,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rating = $data['rating'] ?? null;

        if (! is_array($rating)) {
            throw new InvalidArgumentException('Product rating must be an array.');
        }

        return new self(
            id: self::positiveInteger($data['id'] ?? null, 'id'),
            title: self::string($data['title'] ?? null, 'title'),
            description: self::string($data['description'] ?? null, 'description'),
            category: self::string($data['category'] ?? null, 'category'),
            price: self::number($data['price'] ?? null, 'price'),
            image: self::string($data['image'] ?? null, 'image'),
            ratingRate: self::number($rating['rate'] ?? null, 'rating.rate'),
            ratingCount: self::nonNegativeInteger($rating['count'] ?? null, 'rating.count'),
        );
    }

    /**
     * @return array{
     *     id: int,
     *     title: string,
     *     description: string,
     *     category: string,
     *     price: float,
     *     image: string,
     *     rating: array{rate: float, count: int}
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'price' => $this->price,
            'image' => $this->image,
            'rating' => [
                'rate' => $this->ratingRate,
                'count' => $this->ratingCount,
            ],
        ];
    }

    private static function string(mixed $value, string $field): string
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException("Product {$field} must be a string.");
        }

        return $value;
    }

    private static function positiveInteger(mixed $value, string $field): int
    {
        if (! is_int($value) || $value < 1) {
            throw new InvalidArgumentException("Product {$field} must be a positive integer.");
        }

        return $value;
    }

    private static function nonNegativeInteger(mixed $value, string $field): int
    {
        if (! is_int($value) || $value < 0) {
            throw new InvalidArgumentException("Product {$field} must be a non-negative integer.");
        }

        return $value;
    }

    private static function number(mixed $value, string $field): float
    {
        if ((! is_int($value) && ! is_float($value)) || ! is_finite((float) $value)) {
            throw new InvalidArgumentException("Product {$field} must be a finite number.");
        }

        return (float) $value;
    }
}
