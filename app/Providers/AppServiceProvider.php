<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ProductRepositoryInterface;
use App\Repositories\FakeStoreProductRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, FakeStoreProductRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
