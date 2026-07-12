<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShoppingCartResource;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShoppingCartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $cart = ShoppingCart::with([
            'items.product.unit',
            'items.product.category',
        ])->firstOrCreate(['user_id' => $user->id], ['total' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Carrito obtenido correctamente.',
            'data' => new ShoppingCartResource($cart),
        ]);
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        $person = $user->person;

        if (! $person || ! $person->client) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no está registrado como cliente.',
            ], 422);
        }

        $client = $person->client;

        $request->validate([
            'card_number' => 'required|digits:16',
            'card_holder' => 'required|string|max:255',
            'card_expiration' => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'card_cvv' => 'required|digits:3',
            'phone' => 'required|string|max:20',
            'shipping_address' => 'nullable|string|max:255',
        ]);

        $cart = ShoppingCart::with('items.product')
            ->where('user_id', $user->id)
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'El carrito está vacío',
            ], 422);
        }

        return DB::transaction(function () use ($cart, $client, $request) {

            foreach ($cart->items as $item) {
                $product = $item->product;

                if ($item->quantity > $product->stock) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente para {$product->name}",
                    ], 422);
                }
            }

            $subtotal = $cart->items->sum('subtotal');
            $tax = round($subtotal * 0.18, 2);
            $total = $subtotal + $tax;

            $sale = Sale::create([
                'customer_id' => $client->id,
                'employee_id' => null,
                'sale_date' => now(),
                'status' => 'pending_shipment',
                'shipping_address' => $request->shipping_address,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            foreach ($cart->items as $item) {

                SalesItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'discount' => 0,
                    'subtotal' => $item->subtotal,
                ]);

                $product = $item->product;
                $product->stock -= $item->quantity;
                $product->save();
            }

            Payment::create([
                'sale_id' => $sale->id,
                'method' => 'card',
                'amount' => $total,
                'payment_date' => now(),
                'status' => 'confirmed',
                'card_holder' => $request->card_holder,
                'card_last4' => substr($request->card_number, -4),
                'card_expiration' => $request->card_expiration,
                'phone' => $request->phone,
            ]);

            $cart->items()->delete();
            $cart->total = 0;
            $cart->save();

            return response()->json([
                'success' => true,
                'message' => 'Compra realizada correctamente',
                'sale_id' => $sale->id,
            ], 201);
        });
    }
}
