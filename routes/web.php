<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index']);

// Deliveries (Tracking new deliveries of Medical Supplies & Pharmacy Stocks)
Route::resource('deliveries', DeliveryController::class)->only(['index', 'create', 'store', 'show']);
Route::post('deliveries/{delivery}/payment', [DeliveryController::class, 'recordPayment'])->name('deliveries.payment');

// Inventory: Products (Medical Supplies & Pharmacy Stocks)
Route::resource('products', ProductController::class);
Route::post('products/{product}/adjust', [ProductController::class, 'adjustStock'])->name('products.adjust');

// Sales & Dispensing
Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);

// Financial Management & Consignment Settlement
Route::get('financial', [FinancialController::class, 'index'])->name('financial.index');
Route::get('financial/export-csv', [FinancialController::class, 'exportCsv'])->name('financial.export');

// Suppliers
Route::resource('suppliers', SupplierController::class);

// Hospital Extensive Consignment System (HECS) - Roxas Memorial Provincial Hospital
Route::prefix('hospital')->name('hospital.')->group(function () {
    Route::get('stcg', [App\Http\Controllers\HospitalConsignmentController::class, 'stcg'])->name('stcg');
    Route::get('sccg', [App\Http\Controllers\HospitalConsignmentController::class, 'sccg'])->name('sccg');
    Route::get('stock-card', [App\Http\Controllers\HospitalConsignmentController::class, 'stockCard'])->name('stock-card');
    Route::get('monthly-inventory', [App\Http\Controllers\HospitalConsignmentController::class, 'monthlyInventory'])->name('monthly-inventory');
    Route::get('import', [App\Http\Controllers\HospitalConsignmentController::class, 'showImport'])->name('import');
    Route::post('import', [App\Http\Controllers\HospitalConsignmentController::class, 'processImport'])->name('import.process');
    Route::get('templates/{type}', [App\Http\Controllers\HospitalConsignmentController::class, 'downloadTemplate'])->name('template.download');
});

// Reports & Audit
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('expiry', [ReportController::class, 'expiry'])->name('expiry');
    Route::get('valuation', [ReportController::class, 'valuation'])->name('valuation');
    Route::get('low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
    Route::get('audit-log', [ReportController::class, 'auditLog'])->name('audit-log');
});
