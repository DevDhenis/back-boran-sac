<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleStatusHistory extends Model
{
    protected $fillable = [
        'sale_id',
        'previous_status',
        'new_status',
        'changed_by_employee_id',
        'changed_by_client_id',
        'reason',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function changedByEmployee()
    {
        return $this->belongsTo(Employee::class, 'changed_by_employee_id');
    }

    public function changedByClient()
    {
        return $this->belongsTo(Client::class, 'changed_by_client_id');
    }
}
