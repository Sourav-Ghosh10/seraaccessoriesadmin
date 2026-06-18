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

// Serve uploaded files from the root-level uploads/ directory
// (Required because index.php is at project root and handles all requests through Laravel)
Route::get('/uploads/{path}', function ($path) {
    $fullPath = base_path('uploads/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
    return response()->file($fullPath, ['Content-Type' => $mimeType]);
})->where('path', '.*');
Route::middleware(['auth'])->group(function () {
    // Shared Dashboard (Admin, Operations, Account)
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/chart-data', [PageController::class, 'chartData'])->name('dashboard.chart');

    // Admin Only
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/users', [PageController::class, 'users'])->name('users');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');

        Route::get('/settings', [PageController::class, 'settings'])->name('settings');
        Route::post('/settings/update', [PageController::class, 'updateSettings'])->name('settings.update');
    });

    // Admin & Operations
    Route::middleware(['role:Admin,Operations'])->group(function () {
        Route::get('/dealers', [PageController::class, 'dealers'])->name('dealers');
        Route::post('/dealers', [DealerController::class, 'store'])->name('dealers.store');
        Route::put('/dealers/{id}', [DealerController::class, 'update'])->name('dealers.update');
        Route::put('/dealers/{id}/update-points', [DealerController::class, 'updatePoints'])->name('dealers.update-points');
        Route::patch('/dealers/{id}/toggle-passbook', [DealerController::class, 'togglePassbook'])->name('dealers.toggle-passbook');
        Route::delete('/dealers/{id}', [DealerController::class, 'destroy'])->name('dealers.destroy');

        Route::get('/salesmen', [PageController::class, 'salesmen'])->name('salesmen');
        Route::post('/salesmen', [SalesmanController::class, 'store'])->name('salesmen.store');
        Route::put('/salesmen/{id}', [SalesmanController::class, 'update'])->name('salesmen.update');
        Route::put('/salesmen/{id}/update-points', [SalesmanController::class, 'updatePoints'])->name('salesmen.update-points');
        Route::get('/salesmen/{id}/performance', [SalesmanController::class, 'performance'])->name('salesmen.performance');
        Route::get('/salesman-attendance', [PageController::class, 'salesmanAttendance'])->name('salesman.attendance');
        Route::get('/salesman-attendance/{id}', [PageController::class, 'salesmanAttendanceDetails'])->name('salesman.attendance.details');

        Route::get('/expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
        Route::patch('/expenses/{id}/status', [\App\Http\Controllers\ExpenseController::class, 'updateStatus'])->name('expenses.status.update');

        Route::get('/distributors', [PageController::class, 'distributors'])->name('distributors');
        Route::post('/distributors', [DistributorController::class, 'store'])->name('distributors.store');
        Route::put('/distributors/{id}', [DistributorController::class, 'update'])->name('distributors.update');

        Route::get('/cities', [\App\Http\Controllers\CityController::class, 'index'])->name('cities');
        Route::post('/cities', [\App\Http\Controllers\CityController::class, 'store'])->name('cities.store');
        Route::put('/cities/{id}', [\App\Http\Controllers\CityController::class, 'update'])->name('cities.update');
        Route::patch('/cities/{id}/toggle-status', [\App\Http\Controllers\CityController::class, 'toggleStatus'])->name('cities.toggle-status');
        Route::get('/estimate-requests', [PageController::class, 'estimateRequests'])->name('estimate-requests');
        Route::post('/estimates/{id}/revert', [OrderController::class, 'revertEstimate'])->name('estimates.revert');

        Route::get('/order-requests', [PageController::class, 'orderRequests'])->name('order-requests');
        
        Route::get('/delivery', [PageController::class, 'delivery'])->name('delivery');
        Route::post('/orders/{id}/update-delivery', [OrderController::class, 'updateDeliveryStatus'])->name('orders.update-delivery');

        Route::get('/rewards', [PageController::class, 'rewards'])->name('rewards');
        Route::post('/rewards/store', [OrderController::class, 'storeRewardPoints'])->name('rewards.store');

        Route::get('/price-list', [PriceListController::class, 'index'])->name('price-list');
        Route::post('/price-list/upload', [PriceListController::class, 'upload'])->name('price-list.upload');

        Route::get('/passbook', [PageController::class, 'passbook'])->name('passbook');
        Route::post('/passbook/update', [PageController::class, 'updateBalance'])->name('passbook.update');
        Route::get('/all-transactions', [PageController::class, 'allTransactions'])->name('transactions.index');

        // Compliance Routes
        Route::get('/compliance', [PageController::class, 'complianceDashboard'])->name('compliance.dashboard');
        Route::get('/compliance/sites', [PageController::class, 'siteCompliance'])->name('compliance.sites');
        Route::get('/compliance/expiring', [PageController::class, 'expiringCompliance'])->name('compliance.expiring');
        Route::get('/compliance/non-compliant', [PageController::class, 'nonCompliantSites'])->name('compliance.non-compliant');
        Route::get('/compliance/reports', [PageController::class, 'complianceReports'])->name('compliance.reports');
    });

    // Admin & Account
    Route::middleware(['role:Admin,Account'])->group(function () {
        Route::get('/invoices', [PageController::class, 'invoices'])->name('invoices');
        Route::post('/invoices/store', [OrderController::class, 'storeInvoice'])->name('invoices.store');
        Route::post('/credit-notes/store', [OrderController::class, 'storeCreditNote'])->name('credit-notes.store');
        Route::post('/orders/{id}/mark-returned', [OrderController::class, 'markReturned'])->name('orders.mark-returned');
        Route::patch('/orders/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('orders.cancel');
    });

    // Admin, Account & Operations
    Route::middleware(['role:Admin,Account,Operations'])->group(function () {
        Route::get('/payments/verify', [PageController::class, 'verifyPayments'])->name('payments.verify');
        Route::post('/payments/{id}/approve', [PageController::class, 'approvePayment'])->name('payments.approve');
        Route::post('/payments/{id}/reject', [PageController::class, 'rejectPayment'])->name('payments.reject');
    });

    // Shared by All Roles
    Route::middleware(['role:Admin,Operations,Account'])->group(function () {
        Route::get('/orders', [PageController::class, 'ordersList'])->name('orders.index');
        Route::get('/orders/create', [PageController::class, 'createOrder'])->name('orders.create');
        Route::get('/orders/{id}', [PageController::class, 'showOrder'])->name('orders.show');
        Route::get('/api/check-new-requests', [PageController::class, 'checkNewRequests'])->name('check.new.requests');
        Route::get('/api/dependent-members', [PageController::class, 'dependentMembers'])->name('api.dependent-members');
        Route::post('/order-requests', [OrderController::class, 'storeRequest'])->name('order-requests.store');
        Route::post('/orders/store', [OrderController::class, 'storeOrder'])->name('orders.store');
        Route::post('/orders/{id}/upload-challan', [OrderController::class, 'uploadChallan'])->name('orders.upload-challan');
        Route::post('/order-requests/store', [OrderController::class, 'storeRequest'])->name('order-requests.store');
    });
});
