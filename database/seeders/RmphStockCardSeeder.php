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
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RmphStockCardSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/rmph_stock_cards.json');
        if (!file_exists($jsonPath)) {
            $this->command->error("rmph_stock_cards.json not found.");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        if (!$data || empty($data['products'])) {
            $this->command->error("Invalid or empty stock card JSON data.");
            return;
        }

        // 1. Categories
        $categoriesMap = [];
        $catNames = [
            'Central Supply Room (CSR) Consignment' => 'medical_supply',
            'Consignment Medical Supplies' => 'medical_supply',
            'Dialysis Unit (HDU) Consignment' => 'medical_supply',
            'Hospital Pharmacy Stocks' => 'pharmacy',
        ];

        foreach ($catNames as $name => $type) {
            $cat = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'type' => $type,
                    'description' => "Capiz Provincial Government - Roxas Memorial Provincial Hospital ({$name})",
                ]
            );
            $categoriesMap[$name] = $cat->id;
        }
        $defaultCatId = reset($categoriesMap);

        // 2. Suppliers / Consignors
        $supplierModels = [];
        foreach ($data['suppliers'] as $s) {
            $sup = Supplier::where('name', $s['name'])->first();
            if (!$sup) {
                $count = Supplier::count() + 1;
                $sup = Supplier::create([
                    'name' => $s['name'],
                    'code' => 'SUP-RMPH-' . str_pad($count, 3, '0', STR_PAD_LEFT),
                    'payment_terms' => 'Consignment',
                    'status' => 'active',
                    'contact_person' => 'Hospital Consignment Representative',
                    'notes' => 'Official Consignor for Roxas Memorial Provincial Hospital Extensive Consignment System (HECS)',
                ]);
            }
            $supplierModels[$s['name']] = $sup;
        }

        // 3. Products
        $productModels = [];
        $createdProducts = 0;
        $updatedProducts = 0;

        foreach ($data['products'] as $p) {
            $sup = $supplierModels[$p['supplier']] ?? Supplier::where('name', $p['supplier'])->first();
            $catId = $defaultCatId;
            if (stripos($p['name'], 'dialyzer') !== false || stripos($p['name'], 'bloodline') !== false || stripos($p['name'], 'fistula') !== false) {
                $catId = $categoriesMap['Dialysis Unit (HDU) Consignment'] ?? $defaultCatId;
            }

            // Check if product exists by SKU or by Name
            $prod = Product::where('sku', $p['sku'])
                ->orWhere(function ($q) use ($p) {
                    $q->where('name', $p['name']);
                })->first();

            if (!$prod) {
                $prod = Product::create([
                    'category_id' => $catId,
                    'supplier_id' => $sup?->id,
                    'item_type' => 'medical_supply',
                    'sku' => $p['sku'],
                    'name' => $p['name'],
                    'generic_name' => $p['name'],
                    'brand_name' => $p['supplier'],
                    'unit_of_measure' => strtolower($p['unit'] ?: 'piece'),
                    'inventory_type' => 'consignment',
                    'min_stock_alert' => 50,
                    'max_stock_level' => 10000,
                    'default_purchase_price' => $p['cost'],
                    'selling_price' => $p['sellingPrice'],
                    'storage_condition' => 'room_temperature',
                    'description' => "General Form No. 18 Stock Card: {$p['sheetName']} • Markup: ₱" . number_format($p['markup'], 2),
                ]);
                $createdProducts++;
            } else {
                $prod->update([
                    'default_purchase_price' => $p['cost'] > 0 ? $p['cost'] : $prod->default_purchase_price,
                    'selling_price' => $p['sellingPrice'] > 0 ? $p['sellingPrice'] : $prod->selling_price,
                    'supplier_id' => $sup?->id ?: $prod->supplier_id,
                ]);
                $updatedProducts++;
            }

            $productModels[$p['sku']] = $prod;

            // 4. Inward Deliveries for this product
            foreach ($p['deliveries'] as $idx => $del) {
                $delSupplier = $supplierModels[$del['supplier']] ?? $sup;
                if (!$delSupplier) continue;

                $delDate = Carbon::parse($del['date'] ?: '2025-06-01');
                $delRef = $del['ref'] ?: "DEL-{$p['sku']}-" . str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
                $cost = $del['cost'] > 0 ? $del['cost'] : $prod->default_purchase_price;
                $amount = $del['qty'] * $cost;

                $delivery = Delivery::firstOrCreate(
                    ['delivery_number' => $delRef],
                    [
                        'invoice_number' => $delRef,
                        'supplier_id' => $delSupplier->id,
                        'delivery_date' => $delDate->toDateString(),
                        'received_by' => 'Central Supply Room (CSR) Custodian',
                        'status' => 'received',
                        'total_amount' => $amount,
                        'payment_status' => 'consignment_term',
                        'amount_paid' => 0.00,
                        'due_date' => $delDate->copy()->addDays(30)->toDateString(),
                        'notes' => "General Form 18 Inward Delivery: {$p['name']}",
                    ]
                );

                $batchNum = "LOT-{$delRef}-" . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                $expDate = $delDate->copy()->addMonths(24)->toDateString();

                $batch = ProductBatch::firstOrCreate(
                    [
                        'product_id' => $prod->id,
                        'batch_number' => $batchNum,
                    ],
                    [
                        'delivery_id' => $delivery->id,
                        'lot_number' => 'STANDARD',
                        'manufacture_date' => $delDate->copy()->subMonths(3)->toDateString(),
                        'expiry_date' => $expDate,
                        'quantity_received' => $del['qty'],
                        'quantity_remaining' => $del['qty'],
                        'unit_purchase_cost' => $cost,
                        'unit_selling_price' => $prod->selling_price,
                        'status' => 'active',
                        'notes' => "General Form 18 Batch for {$p['name']}",
                    ]
                );

                DeliveryItem::firstOrCreate(
                    [
                        'delivery_id' => $delivery->id,
                        'product_id' => $prod->id,
                        'batch_number' => $batchNum,
                    ],
                    [
                        'batch_id' => $batch->id,
                        'brand' => $delSupplier->name,
                        'expiry_date' => $expDate,
                        'quantity' => $del['qty'],
                        'unit_cost' => $cost,
                        'subtotal' => $amount,
                        'selling_price' => $prod->selling_price,
                        'notes' => "Stock Card Delivery Entry",
                    ]
                );
            }

            // 5. Issuances / Sales for this product
            foreach ($p['issuances'] as $idx => $iss) {
                $issDate = Carbon::parse($iss['date'] ?: '2025-07-01');
                $issRef = $iss['ref'] ?: "ISS-{$p['sku']}-" . str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
                $cost = $iss['cost'] > 0 ? $iss['cost'] : $prod->default_purchase_price;
                $subtotalCost = $iss['qty'] * $cost;
                $unitPrice = $prod->selling_price;
                $subtotalAmount = $iss['qty'] * $unitPrice;
                $profit = $subtotalAmount - $subtotalCost;

                $sale = Sale::firstOrCreate(
                    ['sale_number' => $issRef],
                    [
                        'transaction_type' => 'department_issue',
                        'patient_name' => $iss['department'] ?: 'Central Supply Room (CSR)',
                        'patient_mrn' => $issRef,
                        'department_name' => $iss['department'] ?: 'Central Supply Room (CSR)',
                        'sale_date' => $issDate->toDateTimeString(),
                        'subtotal_cost' => $subtotalCost,
                        'subtotal_amount' => $subtotalAmount,
                        'discount_amount' => 0.00,
                        'tax_amount' => 0.00,
                        'total_amount' => $subtotalAmount,
                        'total_profit' => $profit,
                        'payment_method' => 'consignment_settled',
                        'payment_status' => 'paid',
                        'dispensed_by' => 'Aliana Marie A. Dula (Admin Aide III)',
                        'notes' => "GF-18 Department Issuance: {$iss['department']}",
                    ]
                );

                $batch = ProductBatch::where('product_id', $prod->id)
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('expiry_date')
                    ->first();

                SaleItem::firstOrCreate(
                    [
                        'sale_id' => $sale->id,
                        'product_id' => $prod->id,
                        'quantity' => $iss['qty'],
                    ],
                    [
                        'batch_id' => $batch?->id,
                        'unit_cost' => $cost,
                        'unit_price' => $unitPrice,
                        'subtotal_cost' => $subtotalCost,
                        'subtotal_amount' => $subtotalAmount,
                        'profit' => $profit,
                    ]
                );

                if ($batch && $batch->quantity_remaining >= $iss['qty']) {
                    $batch->decrement('quantity_remaining', $iss['qty']);
                }
            }
        }

        $this->command->info("RmphStockCardSeeder completed! Created: {$createdProducts}, Updated: {$updatedProducts}");
    }
}
