<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\Admin\SchemaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SettingController;

use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Cashier\CustomerController as CashierCustomerController;
use App\Http\Controllers\Cashier\OrderController;
use App\Http\Controllers\Cashier\ProductController as CashierProductController;
use App\Http\Controllers\Cashier\StockRequestController;

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
    Route::get('/database-schema', [SchemaController::class, 'index']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'exportDashboard'])->name('admin.dashboard.export');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications');
    Route::post('/admin/notifications/{id}/approve', [NotificationController::class, 'approve'])->name('admin.notifications.approve');
    Route::post('/admin/notifications/{id}/rejecot', [NotificationController::class, 'reject'])->name('admin.notifications.reject');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'adminMarkAllRead']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'adminMarkSingleRead']);
    Route::get('/admin/stock-requests', [StockRequestController::class, 'index'])->name('admin.stock-requests');

    Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products');
    Route::post('/products', [AdminProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/by-category', [AdminProductController::class, 'byCategory'])
        ->name('admin.products.byCategory');
    Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');
    Route::post('/products/bulk-delete', [AdminProductController::class, 'bulkDestroy'])->name('products.bulk-delete');

    Route::get('/products/uoms', [AdminProductController::class, 'indexUoms'])->name('admin.products.uoms');
    Route::post('/products/uoms', [AdminProductController::class, 'storeUom'])->name('admin.products.uoms.store');
    Route::put('/products/{id}/uoms', [AdminProductController::class, 'updateUom'])->name('admin.products.uoms.update');
    Route::delete('/products/uoms/{id}', [AdminProductController::class, 'destroyUom'])->name('admin.products.uoms.destroy');

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

    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('admin.customers');
    Route::get('/customers/{id}', [AdminCustomerController::class, 'show'])->name('admin.customers.show');
    Route::get('/customers/export/all', [AdminCustomerController::class, 'export'])->name('admin.customers.export');
    Route::get('/customers/{customer}/order/{order}', [AdminCustomerController::class, 'getOrder']);

    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');

    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('admin.activitylog');
    Route::post('/activitylog/clear', [ActivityLogController::class, 'clear'])->name('admin.activitylog.clear');
    Route::get('/activitylog/export', [ActivityLogController::class, 'export'])->name('admin.activitylog.export');

    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings');
    Route::post('/settings', [SettingController::class, 'save'])->name('admin.settings.save');
});

// Cashier Routes
Route::middleware(['auth', 'role:cashier'])->group(function () {

    Route::get('/cashier/pos', [PosController::class, 'pos'])->name('cashier.pos');
    Route::post('/cashier/checkout', [PosController::class, 'checkout'])->name('cashier.checkout');

    Route::get('/cashier/notifications', [NotificationController::class, 'cashierIndex'])->name('cashier.notifications');
    Route::post('/cashier/notifications/mark-read', [NotificationController::class, 'cashierMarkAllRead'])->name('cashier.notifications.markRead');
    Route::post('/cashier/notifications/{id}/mark-read', [NotificationController::class, 'cashierMarkSingleRead'])->name('cashier.notifications.markSingleRead');

    Route::get('/cashier/customers/search', [CashierCustomerController::class, 'search']);
    Route::post('/cashier/customers', [CashierCustomerController::class, 'store']);
    Route::get('/cashier/customers', [CashierCustomerController::class, 'index'])->name('cashier.customers');
    Route::get('/cashier/customers/export', [CashierCustomerController::class, 'export'])->name('cashier.customers.export');
    Route::put('/cashier/customers/{id}', [CashierCustomerController::class, 'update']);
    Route::get('/cashier/customers/{id}', [CashierCustomerController::class, 'show'])->name('cashier.customers.show');

    Route::get('/cashier/products', [CashierProductController::class, 'index'])->name('cashier.products');
    Route::post('/cashier/stock-loss', [CashierProductController::class, 'reportLoss']);
    Route::post('/cashier/stock-request', [StockRequestController::class, 'store']);
    Route::post('/cashier/stock-request/bulk', [StockRequestController::class, 'bulkProductRequest']);

    Route::get('/cashier/orders/export', [OrderController::class, 'export'])->name('cashier.orders.export');
    Route::get('/cashier/orders', [OrderController::class, 'index'])->name('cashier.orders');
    Route::get('/cashier/orders/{id}', [OrderController::class, 'show'])->name('cashier.orders.show');
    Route::post('/cashier/orders/{id}/refund', [OrderController::class, 'refund']);
});

// Auth routes (already there)
require __DIR__ . '/auth.php';
