<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RepairOrderController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MasterDataController;

// Root → Login
Route::get('/', fn() => redirect()->route('login'));

// Auth
Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(\App\Http\Middleware\AuthMiddleware::class)->group(function () {

    // Dashboard & Sync
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/sync-sheets', [DashboardController::class, 'syncSheets'])->name('sync-sheets');


    // ใบซ่อม
    Route::prefix('repair-orders')->name('repair-orders.')->group(function () {
        Route::get('/',             [RepairOrderController::class, 'index'])->name('index');
        Route::get('/create',       [RepairOrderController::class, 'create'])->name('create');
        Route::post('/',            [RepairOrderController::class, 'store'])->name('store');
        Route::get('/{id}',         [RepairOrderController::class, 'show'])->name('show');
        Route::patch('/{id}/status',[RepairOrderController::class, 'updateStatus'])->name('status');
    });

    // คลังอะไหล่
    Route::prefix('parts')->name('parts.')->group(function () {
        Route::get('/',              [PartController::class, 'index'])->name('index');
        Route::post('/',             [PartController::class, 'store'])->name('store');
        Route::put('/{id}',          [PartController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle', [PartController::class, 'toggleActive'])->name('toggle');
        Route::get('/{id}/movements',[PartController::class, 'movements'])->name('movements');
    });

    // รับอะไหล่เข้า
    Route::prefix('goods-receipts')->name('goods-receipts.')->group(function () {
        Route::get('/',        [GoodsReceiptController::class, 'index'])->name('index');
        Route::get('/create',  [GoodsReceiptController::class, 'create'])->name('create');
        Route::post('/',       [GoodsReceiptController::class, 'store'])->name('store');
        Route::get('/{id}',    [GoodsReceiptController::class, 'show'])->name('show');
    });

    // Stock Movement
    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');

    // รายงาน
    Route::get('/reports',                 [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/profit',          [ReportController::class, 'profit'])->name('reports.profit');
    Route::get('/reports/receipt-summary', [ReportController::class, 'receiptSummary'])->name('reports.receipt-summary');

    // Master Data
    Route::prefix('master-data')->name('master-data.')->group(function () {
        // Customers
        Route::get('/customers',       [MasterDataController::class, 'customers'])->name('customers');
        Route::post('/customers',      [MasterDataController::class, 'storeCustomer'])->name('customers.store');
        Route::put('/customers/{id}',  [MasterDataController::class, 'updateCustomer'])->name('customers.update');

        // Vehicles
        Route::get('/vehicles',        [MasterDataController::class, 'vehicles'])->name('vehicles');
        Route::post('/vehicles',       [MasterDataController::class, 'storeVehicle'])->name('vehicles.store');

        // Suppliers
        Route::get('/suppliers',       [MasterDataController::class, 'suppliers'])->name('suppliers');
        Route::post('/suppliers',      [MasterDataController::class, 'storeSupplier'])->name('suppliers.store');
        Route::put('/suppliers/{id}',  [MasterDataController::class, 'updateSupplier'])->name('suppliers.update');

        // API
        Route::get('/vehicles-by-customer/{customerId}', [MasterDataController::class, 'getVehiclesByCustomer'])
             ->name('vehicles.by-customer');
    });
});
