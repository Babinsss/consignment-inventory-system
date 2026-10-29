<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Delivery;
use App\Models\DepartmentRequest;
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
        $this->seed(\Database\Seeders\DepartmentSeeder::class);
        $this->seed(\Database\Seeders\SampleRequestsSeeder::class);
    }

    public function test_dashboard_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Hospital Supply Operations', false);
        $response->assertSee('Request Processing Timeline', false);
    }

    public function test_deliveries_pages_load_successfully(): void
    {
        $response = $this->get('/deliveries');
        $response->assertStatus(200);
        $response->assertSee('Inward Deliveries & Stock Intake', false);

        $createResponse = $this->get('/deliveries/create');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Receive New Delivery', false);

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

    public function test_requisition_portal_and_who_requested_flow(): void
    {
        // 1. Portal loads
        $portalResponse = $this->get('/portal');
        $portalResponse->assertStatus(200);
        $portalResponse->assertSee('Requisition Portal', false);
        $portalResponse->assertSee('Requested By', false);

        // 2. Submit new requisition
        $product = Product::first();
        $cartData = json_encode([
            [
                'id' => $product->id,
                'qty' => 5,
            ]
        ]);

        $postResponse = $this->post('/requests/submit', [
            'department_name' => 'DIALYSIS',
            'requested_by' => 'Nurse Joy Ramos, RN',
            'purpose' => 'Urgent patient medication in Dialysis Station 1',
            'is_emergency' => '1',
            'cart_data' => $cartData,
        ]);

        $postResponse->assertSessionHas('success');
        $postResponse->assertSessionHas('submitted_batch_id');

        // Verify saved in DB
        $this->assertDatabaseHas('department_requests', [
            'department_name' => 'DIALYSIS',
            'requested_by' => 'Nurse Joy Ramos, RN',
            'purpose' => 'Urgent patient medication in Dialysis Station 1',
            'status' => 'Pending',
            'quantity' => 5,
        ]);

        $createdReq = DepartmentRequest::where('requested_by', 'Nurse Joy Ramos, RN')->first();
        $this->assertNotNull($createdReq);
        $submittedBatchId = $createdReq->batch_id;

        // 3. Requests index shows who requested
        $indexResponse = $this->get('/requests');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Nurse Joy Ramos, RN');
        $indexResponse->assertSee('DIALYSIS');

        // 4. Approve batch
        $admin = \App\Models\User::first();
        $this->actingAs($admin);

        $approveResponse = $this->post("/requests/{$submittedBatchId}/approve");
        $approveResponse->assertSessionHas('success');

        $this->assertDatabaseHas('department_requests', [
            'batch_id' => $submittedBatchId,
            'status' => 'Approved',
            'issued_by' => $admin->id,
        ]);

        // 5. Printable RIS slip
        $printResponse = $this->get("/requests/{$submittedBatchId}/print");
        $printResponse->assertStatus(200);
        $printResponse->assertSee('REQUISITION AND ISSUE SLIP (RIS)');
        $printResponse->assertSee('Nurse Joy Ramos, RN');
    }

    public function test_rejects_unauthorized_department_in_portal(): void
    {
        $product = Product::first();
        $cartData = json_encode([['id' => $product->id, 'qty' => 1]]);

        $response = $this->post('/requests/submit', [
            'department_name' => 'Pediatrics Ward',
            'requested_by' => 'Nurse Joy Ramos, RN',
            'purpose' => 'Routine refill',
            'cart_data' => $cartData,
        ]);

        $response->assertSessionHasErrors(['department_name']);
    }
}
