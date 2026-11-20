<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\ProductRepositoryInterface;
use App\DTOs\ProductData;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(int $id, ProductRepositoryInterface $products): Response
    {
        $product = $products->find($id);

        abort_if(! $product instanceof ProductData, 404);

        return Inertia::render('Products/Show', [
            'product' => $product->toArray(),
        ]);
    }
}
