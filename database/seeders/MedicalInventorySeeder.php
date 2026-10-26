<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MedicalInventorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Admin / Pharmacist user if not exists
        $user = User::firstOrCreate(
            ['email' => 'admin@medinventory.com'],
            [
                'name' => 'Chief Pharmacist / Inventory Manager',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Suppliers
        $suppliers = [
            [
                'name' => 'Zuellig Pharma Corp.',
                'code' => 'SUP-ZUELLIG',
                'contact_person' => 'Maria Santos (Account Exec)',
                'phone' => '+63 2 8988 8888',
                'email' => 'orders@zuelligpharma.com',
                'address' => 'KM 14 West Service Rd, Taguig, Metro Manila',
                'tax_id' => '000-123-456-000',
                'payment_terms' => 'Net 30',
                'status' => 'active',
                'notes' => 'Primary distributor for cardiovascular, diabetes, and cold-chain biologics.',
            ],
            [
                'name' => 'Unilab Pharmaceuticals Inc.',
                'code' => 'SUP-UNILAB',
                'contact_person' => 'Robert Gonzales',
                'phone' => '+63 2 8858 1000',
                'email' => 'institutional.sales@unilab.com.ph',
                'address' => '66 United Street, Mandaluyong City',
                'tax_id' => '000-234-567-000',
                'payment_terms' => 'Net 30',
                'status' => 'active',
                'notes' => 'Major manufacturer of oral antibiotics, pain relief, and maintenance drugs.',
            ],
            [
                'name' => 'B. Braun Medical Supplies Inc.',
                'code' => 'SUP-BBRAUN',
                'contact_person' => 'Elena Reyes',
                'phone' => '+63 2 8588 5600',
                'email' => 'hospital.care@bbraun.com',
                'address' => 'B. Braun Center, 15th Flr, Pasig City',
                'tax_id' => '000-345-678-000',
                'payment_terms' => 'Net 60',
                'status' => 'active',
                'notes' => 'Supplies IV fluids, infusion lines, venflons, and surgical consumables.',
            ],
            [
                'name' => 'Apex Healthcare Disposables Ltd.',
                'code' => 'SUP-APEX',
                'contact_person' => 'David Lim',
                'phone' => '+63 2 8712 3456',
                'email' => 'sales@apexmedical.com',
                'address' => '45 Industrial Ave, Quezon City',
                'tax_id' => '000-456-789-000',
                'payment_terms' => 'Net 15',
                'status' => 'active',
                'notes' => 'Syringes, nitrile gloves, PPE masks, sterile gauze and bandages.',
            ],
            [
                'name' => 'Roche Diagnostics Philippines (Consignment)',
                'code' => 'SUP-ROCHE-CONSIGN',
                'contact_person' => 'Dr. Karen Mendoza',
                'phone' => '+63 2 8893 2000',
                'email' => 'consignment@roche.com',
                'address' => '22F Cyber Sigma, Lawton Ave, Taguig City',
                'tax_id' => '000-567-890-000',
                'payment_terms' => 'Consignment',
                'status' => 'active',
                'notes' => 'Consignment inventory: Hospital pays monthly based on actual consumption/dispensing.',
            ],
        ];

        $supplierModels = [];
        foreach ($suppliers as $sData) {
            $code = $sData['code'];
            unset($sData['code']);
            $supplierModels[$code] = Supplier::firstOrCreate(['code' => $code], $sData);
        }

        // 3. Categories
        $categories = [
            [
                'name' => 'Antibiotics & Anti-Infectives',
                'slug' => 'antibiotics-anti-infectives',
                'type' => 'pharmacy',
                'description' => 'Oral and injectable antibacterial and antifungal medications.',
            ],
            [
                'name' => 'Analgesics & Antipyretics',
                'slug' => 'analgesics-antipyretics',
                'type' => 'pharmacy',
                'description' => 'Pain relievers, fever reducers, and anti-inflammatory drugs.',
            ],
            [
                'name' => 'Cardiovascular & Maintenance Meds',
                'slug' => 'cardiovascular-maintenance',
                'type' => 'pharmacy',
                'description' => 'Antihypertensives, cholesterol lowerers, and cardiac medications.',
            ],
            [
                'name' => 'IV Fluids & Electrolytes',
                'slug' => 'iv-fluids-electrolytes',
                'type' => 'pharmacy',
                'description' => 'Intravenous infusion solutions, saline, and electrolyte additives.',
            ],
            [
                'name' => 'Cold-Chain & Biologicals',
                'slug' => 'cold-chain-biologicals',
                'type' => 'pharmacy',
                'description' => 'Insulins, vaccines, and biologics requiring 2°C - 8°C storage.',
            ],
            [
                'name' => 'Hypodermic Syringes & Needles',
                'slug' => 'syringes-needles',
                'type' => 'medical_supply',
                'description' => 'Sterile disposable syringes, safety needles, and insulin syringes.',
            ],
            [
                'name' => 'Wound Care & Sterile Dressings',
                'slug' => 'wound-care-dressings',
                'type' => 'medical_supply',
                'description' => 'Gauze pads, adhesive bandages, surgical tape, and antiseptics.',
            ],
            [
                'name' => 'Infusion & Vascular Access',
                'slug' => 'infusion-vascular-access',
                'type' => 'medical_supply',
                'description' => 'IV cannulas, macro/micro drip infusion sets, 3-way stopcocks.',
            ],
            [
                'name' => 'Personal Protective Equipment (PPE)',
                'slug' => 'ppe-infection-control',
                'type' => 'medical_supply',
                'description' => 'Nitrile gloves, N95 respirators, surgical masks, and isolation gowns.',
            ],
            [
                'name' => 'Diagnostic Reagents & Test Strips (Consignment)',
                'slug' => 'diagnostic-strips-consignment',
                'type' => 'medical_supply',
                'description' => 'Blood glucose monitoring strips, rapid test kits, and lab cartridges.',
            ],
        ];

        $categoryModels = [];
        foreach ($categories as $cData) {
            $slug = $cData['slug'];
            unset($cData['slug']);
            $categoryModels[$slug] = Category::firstOrCreate(['slug' => $slug], $cData);
        }

        // 4. Products List (Medical Supplies + Pharmacy Stocks + Consignment)
        $products = [
            // Pharmacy Stocks
            [
                'category_slug' => 'antibiotics-anti-infectives',
                'supplier_code' => 'SUP-UNILAB',
                'item_type' => 'pharmacy',
                'sku' => 'RX-AMO-500',
                'barcode' => '4800016100101',
                'name' => 'Amoxicillin 500mg Capsule',
                'generic_name' => 'Amoxicillin Trihydrate',
                'brand_name' => 'Himox / Amoclav',
                'dosage_form' => '500mg Capsule',
                'unit_of_measure' => 'box of 100',
                'inventory_type' => 'owned',
                'min_stock_alert' => 20,
                'max_stock_level' => 300,
                'default_purchase_price' => 350.00,
                'selling_price' => 650.00,
                'storage_condition' => 'room_temperature',
                'description' => 'Broad-spectrum penicillin antibiotic for bacterial infections.',
            ],
            [
                'category_slug' => 'antibiotics-anti-infectives',
                'supplier_code' => 'SUP-ZUELLIG',
                'item_type' => 'pharmacy',
                'sku' => 'RX-CEF-1G',
                'barcode' => '4800016100200',
                'name' => 'Ceftriaxone 1g Powder for Injection Vial',
                'generic_name' => 'Ceftriaxone Sodium',
                'brand_name' => 'Rocephin',
                'dosage_form' => '1g Vial + Diluent',
                'unit_of_measure' => 'vial',
                'inventory_type' => 'owned',
                'min_stock_alert' => 50,
                'max_stock_level' => 500,
                'default_purchase_price' => 145.00,
                'selling_price' => 280.00,
                'storage_condition' => 'room_temperature',
                'description' => 'Third-generation cephalosporin antibiotic for serious hospital-acquired infections.',
            ],
            [
                'category_slug' => 'analgesics-antipyretics',
                'supplier_code' => 'SUP-UNILAB',
                'item_type' => 'pharmacy',
                'sku' => 'RX-PAR-500',
                'barcode' => '4800016100309',
                'name' => 'Paracetamol 500mg Tablet',
                'generic_name' => 'Paracetamol / Acetaminophen',
                'brand_name' => 'Biogesic',
                'dosage_form' => '500mg Tablet',
                'unit_of_measure' => 'box of 100',
                'inventory_type' => 'owned',
                'min_stock_alert' => 30,
                'max_stock_level' => 600,
                'default_purchase_price' => 180.00,
                'selling_price' => 400.00,
                'storage_condition' => 'room_temperature',
                'description' => 'First-line analgesic and antipyretic for pain and fever relief.',
            ],
            [
                'category_slug' => 'cardiovascular-maintenance',
                'supplier_code' => 'SUP-UNILAB',
                'item_type' => 'pharmacy',
                'sku' => 'RX-LOS-50',
                'barcode' => '4800016100408',
                'name' => 'Losartan Potassium 50mg Tablet',
                'generic_name' => 'Losartan Potassium',
                'brand_name' => 'Lifezar',
                'dosage_form' => '50mg Film-Coated Tablet',
                'unit_of_measure' => 'box of 100',
                'inventory_type' => 'owned',
                'min_stock_alert' => 15,
                'max_stock_level' => 250,
                'default_purchase_price' => 420.00,
                'selling_price' => 780.00,
                'storage_condition' => 'room_temperature',
                'description' => 'Angiotensin II receptor blocker for hypertension and renal protection.',
            ],
            [
                'category_slug' => 'iv-fluids-electrolytes',
                'supplier_code' => 'SUP-BBRAUN',
                'item_type' => 'pharmacy',
                'sku' => 'RX-NS-09-500',
                'barcode' => '4800016100507',
                'name' => '0.9% Sodium Chloride (Normal Saline) 500ml',
                'generic_name' => 'Sodium Chloride 0.9% IV',
                'brand_name' => 'Ecoflac Plus',
                'dosage_form' => '500ml IV Solution Bottle',
                'unit_of_measure' => 'bottle',
                'inventory_type' => 'owned',
                'min_stock_alert' => 80,
                'max_stock_level' => 1000,
                'default_purchase_price' => 48.00,
                'selling_price' => 110.00,
                'storage_condition' => 'room_temperature',
                'description' => 'Isotonic crystalloid fluid for intravascular rehydration and drug reconstitution.',
            ],
            [
                'category_slug' => 'cold-chain-biologicals',
                'supplier_code' => 'SUP-ZUELLIG',
                'item_type' => 'pharmacy',
                'sku' => 'RX-INS-GLAR',
                'barcode' => '4800016100606',
                'name' => 'Insulin Glargine 100 Units/ml Solostar Pen',
                'generic_name' => 'Insulin Glargine (rDNA origin)',
                'brand_name' => 'Lantus SoloStar',
                'dosage_form' => '3ml Prefilled Pen',
                'unit_of_measure' => 'piece',
                'inventory_type' => 'owned',
                'min_stock_alert' => 10,
                'max_stock_level' => 80,
                'default_purchase_price' => 620.00,
                'selling_price' => 950.00,
                'storage_condition' => 'refrigerated_2_8c',
                'description' => 'Long-acting basal insulin analog for diabetes mellitus. Must store at 2°C - 8°C.',
            ],

            // Medical Supplies
            [
                'category_slug' => 'syringes-needles',
                'supplier_code' => 'SUP-APEX',
                'item_type' => 'medical_supply',
                'sku' => 'MED-SYR-05ML',
                'barcode' => '4800016200108',
                'name' => 'Disposable Syringe 5ml w/ 23G Needle',
                'generic_name' => 'Luer-Lock Hypodermic Syringe',
                'brand_name' => 'ApexSafe',
                'dosage_form' => 'Sterile Individual Peel Pack',
                'unit_of_measure' => 'box of 100',
                'inventory_type' => 'owned',
                'min_stock_alert' => 25,
                'max_stock_level' => 400,
                'default_purchase_price' => 320.00,
                'selling_price' => 580.00,
                'storage_condition' => 'room_temperature',
                'description' => 'Pyrogen-free, sterile single-use 5ml syringe with clear graduations.',
            ],
            [
                'category_slug' => 'ppe-infection-control',
                'supplier_code' => 'SUP-APEX',
                'item_type' => 'medical_supply',
                'sku' => 'MED-GLV-NIT-M',
                'barcode' => '4800016200207',
                'name' => 'Nitrile Medical Examination Gloves (Medium)',
                'generic_name' => 'Powder-Free Nitrile Gloves',
                'brand_name' => 'ApexShield',
                'dosage_form' => 'Non-Sterile Dispenser Box',
                'unit_of_measure' => 'box of 100',
                'inventory_type' => 'owned',
                'min_stock_alert' => 40,
                'max_stock_level' => 500,
                'default_purchase_price' => 240.00,
                'selling_price' => 450.00,
                'storage_condition' => 'room_temperature',
                'description' => 'Latex-free, textured fingertips, chemo-rated nitrile exam gloves.',
            ],
            [
                'category_slug' => 'infusion-vascular-access',
                'supplier_code' => 'SUP-BBRAUN',
                'item_type' => 'medical_supply',
                'sku' => 'MED-IV-CAN-20G',
                'barcode' => '4800016200306',
                'name' => 'Introcan Safety IV Cannula 20G (Pink)',
                'generic_name' => 'Peripheral IV Catheter w/ Safety Shield',
                'brand_name' => 'B. Braun Introcan',
                'dosage_form' => '20G x 1.25" Sterile Blister Pack',
                'unit_of_measure' => 'box of 50',
                'inventory_type' => 'owned',
                'min_stock_alert' => 15,
                'max_stock_level' => 200,
                'default_purchase_price' => 1450.00,
                'selling_price' => 2200.00,
                'storage_condition' => 'room_temperature',
                'description' => 'Passive needle stick injury prevention intravenous catheter.',
            ],
            [
                'category_slug' => 'wound-care-dressings',
                'supplier_code' => 'SUP-APEX',
                'item_type' => 'medical_supply',
                'sku' => 'MED-GZ-4X4',
                'barcode' => '4800016200405',
                'name' => 'Sterile Gauze Sponge Pads 4x4 (8-Ply)',
                'generic_name' => 'Sterile Cotton Gauze 4"x4"',
                'brand_name' => 'ApexCare',
                'dosage_form' => 'Box of 100 Pouches (2 pcs/pouch)',
                'unit_of_measure' => 'box of 100',
                'inventory_type' => 'owned',
                'min_stock_alert' => 20,
                'max_stock_level' => 300,
                'default_purchase_price' => 290.00,
                'selling_price' => 520.00,
                'storage_condition' => 'room_temperature',
                'description' => '100% absorbent cotton, lint-free sterile gauze for surgical and wound dressing.',
            ],
            // Consignment Medical Stocks
            [
                'category_slug' => 'diagnostic-strips-consignment',
                'supplier_code' => 'SUP-ROCHE-CONSIGN',
                'item_type' => 'medical_supply',
                'sku' => 'CSG-ACCU-STRIP-50',
                'barcode' => '4800016300105',
                'name' => 'Accu-Chek Instant Blood Glucose Test Strips',
                'generic_name' => 'Capillary Blood Glucose Reagent Strips',
                'brand_name' => 'Accu-Chek Instant',
                'dosage_form' => 'Vial of 50 Strips',
                'unit_of_measure' => 'vial of 50',
                'inventory_type' => 'consignment',
                'min_stock_alert' => 15,
                'max_stock_level' => 150,
                'default_purchase_price' => 750.00, // Consignment settlement price to supplier
                'selling_price' => 1150.00,
                'storage_condition' => 'room_temperature',
                'description' => 'CONSIGNMENT STOCK: Supplied by Roche. Hospital pays ₱750 per unit consumed/dispensed.',
            ],
            [
                'category_slug' => 'diagnostic-strips-consignment',
                'supplier_code' => 'SUP-ROCHE-CONSIGN',
                'item_type' => 'medical_supply',
                'sku' => 'CSG-CARTRIDGE-ABG',
                'barcode' => '4800016300204',
                'name' => 'cobas b 123 Blood Gas & Electrolyte Sensor Cartridge',
                'generic_name' => 'Critical Care POCT Sensor Multi-Parameter',
                'brand_name' => 'Roche cobas b 123',
                'dosage_form' => 'Multi-Use Analytical Cartridge',
                'unit_of_measure' => 'cartridge',
                'inventory_type' => 'consignment',
                'min_stock_alert' => 4,
                'max_stock_level' => 20,
                'default_purchase_price' => 12500.00,
                'selling_price' => 16800.00,
                'storage_condition' => 'room_temperature',
                'description' => 'CONSIGNMENT STOCK: High-value ICU/ER blood gas sensor cartridge settled upon machine installation.',
            ],
        ];

        $productModels = [];
        foreach ($products as $pData) {
            $catSlug = $pData['category_slug'];
            $supCode = $pData['supplier_code'];
            $sku = $pData['sku'];
            unset($pData['category_slug'], $pData['supplier_code'], $pData['sku']);

            $pData['category_id'] = $categoryModels[$catSlug]->id;
            $pData['supplier_id'] = $supplierModels[$supCode]->id;

            $productModels[$sku] = Product::firstOrCreate(['sku' => $sku], $pData);
        }

        // 5. Create Deliveries (Receiving Stock) with Batches
        // Delivery 1: Historical Delivery (Unilab - Paid)
        if (Delivery::where('delivery_number', 'DEL-2026-001')->exists()) {
            return;
        }

        $del1 = Delivery::create([
            'delivery_number' => 'DEL-2026-001',
            'invoice_number' => 'UNILAB-SI-99421',
            'supplier_id' => $supplierModels['SUP-UNILAB']->id,
            'delivery_date' => Carbon::now()->subDays(45)->toDateString(),
            'received_by' => 'Pharm. Angela Cruz, RPh',
            'status' => 'received',
            'total_amount' => 44200.00,
            'payment_status' => 'paid',
            'amount_paid' => 44200.00,
            'due_date' => Carbon::now()->subDays(15)->toDateString(),
            'notes' => 'Bulk replenishment for emergency oral antibiotics and analgesics.',
        ]);

        // Items for Delivery 1
        $d1_items = [
            [
                'product_sku' => 'RX-AMO-500',
                'batch_no' => 'LOT-AMX-25A',
                'lot_no' => 'AMX202511',
                'mfg_date' => Carbon::now()->subMonths(5)->toDateString(),
                'exp_date' => Carbon::now()->addMonths(18)->toDateString(), // Safe expiry
                'qty' => 50,
                'unit_cost' => 350.00,
                'unit_sell' => 650.00,
            ],
            [
                'product_sku' => 'RX-PAR-500',
                'batch_no' => 'LOT-PAR-25C',
                'lot_no' => 'PAR202510',
                'mfg_date' => Carbon::now()->subMonths(6)->toDateString(),
                'exp_date' => Carbon::now()->addMonths(24)->toDateString(), // Safe expiry
                'qty' => 80,
                'unit_cost' => 180.00,
                'unit_sell' => 400.00,
            ],
            [
                'product_sku' => 'RX-LOS-50',
                'batch_no' => 'LOT-LOS-25B',
                'lot_no' => 'LOS202512',
                'mfg_date' => Carbon::now()->subMonths(4)->toDateString(),
                'exp_date' => Carbon::now()->addMonths(14)->toDateString(), // Safe expiry
                'qty' => 30,
                'unit_cost' => 410.00,
                'unit_sell' => 780.00,
            ],
        ];

        foreach ($d1_items as $item) {
            $prod = $productModels[$item['product_sku']];
            $batch = ProductBatch::create([
                'product_id' => $prod->id,
                'delivery_id' => $del1->id,
                'batch_number' => $item['batch_no'],
                'lot_number' => $item['lot_no'],
                'manufacture_date' => $item['mfg_date'],
                'expiry_date' => $item['exp_date'],
                'quantity_received' => $item['qty'],
                'quantity_remaining' => $item['qty'] - 12, // some already dispensed
                'unit_purchase_cost' => $item['unit_cost'],
                'unit_selling_price' => $item['unit_sell'],
                'status' => 'active',
            ]);

            DeliveryItem::create([
                'delivery_id' => $del1->id,
                'product_id' => $prod->id,
                'batch_id' => $batch->id,
                'batch_number' => $item['batch_no'],
                'expiry_date' => $item['exp_date'],
                'quantity' => $item['qty'],
                'unit_cost' => $item['unit_cost'],
                'subtotal' => $item['qty'] * $item['unit_cost'],
                'selling_price' => $item['unit_sell'],
            ]);

            StockMovement::create([
                'product_id' => $prod->id,
                'batch_id' => $batch->id,
                'movement_type' => 'delivery_in',
                'quantity' => $item['qty'],
                'balance_before' => 0,
                'balance_after' => $item['qty'],
                'reference_type' => Delivery::class,
                'reference_id' => $del1->id,
                'unit_cost' => $item['unit_cost'],
                'reason' => "Delivery received: {$del1->delivery_number}",
                'user_name' => $del1->received_by,
                'created_at' => Carbon::now()->subDays(45),
            ]);
        }

        // Payment record for Delivery 1
        SupplierPayment::create([
            'payment_number' => 'PAY-2026-001',
            'supplier_id' => $supplierModels['SUP-UNILAB']->id,
            'delivery_id' => $del1->id,
            'amount' => 44200.00,
            'payment_date' => Carbon::now()->subDays(20)->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference_number' => 'BDO-TRX-8831920',
            'notes' => 'Settled in full via BDO online wire transfer.',
            'recorded_by' => 'Accounting Dept / Finance Officer',
        ]);

        // Delivery 2: Recent Delivery (B. Braun - Partially Paid)
        $del2 = Delivery::create([
            'delivery_number' => 'DEL-2026-002',
            'invoice_number' => 'BB-INV-55092',
            'supplier_id' => $supplierModels['SUP-BBRAUN']->id,
            'delivery_date' => Carbon::now()->subDays(12)->toDateString(),
            'received_by' => 'Staff Nurse Mark V.',
            'status' => 'received',
            'total_amount' => 48200.00,
            'payment_status' => 'partially_paid',
            'amount_paid' => 20000.00,
            'due_date' => Carbon::now()->addDays(48)->toDateString(),
            'notes' => 'IV fluids and safety venflons delivery.',
        ]);

        $d2_items = [
            [
                'product_sku' => 'RX-NS-09-500',
                'batch_no' => 'LOT-NS-26A',
                'lot_no' => 'BB202601',
                'mfg_date' => Carbon::now()->subMonths(2)->toDateString(),
                'exp_date' => Carbon::now()->addMonths(22)->toDateString(),
                'qty' => 400,
                'unit_cost' => 48.00,
                'unit_sell' => 110.00,
            ],
            [
                'product_sku' => 'MED-IV-CAN-20G',
                'batch_no' => 'LOT-IVC-26B',
                'lot_no' => 'BB202602',
                'mfg_date' => Carbon::now()->subMonths(3)->toDateString(),
                'exp_date' => Carbon::now()->addMonths(36)->toDateString(),
                'qty' => 20,
                'unit_cost' => 1450.00,
                'unit_sell' => 2200.00,
            ],
        ];

        foreach ($d2_items as $item) {
            $prod = $productModels[$item['product_sku']];
            $batch = ProductBatch::create([
                'product_id' => $prod->id,
                'delivery_id' => $del2->id,
                'batch_number' => $item['batch_no'],
                'lot_number' => $item['lot_no'],
                'manufacture_date' => $item['mfg_date'],
                'expiry_date' => $item['exp_date'],
                'quantity_received' => $item['qty'],
                'quantity_remaining' => $item['qty'] - 15,
                'unit_purchase_cost' => $item['unit_cost'],
                'unit_selling_price' => $item['unit_sell'],
                'status' => 'active',
            ]);

            DeliveryItem::create([
                'delivery_id' => $del2->id,
                'product_id' => $prod->id,
                'batch_id' => $batch->id,
                'batch_number' => $item['batch_no'],
                'expiry_date' => $item['exp_date'],
                'quantity' => $item['qty'],
                'unit_cost' => $item['unit_cost'],
                'subtotal' => $item['qty'] * $item['unit_cost'],
                'selling_price' => $item['unit_sell'],
            ]);

            StockMovement::create([
                'product_id' => $prod->id,
                'batch_id' => $batch->id,
                'movement_type' => 'delivery_in',
                'quantity' => $item['qty'],
                'balance_before' => 0,
                'balance_after' => $item['qty'],
                'reference_type' => Delivery::class,
                'reference_id' => $del2->id,
                'unit_cost' => $item['unit_cost'],
                'reason' => "Delivery received: {$del2->delivery_number}",
                'user_name' => $del2->received_by,
                'created_at' => Carbon::now()->subDays(12),
            ]);
        }

        SupplierPayment::create([
            'payment_number' => 'PAY-2026-002',
            'supplier_id' => $supplierModels['SUP-BBRAUN']->id,
            'delivery_id' => $del2->id,
            'amount' => 20000.00,
            'payment_date' => Carbon::now()->subDays(5)->toDateString(),
            'payment_method' => 'cheque',
            'reference_number' => 'CHK-METRO-004921',
            'notes' => 'Partial payment deposit on delivery DEL-2026-002.',
            'recorded_by' => 'Hospital Treasurer',
        ]);

        // Delivery 3: Medical Disposables & PPE (Apex - Unpaid, with near-expiry item for alert testing)
        $del3 = Delivery::create([
            'delivery_number' => 'DEL-2026-003',
            'invoice_number' => 'APEX-DR-7819',
            'supplier_id' => $supplierModels['SUP-APEX']->id,
            'delivery_date' => Carbon::now()->subDays(4)->toDateString(),
            'received_by' => 'Warehouse Custodian Leo B.',
            'status' => 'received',
            'total_amount' => 38300.00,
            'payment_status' => 'unpaid',
            'amount_paid' => 0.00,
            'due_date' => Carbon::now()->addDays(11)->toDateString(),
            'notes' => 'Syringes, nitrile exam gloves, and sterile gauze.',
        ]);

        $d3_items = [
            [
                'product_sku' => 'MED-SYR-05ML',
                'batch_no' => 'LOT-SYR-26D',
                'lot_no' => 'AP202603',
                'mfg_date' => Carbon::now()->subMonths(2)->toDateString(),
                'exp_date' => Carbon::now()->addMonths(30)->toDateString(),
                'qty' => 50,
                'unit_cost' => 320.00,
                'unit_sell' => 580.00,
            ],
            [
                'product_sku' => 'MED-GLV-NIT-M',
                'batch_no' => 'LOT-GLV-26M',
                'lot_no' => 'AP202604',
                'mfg_date' => Carbon::now()->subMonths(1)->toDateString(),
                'exp_date' => Carbon::now()->addMonths(24)->toDateString(),
                'qty' => 70,
                'unit_cost' => 240.00,
                'unit_sell' => 450.00,
            ],
            [
                'product_sku' => 'MED-GZ-4X4',
                'batch_no' => 'LOT-GZ-EXP-SOON',
                'lot_no' => 'AP202410',
                'mfg_date' => Carbon::now()->subMonths(22)->toDateString(),
                'exp_date' => Carbon::now()->addDays(25)->toDateString(), // CRITICAL NEAR EXPIRY! Tests expiry warning system
                'qty' => 19,
                'unit_cost' => 290.00,
                'unit_sell' => 520.00,
            ],
        ];

        foreach ($d3_items as $item) {
            $prod = $productModels[$item['product_sku']];
            $batch = ProductBatch::create([
                'product_id' => $prod->id,
                'delivery_id' => $del3->id,
                'batch_number' => $item['batch_no'],
                'lot_number' => $item['lot_no'],
                'manufacture_date' => $item['mfg_date'],
                'expiry_date' => $item['exp_date'],
                'quantity_received' => $item['qty'],
                'quantity_remaining' => $item['qty'],
                'unit_purchase_cost' => $item['unit_cost'],
                'unit_selling_price' => $item['unit_sell'],
                'status' => 'active',
            ]);

            DeliveryItem::create([
                'delivery_id' => $del3->id,
                'product_id' => $prod->id,
                'batch_id' => $batch->id,
                'batch_number' => $item['batch_no'],
                'expiry_date' => $item['exp_date'],
                'quantity' => $item['qty'],
                'unit_cost' => $item['unit_cost'],
                'subtotal' => $item['qty'] * $item['unit_cost'],
                'selling_price' => $item['unit_sell'],
            ]);

            StockMovement::create([
                'product_id' => $prod->id,
                'batch_id' => $batch->id,
                'movement_type' => 'delivery_in',
                'quantity' => $item['qty'],
                'balance_before' => 0,
                'balance_after' => $item['qty'],
                'reference_type' => Delivery::class,
                'reference_id' => $del3->id,
                'unit_cost' => $item['unit_cost'],
                'reason' => "Delivery received: {$del3->delivery_number}",
                'user_name' => $del3->received_by,
                'created_at' => Carbon::now()->subDays(4),
            ]);
        }

        // Delivery 4: Consignment Delivery (Roche Diagnostics)
        $del4 = Delivery::create([
            'delivery_number' => 'DEL-2026-004-CSG',
            'invoice_number' => 'ROCHE-CSG-1029',
            'supplier_id' => $supplierModels['SUP-ROCHE-CONSIGN']->id,
            'delivery_date' => Carbon::now()->subDays(8)->toDateString(),
            'received_by' => 'Laboratory Head Tech Sarah T.',
            'status' => 'received',
            'total_amount' => 102500.00,
            'payment_status' => 'consignment_term',
            'amount_paid' => 0.00,
            'due_date' => Carbon::now()->addDays(22)->toDateString(),
            'notes' => 'CONSIGNMENT INVENTORY: Reagent cartridges and glucose test strips on consignment.',
        ]);

        $d4_items = [
            [
                'product_sku' => 'CSG-ACCU-STRIP-50',
                'batch_no' => 'LOT-ROC-STRIP-99',
                'lot_no' => 'RC202605',
                'mfg_date' => Carbon::now()->subMonths(3)->toDateString(),
                'exp_date' => Carbon::now()->addMonths(15)->toDateString(),
                'qty' => 50,
                'unit_cost' => 750.00,
                'unit_sell' => 1150.00,
            ],
            [
                'product_sku' => 'CSG-CARTRIDGE-ABG',
                'batch_no' => 'LOT-ROC-ABG-02',
                'lot_no' => 'RC202608',
                'mfg_date' => Carbon::now()->subMonths(2)->toDateString(),
                'exp_date' => Carbon::now()->addMonths(10)->toDateString(),
                'qty' => 5,
                'unit_cost' => 12500.00,
                'unit_sell' => 16800.00,
            ],
        ];

        foreach ($d4_items as $item) {
            $prod = $productModels[$item['product_sku']];
            $batch = ProductBatch::create([
                'product_id' => $prod->id,
                'delivery_id' => $del4->id,
                'batch_number' => $item['batch_no'],
                'lot_number' => $item['lot_no'],
                'manufacture_date' => $item['mfg_date'],
                'expiry_date' => $item['exp_date'],
                'quantity_received' => $item['qty'],
                'quantity_remaining' => $item['qty'] - 6,
                'unit_purchase_cost' => $item['unit_cost'],
                'unit_selling_price' => $item['unit_sell'],
                'status' => 'active',
            ]);

            DeliveryItem::create([
                'delivery_id' => $del4->id,
                'product_id' => $prod->id,
                'batch_id' => $batch->id,
                'batch_number' => $item['batch_no'],
                'expiry_date' => $item['exp_date'],
                'quantity' => $item['qty'],
                'unit_cost' => $item['unit_cost'],
                'subtotal' => $item['qty'] * $item['unit_cost'],
                'selling_price' => $item['unit_sell'],
            ]);

            StockMovement::create([
                'product_id' => $prod->id,
                'batch_id' => $batch->id,
                'movement_type' => 'delivery_in',
                'quantity' => $item['qty'],
                'balance_before' => 0,
                'balance_after' => $item['qty'],
                'reference_type' => Delivery::class,
                'reference_id' => $del4->id,
                'unit_cost' => $item['unit_cost'],
                'reason' => "Consignment delivery received: {$del4->delivery_number}",
                'user_name' => $del4->received_by,
                'created_at' => Carbon::now()->subDays(8),
            ]);
        }

        // Delivery 5: Zuellig Pharma (Antibiotic injectable & Cold Chain Insulin)
        $del5 = Delivery::create([
            'delivery_number' => 'DEL-2026-005',
            'invoice_number' => 'ZP-DR-33921',
            'supplier_id' => $supplierModels['SUP-ZUELLIG']->id,
            'delivery_date' => Carbon::now()->subDays(2)->toDateString(),
            'received_by' => 'Pharm. Angela Cruz, RPh',
            'status' => 'received',
            'total_amount' => 45500.00,
            'payment_status' => 'unpaid',
            'amount_paid' => 0.00,
            'due_date' => Carbon::now()->addDays(28)->toDateString(),
            'notes' => 'Cold chain verified upon arrival (4.2°C temperature logger cleared).',
        ]);

        $d5_items = [
            [
                'product_sku' => 'RX-CEF-1G',
                'batch_no' => 'LOT-CEF-26E',
                'lot_no' => 'ZP202601',
                'mfg_date' => Carbon::now()->subMonths(1)->toDateString(),
                'exp_date' => Carbon::now()->addMonths(20)->toDateString(),
                'qty' => 100,
                'unit_cost' => 145.00,
                'unit_sell' => 280.00,
            ],
            [
                'product_sku' => 'RX-INS-GLAR',
                'batch_no' => 'LOT-INS-COLD-01',
                'lot_no' => 'ZP202602',
                'mfg_date' => Carbon::now()->subMonths(2)->toDateString(),
                'exp_date' => Carbon::now()->addMonths(16)->toDateString(),
                'qty' => 50,
                'unit_cost' => 620.00,
                'unit_sell' => 950.00,
            ],
        ];

        foreach ($d5_items as $item) {
            $prod = $productModels[$item['product_sku']];
            $batch = ProductBatch::create([
                'product_id' => $prod->id,
                'delivery_id' => $del5->id,
                'batch_number' => $item['batch_no'],
                'lot_number' => $item['lot_no'],
                'manufacture_date' => $item['mfg_date'],
                'expiry_date' => $item['exp_date'],
                'quantity_received' => $item['qty'],
                'quantity_remaining' => $item['qty'],
                'unit_purchase_cost' => $item['unit_cost'],
                'unit_selling_price' => $item['unit_sell'],
                'status' => 'active',
            ]);

            DeliveryItem::create([
                'delivery_id' => $del5->id,
                'product_id' => $prod->id,
                'batch_id' => $batch->id,
                'batch_number' => $item['batch_no'],
                'expiry_date' => $item['exp_date'],
                'quantity' => $item['qty'],
                'unit_cost' => $item['unit_cost'],
                'subtotal' => $item['qty'] * $item['unit_cost'],
                'selling_price' => $item['unit_sell'],
            ]);

            StockMovement::create([
                'product_id' => $prod->id,
                'batch_id' => $batch->id,
                'movement_type' => 'delivery_in',
                'quantity' => $item['qty'],
                'balance_before' => 0,
                'balance_after' => $item['qty'],
                'reference_type' => Delivery::class,
                'reference_id' => $del5->id,
                'unit_cost' => $item['unit_cost'],
                'reason' => "Delivery received: {$del5->delivery_number}",
                'user_name' => $del5->received_by,
                'created_at' => Carbon::now()->subDays(2),
            ]);
        }

        // 6. Sample Sales / Dispensations (Financial Revenue & Profit Tracking)
        // Sale 1: Outpatient Prescription
        $sale1 = Sale::create([
            'sale_number' => 'DISP-2026-0001',
            'transaction_type' => 'dispensing',
            'patient_name' => 'Carmela Bautista',
            'patient_mrn' => 'MRN-2026-8812',
            'department_name' => 'Outpatient Pharmacy',
            'doctor_name' => 'Dr. Antonio Reyes, MD (Internal Med)',
            'prescription_no' => 'RX-99120',
            'sale_date' => Carbon::now()->subDays(10),
            'subtotal_cost' => 1240.00,
            'subtotal_amount' => 2480.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 2480.00,
            'total_profit' => 1240.00, // 50% margin
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'notes' => '7-day post-op discharge medications filled.',
            'dispensed_by' => 'Pharm. Angela Cruz, RPh',
        ]);

        $batchAmo = ProductBatch::where('batch_number', 'LOT-AMX-25A')->first();
        $batchPar = ProductBatch::where('batch_number', 'LOT-PAR-25C')->first();

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $batchAmo->product_id,
            'batch_id' => $batchAmo->id,
            'quantity' => 2,
            'unit_cost' => $batchAmo->unit_purchase_cost,
            'unit_price' => 650.00,
            'subtotal_cost' => 2 * $batchAmo->unit_purchase_cost,
            'subtotal_amount' => 1300.00,
            'profit' => 1300.00 - (2 * $batchAmo->unit_purchase_cost),
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $batchPar->product_id,
            'batch_id' => $batchPar->id,
            'quantity' => 2,
            'unit_cost' => $batchPar->unit_purchase_cost,
            'unit_price' => 400.00,
            'subtotal_cost' => 2 * $batchPar->unit_purchase_cost,
            'subtotal_amount' => 800.00,
            'profit' => 800.00 - (2 * $batchPar->unit_purchase_cost),
        ]);

        // Sale 2: ER Emergency & Consignment Diagnostics Dispensed
        $batchNS = ProductBatch::where('batch_number', 'LOT-NS-26A')->first();
        $batchStrip = ProductBatch::where('batch_number', 'LOT-ROC-STRIP-99')->first();
        $batchIVC = ProductBatch::where('batch_number', 'LOT-IVC-26B')->first();

        $sale2 = Sale::create([
            'sale_number' => 'DISP-2026-0002',
            'transaction_type' => 'dispensing',
            'patient_name' => 'Danilo Ramirez',
            'patient_mrn' => 'MRN-2026-9041',
            'department_name' => 'Emergency Room',
            'doctor_name' => 'Dr. Clarisse Santos, MD (ER Specialist)',
            'prescription_no' => 'ER-STAT-3401',
            'sale_date' => Carbon::now()->subDays(3),
            'subtotal_cost' => 3180.00,
            'subtotal_amount' => 5660.00,
            'discount_amount' => 200.00,
            'tax_amount' => 0.00,
            'total_amount' => 5460.00,
            'total_profit' => 2280.00,
            'payment_method' => 'philhealth',
            'payment_status' => 'paid',
            'notes' => 'Diabetic ketoacidosis admission - includes consignment Accu-Chek strips.',
            'dispensed_by' => 'Pharm. Angela Cruz, RPh',
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $batchNS->product_id,
            'batch_id' => $batchNS->id,
            'quantity' => 5,
            'unit_cost' => $batchNS->unit_purchase_cost,
            'unit_price' => 110.00,
            'subtotal_cost' => 5 * $batchNS->unit_purchase_cost,
            'subtotal_amount' => 550.00,
            'profit' => 550.00 - (5 * $batchNS->unit_purchase_cost),
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $batchStrip->product_id,
            'batch_id' => $batchStrip->id,
            'quantity' => 2,
            'unit_cost' => $batchStrip->unit_purchase_cost,
            'unit_price' => 1150.00,
            'subtotal_cost' => 2 * $batchStrip->unit_purchase_cost,
            'subtotal_amount' => 2300.00,
            'profit' => 2300.00 - (2 * $batchStrip->unit_purchase_cost),
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $batchIVC->product_id,
            'batch_id' => $batchIVC->id,
            'quantity' => 1,
            'unit_cost' => $batchIVC->unit_purchase_cost,
            'unit_price' => 2200.00,
            'subtotal_cost' => 1 * $batchIVC->unit_purchase_cost,
            'subtotal_amount' => 2200.00,
            'profit' => 2200.00 - (1 * $batchIVC->unit_purchase_cost),
        ]);

        // Sale 3: Operating Room Ward Department Issue
        $sale3 = Sale::create([
            'sale_number' => 'ISSUE-2026-0003',
            'transaction_type' => 'department_issue',
            'patient_name' => 'OR Suite 2 (Charge to OR Dept)',
            'patient_mrn' => 'DEPT-OR-02',
            'department_name' => 'Operating Room',
            'doctor_name' => 'Dr. Fernando Lopez, MD (Surgery)',
            'prescription_no' => 'OR-REQ-8820',
            'sale_date' => Carbon::now()->subDays(1),
            'subtotal_cost' => 4800.00,
            'subtotal_amount' => 7400.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 7400.00,
            'total_profit' => 2600.00,
            'payment_method' => 'department_charge',
            'payment_status' => 'paid',
            'notes' => 'Major surgical case stock issue.',
            'dispensed_by' => 'Pharm. Angela Cruz, RPh',
        ]);
    }
}
