<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShoppingCart\UpdateCartItemRequest;
use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShoppingCartItemController extends Controller
{
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();

        $cart = ShoppingCart::firstOrCreate(
            ['user_id' => $user->id],
            ['total' => 0]
        );

        return DB::transaction(function () use ($request, $cart) {

            $product = Product::lockForUpdate()->findOrFail($request->product_id);

            $item = ShoppingCartItem::where('shopping_cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este producto ya está en tu carrito.',
                ], 409);
            }

            if ($product->stock < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente',
                ], 422);
            }

            ShoppingCartItem::create([
                'shopping_cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'discount' => $product->discount,
                'unit_price' => $product->unit_price,
                'subtotal' => $product->final_price,
            ]);

            $cart->recalculateTotal();

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito.',
            ], 201);
        });
    }

    public function updateItem(UpdateCartItemRequest $request, int $itemId)
    {
        $user = $request->user();

        $cart = ShoppingCart::firstOrCreate(
            ['user_id' => $user->id],
            ['total' => 0]
        );

        return DB::transaction(function () use ($request, $cart, $itemId) {

            $item = ShoppingCartItem::where('id', $itemId)
                ->where('shopping_cart_id', $cart->id)
                ->firstOrFail();

            $product = Product::lockForUpdate()->findOrFail($item->product_id);

            if ($request->quantity > $product->stock) {
                return response()->json(['message' => 'Stock insuficiente'], 422);
            }

            $item->quantity = $request->quantity;
            $item->subtotal = $item->quantity * $item->final_price;
            $item->save();

            $cart->recalculateTotal();

            return response()->json($item->load('product'));
        });
    }

    public function removeItem(Request $request, int $itemId)
    {
        $user = $request->user();

        $cart = ShoppingCart::firstOrCreate(
            ['user_id' => $user->id],
            ['total' => 0]
        );

        return DB::transaction(function () use ($cart, $itemId) {

            $item = ShoppingCartItem::where('id', $itemId)
                ->where('shopping_cart_id', $cart->id)
                ->firstOrFail();

            $item->delete();

            $cart->recalculateTotal();

            return response()->json(['message' => 'Item eliminado']);
        });
    }
}
