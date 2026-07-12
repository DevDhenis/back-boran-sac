<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShoppingCartController;
use App\Http\Controllers\ShoppingCartItemController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

// Healthcheck público (sin auth): sirve para verificar que el backend responde en Render.
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Backend Laravel funcionando',
    ]);
});

Route::prefix('auth')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('verify-recovery-code', [AuthController::class, 'verifyRecoveryCode']);
});

Route::middleware('jwt')->group(function () {

    Route::post('auth/verify-email', [AuthController::class, 'verifyEmail']);

    // Perfil del usuario autenticado (self-service de datos personales)
    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('profile/update', [ProfileController::class, 'update']);

    Route::get('roles/{role}/accesses', [RoleController::class, 'getAccesses']);
    Route::post('roles/{role}/accesses', [RoleController::class, 'syncAccesses']);
    Route::post('roles/{role}/assign-user', [RoleController::class, 'assignUser']);
    Route::get('roles/{role}/users', [RoleController::class, 'users']);
    Route::apiResource('roles', RoleController::class);

    Route::get('accesses', [AccessController::class, 'index']);

    Route::apiResource('employees', EmployeeController::class);
    Route::post('employees/{employee}/update', [EmployeeController::class, 'update']);
    Route::patch('employees/{id}/suspend', [EmployeeController::class, 'suspend']);
    Route::patch('employees/{id}/activate', [EmployeeController::class, 'activate']);

    Route::apiResource('product-categories', ProductCategoryController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('products', ProductController::class);
    Route::post('products/{product}/update', [ProductController::class, 'update']);

    Route::get('shopping-cart', [ShoppingCartController::class, 'index']);
    Route::post('shopping-cart/checkout', [ShoppingCartController::class, 'checkout']);

    Route::post('shopping-cart/items', [ShoppingCartItemController::class, 'addItem']);
    Route::put('shopping-cart/items/{itemId}', [ShoppingCartItemController::class, 'updateItem']);
    Route::delete('shopping-cart/items/{itemId}', [ShoppingCartItemController::class, 'removeItem']);

    Route::middleware(['jwt', 'access:ventas'])->prefix('sales')->group(function () {
        Route::get('/', [SaleController::class, 'index']);
        Route::get('{sale}', [SaleController::class, 'show']);
        Route::post('{sale}/change-status', [SaleController::class, 'changeStatus']);
    });

    Route::get('my-sales', [SaleController::class, 'mySales']);
});
