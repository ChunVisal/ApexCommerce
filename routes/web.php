<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CashierProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StockRequestController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root route - check if logged in first
Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        return redirect('/cashier/pos');
    }

    return redirect('/login');
});

Route::post('/cashier/pin-login', [AuthenticatedSessionController::class, 'pinLogin'])->name('cashier.pin-login');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/dashboard/export', [DashboardController::class, 'exportDashboard'])->name('admin.dashboard.export');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications');
    Route::post('/admin/notifications/{id}/approve', [NotificationController::class, 'approve'])->name('admin.notifications.approve');
    Route::post('/admin/notifications/{id}/rejecot', [NotificationController::class, 'reject'])->name('admin.notifications.reject');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markSingle']);
    Route::get('/admin/stock-requests', [StockRequestController::class, 'index'])->name('admin.stock-requests');

    Route::get('/products', [ProductController::class, 'index'])->name('admin.products');
    Route::get('/products/uoms', [ProductController::class, 'uoms'])->name('admin.products.uoms');
    Route::post('/products/uoms', [ProductController::class, 'storeUom'])->name('admin.products.uoms.store');
    Route::put('/products/{id}/uoms', [ProductController::class, 'updateUom'])->name('admin.products.uoms.update');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/by-category', [ProductController::class, 'byCategory'])
        ->name('admin.products.byCategory');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulk-delete');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('admin.inventory');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjustStock'])->name('admin.inventory.adjust');
    Route::get('/inventory/export', [InventoryController::class, 'export'])->name('admin.inventory.export');
    Route::post('/inventory/stock-drop', [InventoryController::class, 'stockDrop'])->name('admin.products.stock-drop');
    Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('admin.inventory.movements');
    Route::get('/stockmovements/export', [InventoryController::class, 'exportMovements'])->name('admin.stockmovement.export');

    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/users/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('admin.users.bulk-deactivate');
    Route::post('/users/bulk-delete', [UserController::class, 'bulkDestroy'])->name('admin.users.bulk-delete');

    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');

    Route::get('/customers', [AdminController::class, 'customers'])->name('admin.customers');
    Route::get('/customers', [CustomerController::class, 'adminIndex'])->name('admin.customers');
    Route::get('/customers/{id}', [CustomerController::class, 'adminShow'])->name('admin.customers.show');
    Route::get('/customers/export/all', [CustomerController::class, 'adminExport'])->name('admin.customers.export');
    Route::get('/customers/{customer}/order/{order}', [CustomerController::class, 'getOrder']);

    Route::get('/activitylog', [AdminController::class, 'activitylog'])->name('admin.activitylog');
});
// Cashier Routes
Route::middleware(['auth', 'role:cashier'])->group(function () {

    Route::get('/cashier/pos', [CashierController::class, 'pos'])->name('cashier.pos');
    Route::post('/cashier/checkout', [CashierController::class, 'checkout'])->name('cashier.checkout');

    Route::get('/cashier/notifications', [NotificationController::class, 'cashierIndex'])->name('cashier.notifications');
    Route::post('/cashier/notifications/mark-read', [NotificationController::class, 'markRead'])->name('cashier.notifications.markRead');
    Route::post('/cashier/notifications/{id}/mark-read', [NotificationController::class, 'markSingleRead'])->name('cashier.notifications.markSingleRead');
    Route::post('/cashier/stock-return', [NotificationController::class, 'returnStock']);

    Route::get('/cashier/customers/search', [CustomerController::class, 'search']);
    Route::post('/cashier/customers', [CustomerController::class, 'store']);
    Route::get('/cashier/customers', [CustomerController::class, 'index'])->name('cashier.customers');
    Route::get('/cashier/customers/export', [CustomerController::class, 'export'])->name('cashier.customers.export');
    Route::put('/cashier/customers/{id}', [CustomerController::class, 'update']);
    Route::get('/cashier/customers/{id}', [CustomerController::class, 'show'])->name('cashier.customers.show');

    Route::get('/cashier/products', [CashierProductController::class, 'index'])->name('cashier.products');
    Route::post('/cashier/stock-request', [StockRequestController::class, 'store']);
    Route::post('/cashier/stock-request/bulk', [StockRequestController::class, 'bulkProductRequest']);

    Route::get('/cashier/orders/export', [OrderController::class, 'export'])->name('cashier.orders.export');
    Route::get('/cashier/orders', [OrderController::class, 'index'])->name('cashier.orders');
    Route::get('/cashier/orders/{id}', [OrderController::class, 'show'])->name('cashier.orders.show');
    Route::post('/cashier/orders/{id}/refund', [OrderController::class, 'refund']);
});

// Auth routes (already there)
require __DIR__ . '/auth.php';
