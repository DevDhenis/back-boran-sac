<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ShoppingCart\UpdateCartItemRequest;
use Illuminate\Http\Request;

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
                    'message' => 'Este producto ya está en tu carrito.'
                ], 409);
            }

            if ($product->stock < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente'
                ], 422);
            }

            ShoppingCartItem::create([
                'shopping_cart_id' => $cart->id,
                'product_id' => $product->id,
                'cantidad' => 1,
                'descuento' => $product->descuento,
                'precio_unitario' => $product->pre_uni,
                'subtotal' => $product->pre_fin,
            ]);

            $cart->recalculateTotal();

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito.'
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

            if ($request->cantidad > $product->stock) {
                return response()->json(['message' => 'Stock insuficiente'], 422);
            }

            $item->cantidad = $request->cantidad;
            $item->subtotal = $item->cantidad * $item->precio_final;
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
