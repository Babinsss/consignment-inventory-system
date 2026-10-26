<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount(['deliveries', 'products']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:suppliers,code',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:100',
            'payment_terms' => 'required|in:COD,Net 15,Net 30,Net 60,Consignment',
            'notes' => 'nullable|string',
        ]);

        $supplier = Supplier::create($validated);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', "Supplier {$supplier->name} registered successfully!");
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'deliveries' => fn($q) => $q->latest('delivery_date'),
            'payments' => fn($q) => $q->latest('payment_date'),
            'products.category',
        ]);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:suppliers,code,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:100',
            'payment_terms' => 'required|in:COD,Net 15,Net 30,Net 60,Consignment',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', "Supplier {$supplier->name} updated successfully!");
    }
}
