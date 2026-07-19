<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryManagement extends Model
{
    use HasFactory;

    protected $table = 'inventory_management';

    protected $fillable = [
        'product_id',
        'sale_id',
        'sale_return_id',
        'purchase_id',
        'supplier_id',
        'supplier_return_id',
        'employee_id',
        'movement_type',
        'origin',
        'quantity',
        'reason',
        'stock_before',
        'stock_after',
        'movement_date',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'stock_before' => 'decimal:3',
        'stock_after' => 'decimal:3',
        'movement_date' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
}
