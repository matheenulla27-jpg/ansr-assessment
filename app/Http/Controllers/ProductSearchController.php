<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SearchProductsRequest;
use App\Services\ProductCatalogueService;
use Inertia\Inertia;
use Inertia\Response;

class ProductSearchController extends Controller
{
    public function __invoke(SearchProductsRequest $request, ProductCatalogueService $catalogue): Response
    {
        $query = $request->validated('q');

        return Inertia::render('Products/Search', [
            'query' => $query,
            'products' => $catalogue->search($query),
        ]);
    }
}
