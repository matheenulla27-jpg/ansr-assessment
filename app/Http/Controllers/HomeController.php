<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ProductCatalogueService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(ProductCatalogueService $catalogue): Response
    {
        return Inertia::render('Home', [
            'featuredProducts' => $catalogue->featuredProducts(),
        ]);
    }
}
