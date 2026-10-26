<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\HospitalConsignmentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth & Fast Role Switcher
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::post('switch-role', [AuthController::class, 'switchRole'])->name('switch-role');

// Dashboard (Accessible by all staff)
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

// Suppliers & Consignors
Route::resource('suppliers', SupplierController::class);

// Hospital Extensive Consignment System (HECS) - Accessible to Medical Supplies Staff, Finance & Admins
Route::prefix('hospital')->name('hospital.')->middleware('role:admin,medical_supply_staff,finance_officer')->group(function () {
    Route::get('stcg', [HospitalConsignmentController::class, 'stcg'])->name('stcg');
    Route::get('sccg', [HospitalConsignmentController::class, 'sccg'])->name('sccg');
    Route::get('stock-card', [HospitalConsignmentController::class, 'stockCard'])->name('stock-card');
    Route::get('monthly-inventory', [HospitalConsignmentController::class, 'monthlyInventory'])->name('monthly-inventory');
    Route::get('import', [HospitalConsignmentController::class, 'showImport'])->name('import');
    Route::post('import', [HospitalConsignmentController::class, 'processImport'])->name('import.process');
    Route::get('templates/{type}', [HospitalConsignmentController::class, 'downloadTemplate'])->name('template.download');
});

// Financial Management & Consignment Settlement - Finance Officers & Admins only
Route::middleware('role:admin,finance_officer')->group(function () {
    Route::get('financial', [FinancialController::class, 'index'])->name('financial.index');
    Route::get('financial/export-csv', [FinancialController::class, 'exportCsv'])->name('financial.export');
});

// User Management & Roles - Hospital Administrator only
Route::resource('users', UserController::class)->middleware('role:admin');

// Reports & Audit
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('expiry', [ReportController::class, 'expiry'])->name('expiry');
    Route::get('valuation', [ReportController::class, 'valuation'])->name('valuation');
    Route::get('low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
    Route::get('audit-log', [ReportController::class, 'auditLog'])->name('audit-log');
});
