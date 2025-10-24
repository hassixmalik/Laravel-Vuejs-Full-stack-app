<?php

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function(){
    //Inventory routes
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');

    //Invoice routes
    Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice.index');
    Route::get('/invoice/detail', [InvoiceController::class, 'detail'])->name('invoice.detail');
    Route::get('/invoice/create', [InvoiceController::class, 'create'])->name('invoice.create');

    //Order Routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/createOrder', [OrderController::class, 'create'])->name('orders.create');

});

require __DIR__.'/settings.php';
