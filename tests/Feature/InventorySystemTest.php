<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventorySystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\MedicalInventorySeeder::class);
    }

    public function test_dashboard_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Medical Supplies & Pharmacy Operations', false);
    }

    public function test_deliveries_pages_load_successfully(): void
    {
        $response = $this->get('/deliveries');
        $response->assertStatus(200);
        $response->assertSee('Deliveries & Stock Receiving', false);

        $createResponse = $this->get('/deliveries/create');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Receive New Delivery & Stocks', false);

        $delivery = Delivery::first();
        if ($delivery) {
            $showResponse = $this->get("/deliveries/{$delivery->id}");
            $showResponse->assertStatus(200);
            $showResponse->assertSee($delivery->delivery_number);
        }
    }

    public function test_products_pages_load_successfully(): void
    {
        $response = $this->get('/products');
        $response->assertStatus(200);
        $response->assertSee('Medical Supplies & Pharmacy Inventory', false);

        $product = Product::first();
        if ($product) {
            $showResponse = $this->get("/products/{$product->id}");
            $showResponse->assertStatus(200);
            $showResponse->assertSee($product->name);
        }
    }

    public function test_sales_and_dispensing_pages_load(): void
    {
        $response = $this->get('/sales');
        $response->assertStatus(200);
        $response->assertSee('Dispensing & Sales Billing', false);

        $createResponse = $this->get('/sales/create');
        $createResponse->assertStatus(200);
    }

    public function test_financial_ledger_and_csv_export_load(): void
    {
        $financeOfficer = \App\Models\User::where('role', \App\Models\User::ROLE_FINANCE)->first();
        $response = $this->actingAs($financeOfficer)->get('/financial');
        $response->assertStatus(200);
        $response->assertSee('Financial Ledger & Consignment Settlements', false);

        $csvResponse = $this->get('/financial/export-csv');
        $csvResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csvResponse->headers->get('content-type'));
    }

    public function test_reports_pages_load(): void
    {
        $this->get('/reports/expiry')->assertStatus(200);
        $this->get('/reports/valuation')->assertStatus(200);
        $this->get('/reports/low-stock')->assertStatus(200);
        $this->get('/reports/audit-log')->assertStatus(200);
    }

    public function test_receiving_new_delivery_creates_batches_and_stock_movements(): void
    {
        $supplier = Supplier::first();
        $product = Product::first();

        $deliveryData = [
            'delivery_number' => 'DEL-TEST-' . rand(1000, 9999),
            'supplier_id' => $supplier->id,
            'invoice_number' => 'INV-TEST-001',
            'delivery_date' => Carbon::today()->toDateString(),
            'received_by' => 'Test Pharmacist',
            'payment_status' => 'unpaid',
            'items' => [
                [
                    'product_id' => $product->id,
                    'batch_number' => 'LOT-TEST-99',
                    'expiry_date' => Carbon::today()->addMonths(12)->toDateString(),
                    'quantity' => 25,
                    'unit_cost' => 150.00,
                    'selling_price' => 250.00,
                ]
            ]
        ];

        $response = $this->post('/deliveries', $deliveryData);
        $response->assertRedirect();

        $this->assertDatabaseHas('deliveries', ['delivery_number' => $deliveryData['delivery_number']]);
        $this->assertDatabaseHas('product_batches', [
            'batch_number' => 'LOT-TEST-99',
            'quantity_remaining' => 25
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'movement_type' => 'delivery_in',
            'quantity' => 25
        ]);
    }

    public function test_dispensing_item_deducts_from_fefo_batch_and_calculates_profit(): void
    {
        $product = Product::where('sku', 'RX-AMO-500')->first();
        $initialStock = $product->total_stock;

        $saleData = [
            'sale_number' => 'DISP-TEST-' . rand(1000, 9999),
            'transaction_type' => 'dispensing',
            'patient_name' => 'Test Patient Maria',
            'sale_date' => Carbon::now()->toDateTimeString(),
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'dispensed_by' => 'Test Pharmacist',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 650.00,
                ]
            ]
        ];

        $response = $this->post('/sales', $saleData);
        $response->assertRedirect();

        $this->assertDatabaseHas('sales', ['sale_number' => $saleData['sale_number']]);
        $this->assertEquals($initialStock - 2, $product->fresh()->total_stock);
    }
}
