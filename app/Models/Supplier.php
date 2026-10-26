<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'phone',
        'email',
        'address',
        'tax_id',
        'payment_terms',
        'status',
        'notes',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function getTotalPurchasedAttribute(): float
    {
        return (float) $this->deliveries()->where('status', '!=', 'cancelled')->sum('total_amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, $this->total_purchased - $this->total_paid);
    }
}
