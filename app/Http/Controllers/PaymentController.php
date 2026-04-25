<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        return Payment::with('sale')->orderBy('payment_date', 'desc')->get();
    }

    public function show(int $id)
    {
        return Payment::with('sale')->findOrFail($id);
    }
}
