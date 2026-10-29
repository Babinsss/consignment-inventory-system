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
        $this->get('/requests')->assertStatus(200);
        $this->get('/portal')->assertStatus(200);
    }

    public function test_consignment_staff_cannot_access_users_management(): void
    {
        $staff = User::where('role', User::ROLE_MEDICAL_SUPPLY)->first();
        $this->actingAs($staff);

        // Blocked from admin user management
        $users = $this->get('/users');
        $users->assertRedirect(route('dashboard'));
        $users->assertSessionHas('error');
    }

    public function test_administrator_has_master_clearance_across_all_modules(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();
        $this->actingAs($admin);

        $this->get('/users')->assertStatus(200);
        $this->get('/hospital/stock-card')->assertStatus(200);
        $this->get('/hospital/import')->assertStatus(200);
        $this->get('/deliveries')->assertStatus(200);
        $this->get('/requests')->assertStatus(200);
        $this->get('/portal')->assertStatus(200);
    }
}
