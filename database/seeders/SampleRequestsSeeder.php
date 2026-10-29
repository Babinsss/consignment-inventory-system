<?php

namespace Database\Seeders;

use App\Models\DepartmentRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleRequestsSeeder extends Seeder
{
    public function run(): void
    {
        DepartmentRequest::query()->delete();

        $admin = User::first();
        $products = Product::where('is_active', true)->take(10)->get();
        if ($products->isEmpty()) {
            return;
        }

        // Batch 1: Pending Emergency Request from DIALYSIS
        $batch1 = 'RIS-' . now()->format('Y-m') . '-001';
        DepartmentRequest::create([
            'batch_id' => $batch1,
            'department_name' => 'DIALYSIS',
            'requested_by' => 'Evangeline Detanoy',
            'product_id' => $products[0]->id,
            'quantity' => 15,
            'quantity_issued' => 0,
            'purpose' => 'Urgent Hemodialysis Line Replacement for ICU Transfer Patient',
            'is_emergency' => true,
            'status' => 'Pending',
            'created_at' => now()->subMinutes(25),
        ]);
        if (isset($products[1])) {
            DepartmentRequest::create([
                'batch_id' => $batch1,
                'department_name' => 'DIALYSIS',
                'requested_by' => 'Evangeline Detanoy',
                'product_id' => $products[1]->id,
                'quantity' => 10,
                'quantity_issued' => 0,
                'purpose' => 'Urgent Hemodialysis Line Replacement for ICU Transfer Patient',
                'is_emergency' => true,
                'status' => 'Pending',
                'created_at' => now()->subMinutes(25),
            ]);
        }

        // Batch 2: Pending Standard Request from PHARMACY OPD
        $batch2 = 'RIS-' . now()->format('Y-m') . '-002';
        if (isset($products[2])) {
            DepartmentRequest::create([
                'batch_id' => $batch2,
                'department_name' => 'PHARMACY OPD',
                'requested_by' => 'Shara Patria Santos',
                'product_id' => $products[2]->id,
                'quantity' => 20,
                'quantity_issued' => 0,
                'purpose' => 'Weekly replenishment for OPD dispensary counter',
                'is_emergency' => false,
                'status' => 'Pending',
                'created_at' => now()->subHours(2),
            ]);
        }
        if (isset($products[3])) {
            DepartmentRequest::create([
                'batch_id' => $batch2,
                'department_name' => 'PHARMACY OPD',
                'requested_by' => 'Shara Patria Santos',
                'product_id' => $products[3]->id,
                'quantity' => 8,
                'quantity_issued' => 0,
                'purpose' => 'Weekly replenishment for OPD dispensary counter',
                'is_emergency' => false,
                'status' => 'Pending',
                'created_at' => now()->subHours(2),
            ]);
        }

        // Batch 3: Approved / Issued Request to CSR
        $batch3 = 'RIS-' . now()->format('Y-m') . '-003';
        if (isset($products[0])) {
            DepartmentRequest::create([
                'batch_id' => $batch3,
                'department_name' => 'CSR',
                'requested_by' => 'Mia Buenvenida',
                'product_id' => $products[0]->id,
                'quantity' => 25,
                'quantity_issued' => 25,
                'purpose' => 'Sterilization tray and surgical pack replenishment',
                'is_emergency' => false,
                'status' => 'Approved',
                'issued_by' => $admin?->id,
                'issued_at' => now()->subHours(5),
                'created_at' => now()->subHours(6),
            ]);
        }
    }
}
