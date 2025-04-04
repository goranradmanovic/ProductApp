<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminController;

Route::get('/', [ProductController::class, 'index']); // Product list page (All products)
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show'); // Single product

Route::get('/login', [AdminController::class, 'loginPage'])->name('login');
Route::post('/login', [AdminController::class, 'login'])->name('login.submit');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products'); // Show all product list/table

    Route::get('/admin/products/add', [AdminController::class, 'addProductForm'])->name('admin.add.product'); // Show add page and form
    Route::post('/admin/products/add', [AdminController::class, 'addProduct'])->name('admin.add.product.submit'); // Post form data and add product to DB

    Route::get('/admin/products/edit/{product}', [AdminController::class, 'editProduct'])->name('admin.edit.product'); // ShoW Edit single product page with product details
    Route::put('/admin/products/edit/{product}', [AdminController::class, 'updateProduct'])->name('admin.update.product'); // Update single product in DB

    Route::get('/admin/products/delete/{product}', [AdminController::class, 'deleteProduct'])->name('admin.delete.product'); // Delete single product

    Route::get('/logout', [AdminController::class, 'logout'])->name('logout'); // Logout admin
});
