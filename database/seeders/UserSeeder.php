<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'aliana.dula@rmph.capiz.gov.ph',
                'name' => 'Aliana Marie A. Dula',
                'role' => User::ROLE_MEDICAL_SUPPLY,
                'department' => 'Central Supply Room (CSR) / HECS',
                'title' => 'Administrative Aide III (Consignment Custodian)',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'email' => 'joemar.dadivas@rmph.capiz.gov.ph',
                'name' => 'Joemar A. Dadivas, RN, CNN, MAN',
                'role' => User::ROLE_MEDICAL_SUPPLY,
                'department' => 'Hemodialysis Unit (HDU) & CSR',
                'title' => 'Nurse Supervisor / Consignment Lead',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'email' => 'angela.cruz@rmph.capiz.gov.ph',
                'name' => 'Angela Cruz, RPh',
                'role' => User::ROLE_PHARMACY,
                'department' => 'Hospital Pharmacy',
                'title' => 'Pharmacist II / Dispensary Custodian',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'email' => 'finance.hecs@rmph.capiz.gov.ph',
                'name' => 'Judy Lyn G. Bermoy',
                'role' => User::ROLE_FINANCE,
                'department' => 'Provincial Accounting & Financial Audit',
                'title' => 'Consignment Settlement Auditor',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'email' => 'admin@rmph.capiz.gov.ph',
                'name' => 'Florencio O. Luching, Jr., MD, FPCS',
                'role' => User::ROLE_ADMIN,
                'department' => 'Office of the Chief of Hospital',
                'title' => 'Chief of Hospital II / Executive Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                $u
            );
        }

        $this->command->info('RMPH Hospital Staff Users & Roles successfully seeded.');
    }
}
