<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $allowedDepartments = [
            ['name' => 'CSR', 'head_name' => 'MIA BUENVENIDA', 'group_name' => 'Wards / Ancillary Units'],
            ['name' => 'DIALYSIS', 'head_name' => 'EVANGELINE DETANOY', 'group_name' => 'Wards / Ancillary Units'],
            ['name' => 'PHARMACY OPD', 'head_name' => 'SHARA PATRIA SANTOS', 'group_name' => 'Pharmacy Units'],
            ['name' => 'PHARMACY MAIN', 'head_name' => 'SHARA PATRIA SANTOS', 'group_name' => 'Pharmacy Units'],
            ['name' => 'GUGMA DIALYSIS', 'head_name' => 'STEPHEN ESPENOCILLA', 'group_name' => 'Wards / Ancillary Units'],
        ];

        Department::query()->delete();

        foreach ($allowedDepartments as $dept) {
            Department::create([
                'name' => $dept['name'],
                'head_name' => $dept['head_name'],
                'group_name' => $dept['group_name'],
                'is_active' => true,
            ]);
        }
    }
}
