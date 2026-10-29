<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HospitalConsignmentController extends Controller
{
    /**
     * Statement of Consumed Goods (StCG)
     * Matches Capiz Provincial Government - Roxas Memorial Provincial Hospital template
     */
    public function stcg(Request $request)
    {
        $consignors = Supplier::where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedConsignorId = $request->input('consignor_id', $consignors->first()?->id);
        $selectedConsignor = Supplier::find($selectedConsignorId);

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Fetch items dispensed for this consignor in the period
        $query = SaleItem::whereHas('product', function ($q) use ($selectedConsignorId) {
            if ($selectedConsignorId) {
                $q->where('supplier_id', $selectedConsignorId);
            }
        })->whereHas('sale', function ($q) use ($month, $year) {
            if ($month) {
                $q->whereMonth('sale_date', $month);
            }
            if ($year) {
                $q->whereYear('sale_date', $year);
            }
        })->with(['product', 'sale']);

        $saleItems = $query->get();

        // Group by product to calculate Cash vs Charge breakdown
        $grouped = $saleItems->groupBy('product_id')->map(function ($items) {
            $product = $items->first()->product;
            $cashItems = $items->filter(fn($i) => strtolower($i->sale->transaction_type) === 'walk_in_sale' || strtolower($i->sale->payment_method) === 'cash');
            $chargeItems = $items->filter(fn($i) => strtolower($i->sale->transaction_type) !== 'walk_in_sale' && strtolower($i->sale->payment_method) !== 'cash');

            $cashQty = $cashItems->sum('quantity');
            $cashAmount = $cashItems->sum('subtotal_cost');

            $chargeQty = $chargeItems->sum('quantity');
            $chargeAmount = $chargeItems->sum('subtotal_cost');

            $totalQty = $items->sum('quantity');
            $totalConsignorAmount = $items->sum('subtotal_cost');
            $totalHospitalSales = $items->sum('subtotal_amount');
            $hospitalProfit = $totalHospitalSales - $totalConsignorAmount;

            return [
                'item_code' => $product->sku,
                'name' => $product->name,
                'unit' => $product->unit_of_measure,
                'consignor_price' => $product->default_purchase_price,
                'cash_qty' => $cashQty,
                'cash_amount' => $cashAmount,
                'charge_qty' => $chargeQty,
                'charge_amount' => $chargeAmount,
                'total_qty' => $totalQty,
                'total_consignor_amount' => $totalConsignorAmount,
                'total_hospital_sales' => $totalHospitalSales,
                'hospital_profit' => $hospitalProfit,
            ];
        })->values();

        $grandConsignorTotal = $grouped->sum('total_consignor_amount');
        $grandHospitalSales = $grouped->sum('total_hospital_sales');
        $grandHospitalProfit = $grouped->sum('hospital_profit');

        $periodLabel = Carbon::createFromDate($year, $month, 1)->format('F Y');

        return view('hospital.stcg', compact(
            'consignors',
            'selectedConsignor',
            'month',
            'year',
            'periodLabel',
            'grouped',
            'grandConsignorTotal',
            'grandHospitalSales',
            'grandHospitalProfit'
        ));
    }

    /**
     * Schedule of Consumed Goods (ScCG)
     * Itemized patient list with dates, quantities, OR/Charge Slip #
     */
    public function sccg(Request $request)
    {
        $consignors = Supplier::where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedConsignorId = $request->input('consignor_id', $consignors->first()?->id);
        $selectedConsignor = Supplier::find($selectedConsignorId);

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $typeFilter = $request->input('type', 'ALL'); // CHARGE, CASH, ALL

        $query = SaleItem::whereHas('product', function ($q) use ($selectedConsignorId) {
            if ($selectedConsignorId) {
                $q->where('supplier_id', $selectedConsignorId);
            }
        })->whereHas('sale', function ($q) use ($month, $year, $typeFilter) {
            if ($month) $q->whereMonth('sale_date', $month);
            if ($year) $q->whereYear('sale_date', $year);
            if ($typeFilter === 'CASH') {
                $q->where(fn($sq) => $sq->where('transaction_type', 'walk_in_sale')->orWhere('payment_method', 'cash'));
            } elseif ($typeFilter === 'CHARGE') {
                $q->where(fn($sq) => $sq->where('transaction_type', '!=', 'walk_in_sale')->where('payment_method', '!=', 'cash'));
            }
        })->with(['product', 'sale'])->latest();

        $items = $query->paginate(50)->withQueryString();

        $periodLabel = Carbon::createFromDate($year, $month, 1)->format('F Y');

        // Summary totals
        $totalIssuedQty = $query->sum('quantity');
        $totalSalesAmount = $query->sum('subtotal_amount');
        $totalConsignorAmount = $query->sum('subtotal_cost');

        return view('hospital.sccg', compact(
            'consignors',
            'selectedConsignor',
            'month',
            'year',
            'typeFilter',
            'periodLabel',
            'items',
            'totalIssuedQty',
            'totalSalesAmount',
            'totalConsignorAmount'
        ));
    }

    /**
     * Government Stock Card (General Form No. 18, Revised Jan 1992)
     */
    public function stockCard(Request $request)
    {
        $consignors = Supplier::whereHas('products', fn($q) => $q->where('inventory_type', 'consignment'))
            ->withCount(['products' => fn($q) => $q->where('inventory_type', 'consignment')])
            ->orderBy('name')
            ->get();

        $selectedConsignorId = $request->input('consignor_id');

        $productsQuery = Product::where('inventory_type', 'consignment')
            ->with('supplier')
            ->orderBy('name');

        if ($selectedConsignorId) {
            $productsQuery->where('supplier_id', $selectedConsignorId);
        }

        $products = $productsQuery->get();

        $selectedProductId = $request->input('product_id');
        if (!$selectedProductId || !$products->contains('id', $selectedProductId)) {
            $selectedProductId = $products->first()?->id;
        }

        $selectedProduct = Product::with(['supplier', 'batches', 'stockMovements'])->find($selectedProductId);

        // Previous and Next Product ID for rapid card flipping
        $currentIndex = $products->search(fn($p) => $p->id == $selectedProductId);
        $prevProductId = ($currentIndex !== false && $currentIndex > 0) ? $products[$currentIndex - 1]->id : null;
        $nextProductId = ($currentIndex !== false && $currentIndex < $products->count() - 1) ? $products[$currentIndex + 1]->id : null;

        // Lightweight JSON array for Alpine.js instant search & filtering
        $productsJson = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'supplier_id' => $p->supplier_id,
                'supplier_name' => $p->supplier?->name ?? 'Consignor',
                'cost' => (float) $p->default_purchase_price,
                'price' => (float) $p->selling_price,
                'unit' => $p->unit_of_measure,
            ];
        })->values();

        // Build running balance card ledger
        $ledger = [];
        if ($selectedProduct) {
            // Deliveries received
            $delItems = DB::table('delivery_items')
                ->join('deliveries', 'deliveries.id', '=', 'delivery_items.delivery_id')
                ->where('delivery_items.product_id', $selectedProduct->id)
                ->select([
                    'deliveries.delivery_date as date',
                    'deliveries.delivery_number as reference',
                    DB::raw('"MHJ/Supplier Inward Delivery" as party'),
                    'delivery_items.quantity as rec_qty',
                    'delivery_items.unit_cost as rec_cost',
                    'delivery_items.subtotal as rec_total',
                    DB::raw('0 as iss_qty'),
                    DB::raw('0 as iss_cost'),
                    DB::raw('0 as iss_total'),
                ])->get();

            // Dispensations issued
            $saleItems = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sale_items.product_id', $selectedProduct->id)
                ->select([
                    'sales.sale_date as date',
                    'sales.sale_number as reference',
                    DB::raw('COALESCE(sales.patient_name, "CSR / Ward Issue") as party'),
                    DB::raw('0 as rec_qty'),
                    DB::raw('0 as rec_cost'),
                    DB::raw('0 as rec_total'),
                    'sale_items.quantity as iss_qty',
                    'sale_items.unit_cost as iss_cost',
                    'sale_items.subtotal_cost as iss_total',
                ])->get();

            // Department Requisitions Issued (Monitor Who Requested)
            $deptReqs = DB::table('department_requests')
                ->where('product_id', $selectedProduct->id)
                ->where('status', 'Approved')
                ->select([
                    DB::raw('COALESCE(issued_at, created_at) as date'),
                    'batch_id as reference',
                    'department_name',
                    'requested_by',
                    DB::raw('0 as rec_qty'),
                    DB::raw('0 as rec_cost'),
                    DB::raw('0 as rec_total'),
                    DB::raw('(CASE WHEN quantity_issued > 0 THEN quantity_issued ELSE quantity END) as iss_qty'),
                    DB::raw('0 as iss_cost'),
                    DB::raw('0 as iss_total'),
                ])->get()->map(function ($row) {
                    $row->party = $row->department_name . ' (Req: ' . $row->requested_by . ')';
                    return $row;
                });

            $merged = $delItems->concat($saleItems)->concat($deptReqs)->sortBy('date')->values();

            $runningBalanceQty = 0;
            $unitCost = $selectedProduct->default_purchase_price;

            foreach ($merged as $row) {
                if ($row->rec_qty > 0) {
                    $runningBalanceQty += $row->rec_qty;
                    $unitCost = $row->rec_cost > 0 ? $row->rec_cost : $unitCost;
                }
                if ($row->iss_qty > 0) {
                    $runningBalanceQty -= $row->iss_qty;
                }

                $ledger[] = [
                    'date' => Carbon::parse($row->date)->format('Y-m-d'),
                    'reference' => $row->reference,
                    'party' => $row->party,
                    'rec_qty' => $row->rec_qty > 0 ? $row->rec_qty : '',
                    'rec_cost' => $row->rec_cost > 0 ? number_format($row->rec_cost, 2) : '',
                    'rec_total' => $row->rec_total > 0 ? number_format($row->rec_total, 2) : '',
                    'iss_qty' => $row->iss_qty > 0 ? $row->iss_qty : '',
                    'iss_cost' => $row->iss_cost > 0 ? number_format($row->iss_cost, 2) : '',
                    'iss_total' => $row->iss_total > 0 ? number_format($row->iss_total, 2) : '',
                    'bal_qty' => max(0, $runningBalanceQty),
                    'bal_cost' => number_format($unitCost, 2),
                    'bal_total' => number_format(max(0, $runningBalanceQty) * $unitCost, 2),
                ];
            }
        }

        return view('hospital.stock-card', compact(
            'consignors',
            'selectedConsignorId',
            'products',
            'productsJson',
            'selectedProduct',
            'ledger',
            'prevProductId',
            'nextProductId'
        ));
    }

    /**
     * Monthly Consignment Inventory Report
     * 13 columns: Beg. Bal, Receipts, Pull Out, Issuance, Returns, Ending Balance, Physical Count, Sales, Collectibles
     */
    public function monthlyInventory(Request $request)
    {
        $consignors = Supplier::where('status', 'active')->orderBy('name')->get();
        $selectedConsignorId = $request->input('consignor_id', $consignors->first()?->id);
        $selectedConsignor = Supplier::find($selectedConsignorId);

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $products = Product::where('supplier_id', $selectedConsignorId)
            ->with(['batches'])
            ->get();

        $report = $products->map(function ($p) use ($month, $year) {
            $receipts = DB::table('delivery_items')
                ->join('deliveries', 'deliveries.id', '=', 'delivery_items.delivery_id')
                ->where('delivery_items.product_id', $p->id)
                ->whereMonth('deliveries.delivery_date', $month)
                ->whereYear('deliveries.delivery_date', $year)
                ->sum('delivery_items.quantity');

            $issuance = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sale_items.product_id', $p->id)
                ->whereMonth('sales.sale_date', $month)
                ->whereYear('sales.sale_date', $year)
                ->sum('sale_items.quantity');

            $endingBal = $p->total_stock;
            $begBal = max(0, $endingBal + $issuance - $receipts);
            $totalSales = $issuance * $p->selling_price;
            $totalCollectibles = $issuance * $p->default_purchase_price;

            return [
                'code' => $p->sku,
                'name' => $p->name,
                'consignor' => $p->supplier?->name,
                'cost' => $p->default_purchase_price,
                'beg_bal' => $begBal,
                'receipts' => $receipts,
                'pull_out' => 0,
                'issuance' => $issuance,
                'returns' => 0,
                'ending_bal' => $endingBal,
                'physical_count' => $endingBal,
                'total_sales' => $totalSales,
                'collectibles' => $totalCollectibles,
            ];
        });

        $periodLabel = Carbon::createFromDate($year, $month, 1)->format('F Y');

        return view('hospital.monthly-inventory', compact(
            'consignors',
            'selectedConsignor',
            'month',
            'year',
            'periodLabel',
            'report'
        ));
    }

    /**
     * Show Excel Import Hub for Hospital Consignment Staff
     */
    public function showImport()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_consignors' => Supplier::where('payment_terms', 'Consignment')->count() ?: Supplier::count(),
            'total_deliveries' => \App\Models\Delivery::count(),
            'total_batches' => ProductBatch::where('quantity_remaining', '>', 0)->count(),
            'total_sales' => Sale::count(),
            'recent_deliveries' => \App\Models\Delivery::with('supplier')->latest()->take(5)->get(),
        ];

        return view('hospital.import', compact('stats'));
    }

    /**
     * Process Uploaded Excel / CSV file
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|max:51200',
            'import_type' => 'required|in:auto,registry,deliveries,transactions',
        ]);

        $file = $request->file('excel_file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv', 'txt'])) {
            return back()->with('error', 'Invalid file type. Please upload an Excel (.xlsx, .xls) or CSV (.csv) file.');
        }

        $importDir = storage_path('app/imports');
        if (!file_exists($importDir)) {
            mkdir($importDir, 0777, true);
        }

        $filename = 'upload_' . time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;
        $targetPath = $importDir . DIRECTORY_SEPARATOR . $filename;
        $file->move($importDir, $filename);

        $outputPath = $importDir . DIRECTORY_SEPARATOR . 'result_' . time() . '_' . \Illuminate\Support\Str::random(8) . '.json';
        $scriptPath = base_path('database/scripts/import_excel_engine.cjs');

        $importType = $request->input('import_type');
        $cmd = sprintf('node "%s" --file "%s" --type "%s" --output "%s"', $scriptPath, $targetPath, $importType, $outputPath);

        exec($cmd, $outputLines, $returnVar);

        if ($returnVar !== 0 || !file_exists($outputPath)) {
            @unlink($targetPath);
            return back()->with('error', 'Failed to parse Excel file. Please ensure the file is not password protected and matches standard HECS formats.');
        }

        $parsed = json_decode(file_get_contents($outputPath), true);
        @unlink($outputPath);
        @unlink($targetPath);

        if (!$parsed || empty($parsed['success'])) {
            $err = $parsed['error'] ?? 'Unknown parsing error occurred.';
            return back()->with('error', 'Error in Excel structure: ' . $err);
        }

        $data = $parsed['data'];
        $counts = $parsed['counts'];

        // If Dry Run requested
        if ($request->boolean('dry_run')) {
            return back()->with('success', sprintf(
                '🧪 [DRY RUN VALIDATION PASSED] File was analyzed successfully with zero database modifications. Detected: %d Consignor(s), %d Product(s), %d Stock Delivery Record(s), %d Daily Transaction(s).',
                $counts['suppliers'],
                $counts['products'],
                $counts['deliveries'],
                $counts['transactions']
            ))->with('import_summary', [
                'dry_run' => true,
                'counts' => $counts,
                'sample_products' => array_slice($data['products'], 0, 5),
                'sample_deliveries' => array_slice($data['deliveries'], 0, 5),
                'sample_transactions' => array_slice($data['transactions'], 0, 5),
            ]);
        }

        // Real Database Ingestion
        DB::beginTransaction();
        try {
            // 1. Categories
            $categoriesMap = [];
            $catNames = [
                'Central Supply Room (CSR) Consignment' => 'medical_supply',
                'Dialysis Unit (HDU) Consignment' => 'medical_supply',
                'Consignment Medical Supplies' => 'medical_supply',
                'CSR Consignment Supplies' => 'medical_supply',
                'Hospital Pharmacy Stocks' => 'pharmacy',
            ];

            foreach ($catNames as $name => $type) {
                $cat = \App\Models\Category::firstOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($name)],
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
            $createdSuppliers = 0;
            foreach ($data['suppliers'] as $s) {
                $sup = Supplier::where('name', $s['name'])->first();
                if (!$sup) {
                    $supCount = Supplier::count() + 1;
                    $sup = Supplier::create([
                        'name' => $s['name'],
                        'code' => 'SUP-RMPH-' . str_pad($supCount, 3, '0', STR_PAD_LEFT),
                        'payment_terms' => 'Consignment',
                        'status' => 'active',
                        'contact_person' => 'Hospital Consignment Representative',
                        'notes' => 'Official Consignor for Roxas Memorial Provincial Hospital Extensive Consignment System (HECS)',
                    ]);
                    $createdSuppliers++;
                }
                $supplierModels[$s['name']] = $sup;
            }

            // 3. Products
            $productModels = [];
            $createdProducts = 0;
            $updatedProducts = 0;
            foreach ($data['products'] as $p) {
                $sup = $supplierModels[$p['consignor']] ?? Supplier::where('name', $p['consignor'])->first();
                $catId = $categoriesMap[$p['category'] ?? ''] ?? reset($categoriesMap);

                $prod = Product::where('sku', $p['code'])->first();
                if (!$prod) {
                    $prod = Product::create([
                        'category_id' => $catId,
                        'supplier_id' => $sup?->id,
                        'item_type' => 'medical_supply',
                        'sku' => $p['code'],
                        'name' => $p['name'],
                        'generic_name' => $p['name'],
                        'brand_name' => $p['consignor'] ?? 'RMPH Consignment',
                        'unit_of_measure' => $p['unit'] ?? 'piece',
                        'inventory_type' => 'consignment',
                        'min_stock_alert' => 50,
                        'max_stock_level' => 5000,
                        'default_purchase_price' => $p['cost'],
                        'selling_price' => $p['sell'],
                        'storage_condition' => 'room_temperature',
                        'description' => "Consignor: {$p['consignor']} • Hospital Markup: ₱" . number_format($p['markup'], 2),
                    ]);
                    $createdProducts++;
                } else {
                    $prod->update([
                        'name' => $p['name'],
                        'default_purchase_price' => $p['cost'] > 0 ? $p['cost'] : $prod->default_purchase_price,
                        'selling_price' => $p['sell'] > 0 ? $p['sell'] : $prod->selling_price,
                        'supplier_id' => $sup?->id ?: $prod->supplier_id,
                    ]);
                    $updatedProducts++;
                }
                $productModels[$p['code']] = $prod;
            }

            // 4. Stock Deliveries (SPR / CO)
            $createdDeliveries = 0;
            $createdBatches = 0;
            $deliveriesGrouped = [];
            foreach ($data['deliveries'] as $d) {
                $deliveriesGrouped[$d['ref']][] = $d;
            }

            $batchModels = [];
            foreach ($deliveriesGrouped as $ref => $items) {
                $first = $items[0];
                $sup = $supplierModels[$first['consignor']] ?? Supplier::where('name', $first['consignor'])->first();
                if (!$sup) continue;

                $totalAmount = array_sum(array_column($items, 'amount'));
                $delDate = Carbon::parse($first['date']);

                $delivery = \App\Models\Delivery::firstOrCreate(
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
                $createdDeliveries++;

                foreach ($items as $idx => $it) {
                    $prod = $productModels[$it['itemCode']] ?? Product::where('sku', $it['itemCode'])->first();
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
                    $createdBatches++;

                    $batchModels[$it['itemCode']][] = $batch;

                    \App\Models\DeliveryItem::firstOrCreate(
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
                            'notes' => "Unit: {$it['unit']}",
                        ]
                    );

                    // Stock movement audit
                    StockMovement::create([
                        'product_id' => $prod->id,
                        'product_batch_id' => $batch->id,
                        'movement_type' => 'delivery_in',
                        'quantity' => $it['qty'],
                        'unit_cost' => $it['cost'],
                        'reference_type' => 'App\Models\Delivery',
                        'reference_id' => $delivery->id,
                        'notes' => "Excel Import SPR/CO: {$ref}",
                    ]);
                }
            }

            // 5. Daily Transactions
            $createdSales = 0;
            $txGrouped = [];
            foreach ($data['transactions'] as $tx) {
                $key = $tx['date'] . '|' . ($tx['patient'] ?: 'WALKIN') . '|' . ($tx['refCode'] ?: 'STAT');
                $txGrouped[$key][] = $tx;
            }

            $existingSalesCount = Sale::count();
            $saleCounter = $existingSalesCount + 1;

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
                        'notes' => 'Imported via HECS Excel Importer',
                    ]
                );
                $createdSales++;

                foreach ($txList as $t) {
                    $prod = $productModels[$t['itemCode']] ?? Product::where('sku', $t['itemCode'])->first();
                    if (!$prod) continue;

                    $batch = ProductBatch::where('product_id', $prod->id)->where('quantity_remaining', '>', 0)->orderBy('expiry_date')->first();

                    SaleItem::firstOrCreate(
                        [
                            'sale_id' => $sale->id,
                            'product_id' => $prod->id,
                            'unit_price' => $t['price'],
                            'quantity' => $t['qty'],
                        ],
                        [
                            'batch_id' => $batch?->id,
                            'qty_returned' => $t['qtyReturned'] ?? 0,
                            'unit_cost' => $t['cost'],
                            'subtotal_cost' => $t['totalConsignorPrice'],
                            'subtotal_amount' => $t['totalSales'],
                            'profit' => $t['totalSales'] - $t['totalConsignorPrice'],
                        ]
                    );

                    if ($batch && $batch->quantity_remaining >= $t['qty']) {
                        $batch->decrement('quantity_remaining', $t['qty']);
                    }

                    // Stock movement audit
                    StockMovement::create([
                        'product_id' => $prod->id,
                        'product_batch_id' => $batch?->id,
                        'movement_type' => 'sale_out',
                        'quantity' => $t['qty'],
                        'unit_cost' => $t['cost'],
                        'reference_type' => 'App\Models\Sale',
                        'reference_id' => $sale->id,
                        'notes' => "Dispensed to {$first['patient']} (Ref: {$first['refCode']})",
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', sprintf(
                '✅ Excel Import Successful! %d Consignor(s) registered, %d Product(s) created (%d updated), %d Delivery SPR/CO(s) logged with %d Batches, and %d Issuance Transaction(s) logged.',
                $createdSuppliers,
                $createdProducts,
                $updatedProducts,
                $createdDeliveries,
                $createdBatches,
                $createdSales
            ))->with('import_summary', [
                'dry_run' => false,
                'created_suppliers' => $createdSuppliers,
                'created_products' => $createdProducts,
                'updated_products' => $updatedProducts,
                'created_deliveries' => $createdDeliveries,
                'created_batches' => $createdBatches,
                'created_sales' => $createdSales,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Database import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV Templates for Consignment Staff
     */
    public function downloadTemplate(string $type)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-store, no-cache',
        ];

        if ($type === 'registry') {
            $filename = 'template_hecs_stock_registry.csv';
            $content = "ItemCode,Item Name,Name of Consignor,CONSIGNOR'S UNIT PRICE,MARK UP,SELLING PRICE/UNIT,Unit of Issue\r\n";
            $content .= "HECSM-RMH-CU01,SURGICAL GLOVES STERILE SIZE 7,MHJ MEDICAL AND LABORATORY SUPPLY,20.00,6.00,26.00,PC\r\n";
            $content .= "HECSM-RMH-CU02,SURGICAL GLOVES STERILE SIZE 7.5,MHJ MEDICAL AND LABORATORY SUPPLY,20.00,6.00,26.00,PC\r\n";
            $content .= "HECSM-HDU-00001,Hemodialysis Blood Tubing Set with 2TP,MHJ MEDICAL AND LABORATORY SUPPLY,250.00,2500.00,2750.00,PC\r\n";
            $content .= "HECSM-RMH-CSR02,CBG PACK,DOUBLE-Y PHARMA MEDICAL AND DENTAL SUPPLIES TRADING,13.00,87.00,100.00,PACK\r\n";
        } elseif ($type === 'deliveries') {
            $filename = 'template_hecs_stock_deliveries.csv';
            $content = "SPR/CO No:,Date of Delivery,ItemCode,Item Name and Description,Name of Consignor,Unit of Issue,Brand,Qty Delivered,Consignor's Unit Price,Amount\r\n";
            $content .= "CR-01-001-2025,2025-03-24,HECSM-RMH-CU01,SURGICAL GLOVES STERILE SIZE 7,MHJ MEDICAL AND LABORATORY SUPPLY,PC,INDOPLAS,5000,20.00,100000.00\r\n";
            $content .= "CR-01-001-2025,2025-03-24,HECSM-RMH-CU02,SURGICAL GLOVES STERILE SIZE 7.5,MHJ MEDICAL AND LABORATORY SUPPLY,PC,INDOPLAS,5000,20.00,100000.00\r\n";
            $content .= "CR-02-005-2025,2025-04-10,HECSM-HDU-00001,Hemodialysis Blood Tubing Set with 2TP,MHJ MEDICAL AND LABORATORY SUPPLY,PC,NIPRO,200,250.00,50000.00\r\n";
        } elseif ($type === 'transactions') {
            $filename = 'template_hecs_daily_transactions.csv';
            $content = "Date,Type,ItemCode,Item Name,Name of Consignor,Consignor's Unit Price,Mark-up,Selling Price,Quantity Issued,Qty. Returned,Total Actual Qty Issued,TOTAL SALES,Issued to,Less: SC/PWD Discount,OFFICIAL RECEIPT AMOUNT,OR No.,Total Consignor's Price,Charge Slip No.\r\n";
            $content .= "2024-11-01,CHARGE,HECSM-RMH-CSR02,CBG PACK,MHJ MEDICAL AND LABORATORY SUPPLY,13.00,87.00,100.00,6,0,6,600.00,FRIAS JENNIEFER,0.00,0.00,,78.00,CSG-2024-11-01\r\n";
            $content .= "2024-11-02,CASH,HECSM-RMH-CU01,SURGICAL GLOVES STERILE SIZE 7,MHJ MEDICAL AND LABORATORY SUPPLY,20.00,6.00,26.00,5,0,5,130.00,DELA CRUZ JUAN,0.00,130.00,OR-2024-0012,100.00,CSG-2024-11-02\r\n";
        } else {
            return back()->with('error', 'Invalid template type requested.');
        }

        $headers['Content-Disposition'] = 'attachment; filename="' . $filename . '"';
        return response($content, 200, $headers);
    }
}
