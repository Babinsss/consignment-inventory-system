<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'product_id',
        'batch_id',
        'batch_number',
        'expiry_date',
        'quantity',
        'unit_cost',
        'subtotal',
        'selling_price',
        'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }
}
