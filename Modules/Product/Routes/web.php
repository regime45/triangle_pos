<?php

use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\CategoriesController;
use Modules\Product\Http\Controllers\BarcodeController;
use Illuminate\Support\Facades\Route;

// CSV Upload Test
Route::get('/products/upload-test', function () { return 'OK'; });

// CSV Upload
Route::get('/products/upload', [ProductController::class, 'showUploadForm'])->name('products.upload.form');
Route::post('/products/upload', [ProductController::class, 'uploadCsv'])->name('products.upload.csv');

// Product resources
// Print Barcode
Route::get('/products/print-barcode', [ProductController::class, 'printBarcode'])->name('barcode.print');


Route::resource('products', ProductController::class);

// Product categories
Route::resource('product-categories', CategoriesController::class)->except('create', 'show');


