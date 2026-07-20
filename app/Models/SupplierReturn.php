<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierReturn extends Model
{
    protected $table = 'supplier_returns';

    protected $fillable = [
        'supplier_id',
        'employee_id',
        'return_date',
        'reason',
        'credit_status',
    ];

    protected $casts = [
        'return_date' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierReturnItem::class, 'supplier_return_id');
    }
}
