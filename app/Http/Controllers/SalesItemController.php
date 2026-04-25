<?php

namespace App\Http\Controllers;

use App\Models\SalesItem;
use Illuminate\Http\Request;
use App\Http\Requests\Sales\StoreSalesItemRequest;

class SalesItemController extends Controller
{
    public function index()
    {
        return SalesItem::with(['sale', 'product'])->get();
    }

    public function store(StoreSalesItemRequest $request)
    {
        $item = SalesItem::create($request->validated());
        return response()->json($item, 201);
    }

    public function show($id)
    {
        return SalesItem::with(['sale', 'product'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $item = SalesItem::findOrFail($id);
        $item->update($request->only(['quantity', 'price', 'discount']));
        return response()->json($item);
    }

    public function destroy($id)
    {
        SalesItem::findOrFail($id)->delete();
        return response()->json(['message' => 'Item eliminado']);
    }
}