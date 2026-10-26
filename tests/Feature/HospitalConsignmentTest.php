<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use Carbon\Carbon;
use Database\Seeders\MedicalInventorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalConsignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MedicalInventorySeeder::class);
    }

    public function test_hospital_hecs_reports_load_successfully(): void
    {
        $stcg = $this->get('/hospital/stcg');
        $stcg->assertStatus(200);
        $stcg->assertSee('STATEMENT OF CONSUMED GOODS', false);

        $sccg = $this->get('/hospital/sccg');
        $sccg->assertStatus(200);
        $sccg->assertSee('SCHEDULE OF CONSUMED GOODS', false);

        $stockCard = $this->get('/hospital/stock-card');
        $stockCard->assertStatus(200);
        $stockCard->assertSee('Stock Card', false);
        $stockCard->assertSee('GENERAL FORM NO. 18', false);

        $monthly = $this->get('/hospital/monthly-inventory');
        $monthly->assertStatus(200);
        $monthly->assertSee('Monthly Consignment Inventory Report', false);
    }

    public function test_hospital_import_page_loads_successfully(): void
    {
        $response = $this->get('/hospital/import');
        $response->assertStatus(200);
        $response->assertSee('HOSPITAL EXTENSIVE CONSIGNMENT SYSTEM', false);
        $response->assertSee('Upload Hospital Excel / CSV File', false);
        $response->assertSee('Download Official HECS Templates', false);
    }

    public function test_csv_templates_download_correctly(): void
    {
        $reg = $this->get('/hospital/templates/registry');
        $reg->assertStatus(200);
        $this->assertStringContainsString('text/csv', $reg->headers->get('content-type'));
        $this->assertStringContainsString('ItemCode', $reg->getContent());

        $del = $this->get('/hospital/templates/deliveries');
        $del->assertStatus(200);
        $this->assertStringContainsString('text/csv', $del->headers->get('content-type'));
        $this->assertStringContainsString('SPR/CO No:', $del->getContent());

        $tx = $this->get('/hospital/templates/transactions');
        $tx->assertStatus(200);
        $this->assertStringContainsString('text/csv', $tx->headers->get('content-type'));
        $this->assertStringContainsString('ItemCode', $tx->getContent());
    }

    public function test_excel_dry_run_validation_without_db_changes(): void
    {
        $csvContent = "ItemCode,Item Name,Name of Consignor,CONSIGNOR'S UNIT PRICE,MARK UP,SELLING PRICE/UNIT,Unit of Issue\r\n";
        $csvContent .= "HECSM-TEST-DRY01,SURGICAL SUTURE ETHICON,BEROVAN MARKETING INC.,120.00,36.00,156.00,PC\r\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('test_registry.csv', $csvContent);

        $initialProductsCount = Product::count();

        $response = $this->post('/hospital/import', [
            'excel_file' => $file,
            'import_type' => 'registry',
            'dry_run' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionHas('import_summary');

        // Verify NO records were written to DB
        $this->assertEquals($initialProductsCount, Product::count());
    }

    public function test_excel_real_database_import(): void
    {
        $csvContent = "ItemCode,Item Name,Name of Consignor,CONSIGNOR'S UNIT PRICE,MARK UP,SELLING PRICE/UNIT,Unit of Issue\r\n";
        $csvContent .= "HECSM-NEW-REAL01,HYDROPHILLIC GUIDEWIRE 0.035,MHJ MEDICAL AND LABORATORY SUPPLY,850.00,250.00,1100.00,PC\r\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('real_registry.csv', $csvContent);

        $response = $this->post('/hospital/import', [
            'excel_file' => $file,
            'import_type' => 'registry',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify product was created in the database
        $this->assertDatabaseHas('products', [
            'sku' => 'HECSM-NEW-REAL01',
            'default_purchase_price' => 850.00,
            'selling_price' => 1100.00,
        ]);
    }
}
