<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProductCatalogueService;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function show(string $category, ProductCatalogueService $catalogue): Response
    {
        $products = $catalogue->productsForCategory($category);

        abort_if($products === null, 404);

        return Inertia::render('Categories/Show', [
            'category' => $category,
            ...$products,
        ]);
    }
}
