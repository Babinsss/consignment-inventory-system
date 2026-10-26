<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_number',
        'invoice_number',
        'supplier_id',
        'delivery_date',
        'received_by',
        'status',
        'total_amount',
        'payment_status',
        'amount_paid',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected $appends = [
        'balance_due',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->amount_paid);
    }
}
