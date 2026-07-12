<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class UnitController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Unit::all());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'abbreviation' => 'required|string|max:10',
        ]);

        $unit = Unit::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Unidad creada correctamente.',
            'data' => $unit,
        ], 201);
    }

    public function show(Unit $unit): JsonResponse
    {
        return response()->json($unit);
    }

    public function update(Request $request, Unit $unit): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
            'abbreviation' => 'sometimes|string|max:10',
        ]);

        $unit->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Unidad actualizada.',
            'data' => $unit,
        ]);
    }

    public function destroy(Unit $unit): JsonResponse
    {
        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unidad eliminada.',
        ]);
    }
}
