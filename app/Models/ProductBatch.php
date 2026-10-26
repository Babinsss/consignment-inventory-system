<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'delivery_id',
        'batch_number',
        'lot_number',
        'manufacture_date',
        'expiry_date',
        'quantity_received',
        'quantity_remaining',
        'unit_purchase_cost',
        'unit_selling_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'quantity_received' => 'integer',
        'quantity_remaining' => 'integer',
        'unit_purchase_cost' => 'decimal:2',
        'unit_selling_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'batch_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'batch_id');
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return (int) Carbon::now()->startOfDay()->diffInDays($this->expiry_date, false);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->days_until_expiry < 0;
    }

    public function getExpiryStatusAttribute(): string
    {
        $days = $this->days_until_expiry;
        if ($days < 0) {
            return 'expired';
        }
        if ($days <= 30) {
            return 'critical';
        }
        if ($days <= 90) {
            return 'warning';
        }
        return 'good';
    }
}
