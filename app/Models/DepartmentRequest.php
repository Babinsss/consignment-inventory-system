<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentRequest extends Model
{
    use HasFactory;

    protected $table = 'department_requests';

    protected $fillable = [
        'batch_id',
        'department_name',
        'requested_by',
        'product_id',
        'quantity',
        'quantity_issued',
        'purpose',
        'is_emergency',
        'status',
        'issued_by',
        'issued_at',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_issued' => 'integer',
        'is_emergency' => 'boolean',
        'issued_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function supply(): BelongsTo
    {
        return $this->product();
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
