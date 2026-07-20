<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    protected $table = 'sale_returns';

    protected $fillable = [
        'sale_id',
        'client_id',
        'status',
        'reason',
        'reviewed_by_employee_id',
        'review_note',
        'refund_status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewed_by_employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class, 'sale_return_id');
    }
}
