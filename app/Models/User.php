<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MEDICAL_SUPPLY = 'medical_supply_staff';
    public const ROLE_PHARMACY = 'pharmacy_staff';
    public const ROLE_FINANCE = 'finance_officer';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'title',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is an Administrator
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is Medical Supply / Consignment Staff
     */
    public function isMedicalSupplyStaff(): bool
    {
        return $this->role === self::ROLE_MEDICAL_SUPPLY;
    }

    /**
     * Check if user is Pharmacy Staff
     */
    public function isPharmacyStaff(): bool
    {
        return $this->role === self::ROLE_PHARMACY;
    }

    /**
     * Check if user is Finance Officer
     */
    public function isFinanceOfficer(): bool
    {
        return $this->role === self::ROLE_FINANCE;
    }

    /**
     * Check if user matches any of the given roles
     */
    public function hasRole(string|array $roles): bool
    {
        if ($this->isAdmin()) {
            return true; // Admins have master access
        }

        if (is_string($roles)) {
            $roles = [$roles];
        }

        return in_array($this->role, $roles, true);
    }

    /**
     * Can access Consignment & Medical Supplies modules
     */
    public function canAccessConsignment(): bool
    {
        return $this->isAdmin() || $this->isMedicalSupplyStaff() || $this->isFinanceOfficer();
    }

    /**
     * Can access Pharmacy modules
     */
    public function canAccessPharmacy(): bool
    {
        return $this->isAdmin() || $this->isPharmacyStaff();
    }

    /**
     * Can access Financial Ledger & Consignment Settlements
     */
    public function canAccessFinancial(): bool
    {
        return $this->isAdmin() || $this->isFinanceOfficer();
    }

    /**
     * Get human-readable role label
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Hospital Administrator',
            self::ROLE_MEDICAL_SUPPLY => 'Consignment & Medical Supplies Staff',
            self::ROLE_PHARMACY => 'Pharmacy Staff / Pharmacist',
            self::ROLE_FINANCE => 'Hospital Finance / Consignment Auditor',
            default => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }

    /**
     * Get Tailwind badge color class
     */
    public function getRoleBadgeColorAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'bg-rose-100 text-rose-800 border-rose-200',
            self::ROLE_MEDICAL_SUPPLY => 'bg-purple-100 text-purple-800 border-purple-200',
            self::ROLE_PHARMACY => 'bg-sky-100 text-sky-800 border-sky-200',
            self::ROLE_FINANCE => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }
}
