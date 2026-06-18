<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DealerController;
use App\Http\Controllers\Api\SalesmanController;
use App\Http\Controllers\Api\DistributorController;

/*
|--------------------------------------------------------------------------
| API Routes — Sera Accessories
|--------------------------------------------------------------------------
|
| Public:
|   POST  /api/auth/login
|
| Auth protected (access token):
|   GET   /api/auth/me
|   POST  /api/auth/logout
|   POST  /api/auth/refresh      ← use refresh token here
|
| Dealer protected (access token, dealer role):
|   POST  /api/dealer/estimate
|   POST  /api/dealer/order-request
|
*/

// ── Public ───────────────────────────────────────────────────────────────────
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
});

use App\Http\Middleware\JwtAuthMiddleware;

// ── Auth Protected (JWT) ──────────────────────────────────────────────────────
Route::middleware(JwtAuthMiddleware::class)->group(function () {

    Route::prefix('auth')->name('api.auth.')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });

    // ── Dealer APIs ───────────────────────────────────────────────────────────
    Route::prefix('dealer')->name('api.dealer.')->group(function () {
        Route::post('/estimate', [DealerController::class, 'submitEstimate'])->name('estimate');
        Route::post('/estimate/{id}/confirm', [DealerController::class, 'confirmEstimate'])->name('estimate.confirm');
        Route::post('/estimate/{id}/cancel', [DealerController::class, 'cancelEstimate'])->name('estimate.cancel');
        Route::post('/order-request', [DealerController::class, 'placeOrderRequest'])->name('order-request');
        Route::get('/my-orders', [DealerController::class, 'myOrders'])->name('my-orders');
        Route::get('/my-orders/details', [DealerController::class, 'orderDetails'])->name('my-orders.details');
        Route::post('/update-fcm-token', [DealerController::class, 'updateFcmToken'])->name('update-fcm-token');
        Route::get('/notifications', [DealerController::class, 'getNotifications'])->name('notifications.list');
        Route::post('/notifications/read-all', [DealerController::class, 'readAllNotifications'])->name('notifications.read-all');
        Route::get('/my-points', [DealerController::class, 'myPoints'])->name('my-points');
        Route::get('/price-list', [DealerController::class, 'getLatestPriceList'])->name('price-list');
        Route::get('/my-passbook', [DealerController::class, 'myPassbook'])->name('my-passbook');
        Route::post('/upload-payment', [DealerController::class, 'uploadPayment'])->name('upload-payment');
        Route::post('/order/{id}/receive', [DealerController::class, 'markOrderReceived'])->name('order.receive');
    });

    // ── Salesman APIs ─────────────────────────────────────────────────────────
    Route::prefix('salesman')->name('api.salesman.')->group(function () {
        Route::get('/my-dealers', [SalesmanController::class, 'myDealers'])->name('my-dealers');
        Route::post('/order-request', [SalesmanController::class, 'placeOrderRequest'])->name('order-request');
        Route::get('/my-orders', [SalesmanController::class, 'myOrders'])->name('my-orders');
        Route::get('/my-orders/details', [SalesmanController::class, 'orderDetails'])->name('my-orders.details');
        Route::get('/my-points', [SalesmanController::class, 'myPoints'])->name('my-points');
        Route::get('/dealer/passbook', [SalesmanController::class, 'dealerPassbook'])->name('dealer-passbook');

        // Attendance & Visits
        Route::get('/attendance-status', [SalesmanController::class, 'attendanceStatus'])->name('attendance-status');
        Route::get('/attendance-history', [SalesmanController::class, 'attendanceHistory'])->name('attendance-history');
        Route::post('/clock-in', [SalesmanController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out', [SalesmanController::class, 'clockOut'])->name('clock-out');
        Route::get('/visits', [SalesmanController::class, 'getVisits'])->name('visits.index');
        Route::post('/visits', [SalesmanController::class, 'storeVisit'])->name('visits.store');
        Route::post('/location-ping', [SalesmanController::class, 'locationPing'])->name('location-ping');

        // Expenses
        Route::get('/expense-categories', [SalesmanController::class, 'getExpenseCategories'])->name('expense-categories');
        Route::get('/expenses', [SalesmanController::class, 'getExpenses'])->name('expenses.index');
        Route::post('/expenses', [SalesmanController::class, 'storeExpense'])->name('expenses.store');
    });

    // ── Distributor APIs ──────────────────────────────────────────────────────
    Route::prefix('distributor')->name('api.distributor.')->group(function () {
        Route::get('/my-orders', [DistributorController::class, 'myOrders'])->name('my-orders');
        Route::get('/my-orders/details', [DistributorController::class, 'orderDetails'])->name('my-orders.details');
        Route::post('/order/{id}/delivery', [DistributorController::class, 'updateDelivery'])->name('order.delivery');
    });

});
