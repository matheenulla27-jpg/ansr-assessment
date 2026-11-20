<?php

declare(strict_types=1);

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/products/search', ProductSearchController::class)->name('products.search');
Route::get('/products/{id}', [ProductController::class, 'show'])->whereNumber('id')->name('products.show');
Route::post('/products/enquiries', [EnquiryController::class, 'store'])->name('products.enquiries.store');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
