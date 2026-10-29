<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentRequest;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentRequestController extends Controller
{
    /**
     * Master Requisition & Issuance Archive (Monitor who requested)
     */
    public function index(Request $request)
    {
        $query = DepartmentRequest::with(['product.category', 'issuer'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('batch_id', 'like', "%{$search}%")
                  ->orWhere('department_name', 'like', "%{$search}%")
                  ->orWhere('requested_by', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('department')) {
            $query->where('department_name', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('requested_by')) {
            $query->where('requested_by', 'like', "%{$request->requested_by}%");
        }

        $allRequests = $query->get();

        // Group into Requisition Batches
        $groupedBatches = [];
        foreach ($allRequests as $req) {
            if (!isset($groupedBatches[$req->batch_id])) {
                $groupedBatches[$req->batch_id] = [
                    'batch_id' => $req->batch_id,
                    'created_at' => $req->created_at,
                    'department_name' => $req->department_name,
                    'requested_by' => $req->requested_by,
                    'purpose' => $req->purpose,
                    'is_emergency' => $req->is_emergency,
                    'status' => $req->status,
                    'issued_by' => $req->issuer?->name,
                    'issued_at' => $req->issued_at,
                    'items' => collect(),
                ];
            }
            $groupedBatches[$req->batch_id]['items']->push($req);
        }

        // Determine overall batch status
        foreach ($groupedBatches as $key => $batch) {
            $hasPending = $batch['items']->contains('status', 'Pending');
            $hasApproved = $batch['items']->contains('status', 'Approved');
            $hasDenied = $batch['items']->contains('status', 'Denied');

            if ($hasPending) {
                $groupedBatches[$key]['status'] = 'Pending';
            } elseif ($hasApproved && !$hasDenied) {
                $groupedBatches[$key]['status'] = 'Approved';
            } elseif ($hasDenied && !$hasApproved) {
                $groupedBatches[$key]['status'] = 'Denied';
            } else {
                $groupedBatches[$key]['status'] = 'Partially Approved';
            }
        }

        $departments = Department::whereIn('name', ['CSR', 'DIALYSIS', 'PHARMACY OPD', 'PHARMACY MAIN', 'GUGMA DIALYSIS'])
            ->orderByRaw("CASE WHEN name='CSR' THEN 1 WHEN name='DIALYSIS' THEN 2 WHEN name='PHARMACY OPD' THEN 3 WHEN name='PHARMACY MAIN' THEN 4 WHEN name='GUGMA DIALYSIS' THEN 5 ELSE 6 END")
            ->get();
        $totalPendingCount = DepartmentRequest::where('status', 'Pending')->distinct('batch_id')->count('batch_id');
        $totalApprovedCount = DepartmentRequest::where('status', 'Approved')->distinct('batch_id')->count('batch_id');

        return view('requests.index', [
            'batches' => array_values($groupedBatches),
            'departments' => $departments,
            'totalPendingCount' => $totalPendingCount,
            'totalApprovedCount' => $totalApprovedCount,
        ]);
    }

    /**
     * Department Front-Facing Requisition Portal
     */
    public function portal()
    {
        $products = Product::where('is_active', true)
            ->with(['category', 'activeBatches'])
            ->orderBy('name')
            ->get()
            ->filter(fn($p) => $p->total_stock > 0)
            ->values();

        // Allowed departments strictly limited to CSR, DIALYSIS, PHARMACY OPD, PHARMACY MAIN, & GUGMA DIALYSIS
        $allowedDeptNames = ['CSR', 'DIALYSIS', 'PHARMACY OPD', 'PHARMACY MAIN', 'GUGMA DIALYSIS'];
        $departments = Department::whereIn('name', $allowedDeptNames)
            ->orderByRaw("CASE WHEN name='CSR' THEN 1 WHEN name='DIALYSIS' THEN 2 WHEN name='PHARMACY OPD' THEN 3 WHEN name='PHARMACY MAIN' THEN 4 WHEN name='GUGMA DIALYSIS' THEN 5 ELSE 6 END")
            ->get();

        if ($departments->isEmpty()) {
            // Fallback ensure existence if table was empty
            $departments = collect([
                (object)['name' => 'CSR', 'head_name' => 'MIA BUENVENIDA', 'group_name' => 'Wards / Ancillary Units'],
                (object)['name' => 'DIALYSIS', 'head_name' => 'EVANGELINE DETANOY', 'group_name' => 'Wards / Ancillary Units'],
                (object)['name' => 'PHARMACY OPD', 'head_name' => 'SHARA PATRIA SANTOS', 'group_name' => 'Pharmacy Units'],
                (object)['name' => 'PHARMACY MAIN', 'head_name' => 'SHARA PATRIA SANTOS', 'group_name' => 'Pharmacy Units'],
                (object)['name' => 'GUGMA DIALYSIS', 'head_name' => 'STEPHEN ESPENOCILLA', 'group_name' => 'Wards / Ancillary Units'],
            ]);
        }

        $categories = $products->pluck('category.name')->filter()->unique()->values();

        return view('requests.portal', compact('products', 'departments', 'categories'));
    }

    /**
     * Submit Requisition from Portal
     */
    public function submitRequest(Request $request)
    {
        $request->validate([
            'department_name' => ['required', 'string', \Illuminate\Validation\Rule::in(['CSR', 'DIALYSIS', 'PHARMACY OPD', 'PHARMACY MAIN', 'GUGMA DIALYSIS'])],
            'requested_by' => 'required|string|max:120',
            'purpose' => 'required|string|max:255',
            'cart_data' => 'required|string',
        ]);

        $cart = json_decode($request->cart_data, true);
        if (empty($cart) || !is_array($cart)) {
            return redirect()->back()->with('error', 'Please select at least one item to request.');
        }

        $seq = DepartmentRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->distinct('batch_id')
            ->count('batch_id') + 1;

        $batchId = sprintf('RIS-%s-%03d', now()->format('Y-m'), $seq);
        $isEmergency = $request->boolean('is_emergency');

        DB::transaction(function () use ($batchId, $request, $cart, $isEmergency) {
            foreach ($cart as $item) {
                $productId = (int) ($item['id'] ?? $item['product_id']);
                $qty = (int) ($item['qty'] ?? $item['quantity']);

                if ($productId > 0 && $qty > 0) {
                    DepartmentRequest::create([
                        'batch_id' => $batchId,
                        'department_name' => trim($request->department_name),
                        'requested_by' => trim($request->requested_by),
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'quantity_issued' => 0,
                        'purpose' => trim($request->purpose),
                        'is_emergency' => $isEmergency,
                        'status' => 'Pending',
                    ]);
                }
            }
        });

        return redirect()->route('requests.portal')
            ->with('success', "Requisition submitted successfully! Control Slip Number: {$batchId}")
            ->with('submitted_batch_id', $batchId);
    }

    /**
     * Approve and Issue Department Request Batch
     */
    public function approveBatch(Request $request, $batch_id)
    {
        $requests = DepartmentRequest::with('product.activeBatches')
            ->where('batch_id', $batch_id)
            ->where('status', 'Pending')
            ->get();

        if ($requests->isEmpty()) {
            return redirect()->back()->with('error', 'No pending requests found for this batch.');
        }

        $user = Auth::user();

        DB::transaction(function () use ($requests, $request, $user) {
            foreach ($requests as $req) {
                // Check if user submitted an adjusted quantity
                $adjQty = $request->input("qty_{$req->id}");
                $issuedQty = ($adjQty !== null && $adjQty !== '') ? (int) $adjQty : $req->quantity;

                if ($issuedQty <= 0) {
                    $req->update([
                        'status' => 'Denied',
                        'quantity_issued' => 0,
                        'issued_by' => $user?->id,
                        'issued_at' => now(),
                        'notes' => 'Requested quantity zeroed or cancelled during approval.',
                    ]);
                    continue;
                }

                // Deduct stock using FEFO (First Expiring, First Out)
                $product = $req->product;
                $remainingToDeduct = $issuedQty;

                $activeBatches = ProductBatch::where('product_id', $product->id)
                    ->where('status', 'active')
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->get();

                foreach ($activeBatches as $batch) {
                    if ($remainingToDeduct <= 0) break;

                    $deduct = min($batch->quantity_remaining, $remainingToDeduct);
                    $balanceBefore = $product->total_stock;

                    $batch->quantity_remaining -= $deduct;
                    if ($batch->quantity_remaining == 0) {
                        $batch->status = 'depleted';
                    }
                    $batch->save();

                    $remainingToDeduct -= $deduct;

                    // Log Stock Movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'batch_id' => $batch->id,
                        'movement_type' => 'dispense_out',
                        'quantity' => -$deduct,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceBefore - $deduct,
                        'unit_cost' => $batch->unit_purchase_cost,
                        'reason' => "Issued for Requisition {$req->batch_id} to {$req->department_name} (Requested by: {$req->requested_by})",
                        'user_name' => $user?->name ?? 'Supply Officer',
                    ]);
                }

                $req->update([
                    'status' => 'Approved',
                    'quantity_issued' => $issuedQty,
                    'issued_by' => $user?->id,
                    'issued_at' => now(),
                ]);
            }
        });

        return redirect()->back()->with('success', "Requisition {$batch_id} has been approved and supplies issued to {$requests->first()->department_name}!");
    }

    /**
     * Deny Department Request Batch
     */
    public function denyBatch(Request $request, $batch_id)
    {
        $requests = DepartmentRequest::where('batch_id', $batch_id)
            ->where('status', 'Pending')
            ->get();

        if ($requests->isEmpty()) {
            return redirect()->back()->with('error', 'No pending requests found to deny.');
        }

        $user = Auth::user();
        foreach ($requests as $req) {
            $req->update([
                'status' => 'Denied',
                'issued_by' => $user?->id,
                'issued_at' => now(),
                'notes' => $request->input('reason', 'Denied by Supply Management'),
            ]);
        }

        return redirect()->back()->with('success', "Requisition {$batch_id} has been denied. Inventory remains untouched.");
    }

    /**
     * Printable Requisition and Issue Slip (RIS)
     */
    public function printRis($batch_id)
    {
        $requests = DepartmentRequest::with(['product.category', 'issuer'])
            ->where('batch_id', $batch_id)
            ->get();

        if ($requests->isEmpty()) {
            abort(404, 'Requisition batch not found.');
        }

        $first = $requests->first();

        return view('requests.print_ris', [
            'batch_id' => $batch_id,
            'department_name' => $first->department_name,
            'requested_by' => $first->requested_by,
            'purpose' => $first->purpose,
            'is_emergency' => $first->is_emergency,
            'status' => $first->status,
            'issuer' => $first->issuer,
            'created_at' => $first->created_at,
            'issued_at' => $first->issued_at,
            'items' => $requests,
        ]);
    }
}
