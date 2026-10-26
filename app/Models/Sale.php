<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'transaction_type',
        'patient_name',
        'patient_mrn',
        'department_name',
        'doctor_name',
        'prescription_no',
        'sale_date',
        'subtotal_cost',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'total_profit',
        'payment_method',
        'payment_status',
        'notes',
        'dispensed_by',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'subtotal_cost' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_profit' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getMarginPercentageAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0.0;
        }
        return round(($this->total_profit / $this->total_amount) * 100, 2);
    }
}
