<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'item_type',
        'sku',
        'barcode',
        'name',
        'generic_name',
        'brand_name',
        'dosage_form',
        'unit_of_measure',
        'inventory_type',
        'min_stock_alert',
        'max_stock_level',
        'default_purchase_price',
        'selling_price',
        'storage_condition',
        'is_active',
        'description',
    ];

    protected $casts = [
        'min_stock_alert' => 'integer',
        'max_stock_level' => 'integer',
        'default_purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'total_stock',
        'inventory_value',
        'stock_status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function activeBatches(): HasMany
    {
        // Order by expiry_date asc for FEFO (First Expire, First Out)
        return $this->hasMany(ProductBatch::class)
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->orderBy('expiry_date', 'asc');
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function departmentRequests(): HasMany
    {
        return $this->hasMany(DepartmentRequest::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->batches()
            ->where('status', '!=', 'depleted')
            ->where('status', '!=', 'expired')
            ->sum('quantity_remaining');
    }

    public function getInventoryValueAttribute(): float
    {
        return (float) $this->batches()
            ->where('status', '!=', 'depleted')
            ->where('status', '!=', 'expired')
            ->selectRaw('SUM(quantity_remaining * unit_purchase_cost) as total')
            ->value('total') ?? 0.00;
    }

    public function getStockStatusAttribute(): string
    {
        $stock = $this->total_stock;
        if ($stock <= 0) {
            return 'out_of_stock';
        }
        if ($stock <= $this->min_stock_alert) {
            return 'low_stock';
        }
        if ($stock > $this->max_stock_level) {
            return 'overstocked';
        }
        return 'in_stock';
    }

    public function getNearestExpiryAttribute()
    {
        return $this->batches()
            ->where('quantity_remaining', '>', 0)
            ->where('status', 'active')
            ->orderBy('expiry_date', 'asc')
            ->value('expiry_date');
    }

    public function getExpiryAlertAttribute(): ?string
    {
        $nearest = $this->nearest_expiry;
        if (!$nearest) {
            return null;
        }

        $days = (int) Carbon::now()->startOfDay()->diffInDays(Carbon::parse($nearest), false);
        if ($days < 0) {
            return 'expired';
        }
        if ($days <= 30) {
            return 'critical';
        }
        if ($days <= 90) {
            return 'warning';
        }
        return 'safe';
    }
}
