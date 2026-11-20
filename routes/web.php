<?php

declare(strict_types=1);

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/products/search', ProductSearchController::class)->name('products.search');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
