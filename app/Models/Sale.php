<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'employee_id',
        'sale_date',
        'status',
        'subtotal',
        'tax',
        'total',
        'shipping_address',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesItem::class, 'sale_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'sale_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(SaleStatusHistory::class, 'sale_id')
            ->orderBy('created_at', 'desc');
    }
}
