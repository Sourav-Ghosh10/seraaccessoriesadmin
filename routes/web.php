<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

use App\Http\Controllers\DealerController;
use App\Http\Controllers\SalesmanController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PriceListController;

use App\Http\Controllers\AuthController;

Route::get('/', [PageController::class, 'login'])->name('login');
Route::get('/login', [PageController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard')->middleware('auth');

Route::post('/order-requests', [OrderController::class, 'storeRequest'])->name('order-requests.store');
Route::post('/orders/store', [OrderController::class, 'storeOrder'])->name('orders.store');
Route::post('/orders/{id}/upload-challan', [OrderController::class, 'uploadChallan'])->name('orders.upload-challan');
Route::post('/orders/{id}/update-delivery', [OrderController::class, 'updateDeliveryStatus'])->name('orders.update-delivery');
Route::post('/invoices/store', [OrderController::class, 'storeInvoice'])->name('invoices.store');
Route::post('/rewards/store', [OrderController::class, 'storeRewardPoints'])->name('rewards.store');
Route::post('/order-requests/store', [OrderController::class, 'storeRequest'])->name('order-requests.store');
// Dealers CRUD
Route::get('/dealers', [PageController::class, 'dealers'])->name('dealers');
Route::post('/dealers', [DealerController::class, 'store'])->name('dealers.store');
Route::put('/dealers/{id}', [DealerController::class, 'update'])->name('dealers.update');

// Salesmen CRUD
Route::get('/salesmen', [PageController::class, 'salesmen'])->name('salesmen');
Route::post('/salesmen', [SalesmanController::class, 'store'])->name('salesmen.store');
Route::put('/salesmen/{id}', [SalesmanController::class, 'update'])->name('salesmen.update');

// Distributors CRUD
Route::get('/distributors', [PageController::class, 'distributors'])->name('distributors');
Route::post('/distributors', [DistributorController::class, 'store'])->name('distributors.store');
Route::put('/distributors/{id}', [DistributorController::class, 'update'])->name('distributors.update');

// Users CRUD
Route::get('/users', [PageController::class, 'users'])->name('users');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');

Route::get('/estimate-requests', [PageController::class, 'estimateRequests'])->name('estimate-requests');
Route::post('/estimates/{id}/revert', [OrderController::class, 'revertEstimate'])->name('estimates.revert');

// Compliance Routes
Route::get('/compliance', [PageController::class, 'complianceDashboard'])->name('compliance.dashboard');
Route::get('/compliance/sites', [PageController::class, 'siteCompliance'])->name('compliance.sites');
Route::get('/compliance/expiring', [PageController::class, 'expiringCompliance'])->name('compliance.expiring');
Route::get('/compliance/non-compliant', [PageController::class, 'nonCompliantSites'])->name('compliance.non-compliant');
Route::get('/compliance/reports', [PageController::class, 'complianceReports'])->name('compliance.reports');

Route::get('/order-requests', [PageController::class, 'orderRequests'])->name('order-requests');
Route::get('/orders', [PageController::class, 'ordersList'])->name('orders.index');
Route::get('/orders/create', [PageController::class, 'createOrder'])->name('orders.create');
Route::get('/orders/{id}', [PageController::class, 'showOrder'])->name('orders.show');
Route::get('/delivery', [PageController::class, 'delivery'])->name('delivery');
Route::get('/invoices', [PageController::class, 'invoices'])->name('invoices');
Route::get('/rewards', [PageController::class, 'rewards'])->name('rewards');
    Route::get('/price-list', [PriceListController::class, 'index'])->name('price-list');
    Route::post('/price-list/upload', [PriceListController::class, 'upload'])->name('price-list.upload');
Route::get('/passbook', [PageController::class, 'passbook'])->name('passbook');
Route::post('/passbook/update', [PageController::class, 'updateBalance'])->name('passbook.update');
Route::get('/all-transactions', [PageController::class, 'allTransactions'])->name('transactions.index');

Route::get('/payments/verify', [PageController::class, 'verifyPayments'])->name('payments.verify')->middleware('auth');
Route::post('/payments/{id}/approve', [PageController::class, 'approvePayment'])->name('payments.approve')->middleware('auth');
Route::post('/payments/{id}/reject', [PageController::class, 'rejectPayment'])->name('payments.reject')->middleware('auth');

Route::get('/settings', [PageController::class, 'settings'])->name('settings')->middleware('auth');
Route::post('/settings/update', [PageController::class, 'updateSettings'])->name('settings.update')->middleware('auth');
