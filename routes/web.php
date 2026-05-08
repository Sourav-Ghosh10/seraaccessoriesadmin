<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

Route::get('/', [PageController::class, 'login'])->name('login');
Route::get('/login', [PageController::class, 'login'])->name('login');
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
Route::get('/dealers', [PageController::class, 'dealers'])->name('dealers');
Route::get('/salesmen', [PageController::class, 'salesmen'])->name('salesmen');
Route::get('/distributors', [PageController::class, 'distributors'])->name('distributors');
Route::get('/order-requests', [PageController::class, 'orderRequests'])->name('order-requests');
Route::get('/orders', [PageController::class, 'ordersList'])->name('orders.index');
Route::get('/orders/create', [PageController::class, 'createOrder'])->name('orders.create');
Route::get('/orders/{id}', [PageController::class, 'showOrder'])->name('orders.show');
Route::get('/delivery', [PageController::class, 'delivery'])->name('delivery');
Route::get('/invoices', [PageController::class, 'invoices'])->name('invoices');
Route::get('/rewards', [PageController::class, 'rewards'])->name('rewards');
Route::get('/price-list', [PageController::class, 'priceList'])->name('price-list');
Route::get('/passbook', [PageController::class, 'passbook'])->name('passbook');
