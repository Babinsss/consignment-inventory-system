<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_id',
        'movement_type',
        'quantity',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'unit_cost',
        'reason',
        'user_name',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
