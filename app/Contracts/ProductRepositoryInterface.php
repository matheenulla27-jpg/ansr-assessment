<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\ProductData;

interface ProductRepositoryInterface
{
    /**
     * @return list<ProductData>
     */
    public function all(): array;

    public function find(int $id): ?ProductData;
}
