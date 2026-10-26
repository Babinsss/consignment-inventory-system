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

class RmphConsignmentSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/rmph_master.json');
        if (!file_exists($jsonPath)) {
            $this->command->error("rmph_master.json not found.");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        // 1. Ensure Categories
        $categoriesMap = [];
        $catNames = [
            'Central Supply Room (CSR) Consignment' => 'medical_supply',
            'Dialysis Unit (HDU) Consignment' => 'medical_supply',
            'Consignment Medical Supplies' => 'medical_supply',
            'CSR Consignment Supplies' => 'medical_supply',
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
                    'notes' => "Official Consignor for Roxas Memorial Provincial Hospital Extensive Consignment System (HECS)",
                ]);
            }
            $supplierModels[$s['name']] = $sup;
        }

        // 3. Products (Stock Registry)
        $productModels = [];
        foreach ($data['products'] as $p) {
            $sup = $supplierModels[$p['consignor']] ?? null;
            $catId = $categoriesMap[$p['category']] ?? reset($categoriesMap);

            $prod = Product::firstOrCreate(
                ['sku' => $p['code']],
                [
                    'category_id' => $catId,
                    'supplier_id' => $sup ? $sup->id : null,
                    'item_type' => 'medical_supply',
                    'name' => $p['name'],
                    'generic_name' => $p['name'],
                    'brand_name' => $p['consignor'],
                    'unit_of_measure' => 'piece',
                    'inventory_type' => 'consignment',
                    'min_stock_alert' => 50,
                    'max_stock_level' => 5000,
                    'default_purchase_price' => $p['cost'],
                    'selling_price' => $p['sell'],
                    'storage_condition' => 'room_temperature',
                    'description' => "Consignor: {$p['consignor']} • Markup: ₱{$p['markup']}",
                ]
            );
            $productModels[$p['code']] = $prod;
        }

        // 4. Stock Deliveries (SPR / CO Deliveries)
        $deliveriesGrouped = [];
        foreach ($data['deliveries'] as $d) {
            $deliveriesGrouped[$d['ref']][] = $d;
        }

        $batchModels = [];

        foreach ($deliveriesGrouped as $ref => $items) {
            $first = $items[0];
            $sup = $supplierModels[$first['consignor']] ?? null;
            if (!$sup) continue;

            $totalAmount = array_sum(array_column($items, 'amount'));
            $delDate = Carbon::parse($first['date']);

            $delivery = Delivery::firstOrCreate(
                ['delivery_number' => $ref],
                [
                    'invoice_number' => $ref,
                    'supplier_id' => $sup->id,
                    'delivery_date' => $delDate->toDateString(),
                    'received_by' => 'Central Supply Room (CSR) Receiving Custodian',
                    'status' => 'received',
                    'total_amount' => $totalAmount,
                    'payment_status' => 'consignment_term',
                    'amount_paid' => 0.00,
                    'due_date' => $delDate->copy()->addDays(30)->toDateString(),
                    'notes' => "Official Consignment Delivery SPR/CO: {$ref} for Roxas Memorial Provincial Hospital",
                ]
            );

            foreach ($items as $idx => $it) {
                $prod = $productModels[$it['itemCode']] ?? null;
                if (!$prod) continue;

                $batchNum = "LOT-{$ref}-" . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                $expDate = $delDate->copy()->addMonths(24)->toDateString();

                $batch = ProductBatch::firstOrCreate(
                    [
                        'product_id' => $prod->id,
                        'batch_number' => $batchNum,
                    ],
                    [
                        'delivery_id' => $delivery->id,
                        'lot_number' => $it['brand'] ?: 'STANDARD',
                        'manufacture_date' => $delDate->copy()->subMonths(3)->toDateString(),
                        'expiry_date' => $expDate,
                        'quantity_received' => $it['qty'],
                        'quantity_remaining' => $it['qty'],
                        'unit_purchase_cost' => $it['cost'],
                        'unit_selling_price' => $prod->selling_price,
                        'status' => 'active',
                        'notes' => "Brand: {$it['brand']} • SPR/CO Ref: {$ref}",
                    ]
                );

                $batchModels[$it['itemCode']][] = $batch;

                DeliveryItem::firstOrCreate(
                    [
                        'delivery_id' => $delivery->id,
                        'product_id' => $prod->id,
                        'batch_number' => $batchNum,
                    ],
                    [
                        'batch_id' => $batch->id,
                        'brand' => $it['brand'],
                        'expiry_date' => $expDate,
                        'quantity' => $it['qty'],
                        'unit_cost' => $it['cost'],
                        'subtotal' => $it['amount'],
                        'selling_price' => $prod->selling_price,
                        'notes' => "Unit: {$it['uom']}",
                    ]
                );
            }
        }

        // For products that don't have a delivery batch yet, ensure an active batch exists
        foreach ($productModels as $code => $prod) {
            if (!isset($batchModels[$code]) || empty($batchModels[$code])) {
                $batchNum = "LOT-CONSIGN-{$code}";
                $batch = ProductBatch::firstOrCreate(
                    [
                        'product_id' => $prod->id,
                        'batch_number' => $batchNum,
                    ],
                    [
                        'lot_number' => 'OPENING-STOCK',
                        'manufacture_date' => Carbon::now()->subMonths(6)->toDateString(),
                        'expiry_date' => Carbon::now()->addMonths(18)->toDateString(),
                        'quantity_received' => 5000,
                        'quantity_remaining' => 5000,
                        'unit_purchase_cost' => $prod->default_purchase_price,
                        'unit_selling_price' => $prod->selling_price,
                        'status' => 'active',
                        'notes' => "Opening Consignment Inventory Stock Card Balance",
                    ]
                );
                $batchModels[$code][] = $batch;
            }
        }

        // 5. Daily Issuance Transactions (Charges & Cash)
        $txGrouped = [];
        foreach ($data['transactions'] as $tx) {
            $key = $tx['date'] . '|' . ($tx['patient'] ?: 'WALKIN') . '|' . ($tx['refCode'] ?: 'STAT');
            $txGrouped[$key][] = $tx;
        }

        $saleCounter = 1;
        foreach ($txGrouped as $key => $txList) {
            $first = $txList[0];
            $saleDate = Carbon::parse($first['date']);
            $saleNumber = sprintf('CSG-%s-%05d', $saleDate->format('ym'), $saleCounter++);

            $totalGross = 0;
            $totalCost = 0;
            $totalDiscount = 0;

            foreach ($txList as $t) {
                $totalGross += $t['totalSales'];
                $totalCost += $t['totalConsignorPrice'];
                $totalDiscount += $t['discount'];
            }

            $netTotal = max(0, $totalGross - $totalDiscount);
            $profit = $netTotal - $totalCost;

            $sale = Sale::firstOrCreate(
                ['sale_number' => $saleNumber],
                [
                    'transaction_type' => strtolower($first['type']) === 'cash' ? 'walk_in_sale' : 'department_issue',
                    'patient_name' => $first['patient'],
                    'patient_mrn' => $first['refCode'] ?: null,
                    'department_name' => 'Central Supply Room / HDU',
                    'prescription_no' => $first['refCode'] ?: null,
                    'or_number' => $first['orNumber'] ?: null,
                    'charge_slip_no' => $first['refCode'] ?: null,
                    'sale_date' => $saleDate->toDateTimeString(),
                    'subtotal_cost' => $totalCost,
                    'subtotal_amount' => $totalGross,
                    'discount_amount' => $totalDiscount,
                    'tax_amount' => 0.00,
                    'total_amount' => $netTotal,
                    'total_profit' => $profit,
                    'payment_method' => strtolower($first['type']) === 'cash' ? 'cash' : 'consignment_settled',
                    'payment_status' => 'paid',
                    'dispensed_by' => 'Aliana Marie A. Dula (Admin Aide III)',
                    'notes' => "Hospital Extensive Consignment System - Capiz Provincial Government",
                ]
            );

            foreach ($txList as $t) {
                $prod = $productModels[$t['itemCode']] ?? null;
                if (!$prod) continue;

                $batch = isset($batchModels[$t['itemCode']]) ? $batchModels[$t['itemCode']][0] : null;

                SaleItem::firstOrCreate(
                    [
                        'sale_id' => $sale->id,
                        'product_id' => $prod->id,
                        'unit_price' => $t['price'],
                        'quantity' => $t['qty'],
                    ],
                    [
                        'batch_id' => $batch ? $batch->id : null,
                        'qty_returned' => $t['qtyReturned'],
                        'unit_cost' => $t['cost'],
                        'subtotal_cost' => $t['totalConsignorPrice'],
                        'subtotal_amount' => $t['totalSales'],
                        'profit' => $t['totalSales'] - $t['totalConsignorPrice'],
                    ]
                );

                if ($batch && $batch->quantity_remaining >= $t['qty']) {
                    $batch->decrement('quantity_remaining', $t['qty']);
                }
            }
        }
    }
}
