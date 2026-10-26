<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\MedicalInventorySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MedicalInventorySeeder::class);
        $this->seed(UserSeeder::class);
    }

    public function test_consignment_staff_can_access_medical_supplies_and_hecs_forms(): void
    {
        $staff = User::where('role', User::ROLE_MEDICAL_SUPPLY)->first();
        $this->actingAs($staff);

        $this->get('/hospital/stock-card')->assertStatus(200);
        $this->get('/hospital/stcg')->assertStatus(200);
        $this->get('/hospital/import')->assertStatus(200);
        $this->get('/deliveries')->assertStatus(200);
        $this->get('/products')->assertStatus(200);
    }

    public function test_consignment_staff_cannot_access_financial_ledger_or_users_management(): void
    {
        $staff = User::where('role', User::ROLE_MEDICAL_SUPPLY)->first();
        $this->actingAs($staff);

        // Blocked from financial ledger
        $fin = $this->get('/financial');
        $fin->assertRedirect(route('dashboard'));
        $fin->assertSessionHas('error');

        // Blocked from admin user management
        $users = $this->get('/users');
        $users->assertRedirect(route('dashboard'));
        $users->assertSessionHas('error');
    }

    public function test_finance_officer_can_access_financial_ledger_and_consignor_settlements(): void
    {
        $finance = User::where('role', User::ROLE_FINANCE)->first();
        $this->actingAs($finance);

        $this->get('/financial')->assertStatus(200);
        $this->get('/hospital/stcg')->assertStatus(200);
        $this->get('/hospital/sccg')->assertStatus(200);

        // Blocked from admin user management
        $users = $this->get('/users');
        $users->assertRedirect(route('dashboard'));
    }

    public function test_administrator_has_master_clearance_across_all_modules(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();
        $this->actingAs($admin);

        $this->get('/users')->assertStatus(200);
        $this->get('/financial')->assertStatus(200);
        $this->get('/hospital/stock-card')->assertStatus(200);
        $this->get('/hospital/import')->assertStatus(200);
        $this->get('/deliveries')->assertStatus(200);
    }

    public function test_fast_role_switcher_switches_active_user(): void
    {
        $initial = User::where('role', User::ROLE_MEDICAL_SUPPLY)->first();
        $this->actingAs($initial);

        $response = $this->post('/switch-role', [
            'role' => User::ROLE_FINANCE,
        ]);

        $response->assertRedirect();
        $this->assertEquals(User::ROLE_FINANCE, auth()->user()->role);
        $this->assertEquals('Judy Lyn G. Bermoy', auth()->user()->name);
    }
}
