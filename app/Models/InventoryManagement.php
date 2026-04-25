<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryManagement extends Model
{
    use HasFactory;

    protected $table = 'inventory_management';

    protected $fillable = [
        'product_id',
        'employee_id',
        'tipo_movimiento',
        'cantidad',
        'motivo',
        'stock_antes',
        'stock_despues',
        'fecha_movimiento',
        'estado_registro',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'stock_antes' => 'decimal:3',
        'stock_despues' => 'decimal:3',
        'fecha_movimiento' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}