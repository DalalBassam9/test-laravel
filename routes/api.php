<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\API\LookupsController;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\CityController as CityController;
use App\Http\Controllers\API\ProductController as APIProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\AccountUserController;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\AddressController;
use Illuminate\Support\Facades\Storage;

    

Route::get('/private-image/{path}', function (string $path) {
    if (!Storage::disk('private')->exists($path)) {
        abort(404);
    }

    $fullPath = Storage::disk('private')->path($path);
    
    return response(
        Storage::disk('private')->get($path),
        200
    )->header('Content-Type', mime_content_type($fullPath));
    
})->where('path', '.*')->name('private.image');


Route::prefix('admin')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products/{id}', [ProductController::class, 'update']); 
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::get('categories/parents', [AdminCategoryController::class, 'getParents']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::get('/categories/{id}', [AdminCategoryController::class, 'show']);
    Route::post('/categories/{id}', [AdminCategoryController::class, 'update']); 
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy']);
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'getOrders']);
    Route::put('/orders/{id}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatusOrder']);
    Route::get('/orders/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'show']);
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index']);
    Route::get('/cities', [CityController::class, 'index']);
    Route::post('/cities', [CityController::class, 'store']);
    Route::get('/cities/{id}', [CityController::class, 'show']);
    Route::post('/cities/{id}', [CityController::class, 'update']);
    Route::delete('/cities/{id}', [CityController::class, 'destroy']);
    Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login']);
});


// routes/api.php
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::get('/products/featured', [APIProductController::class, 'featuredProducts']);
Route::get('/products/{slug}', [APIProductController::class, 'show']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/get-categories', [LookupsController::class, 'getCategories']);
Route::get('/categories/{slug}', [CategoryController::class, 'getCategoryProducts']);
Route::prefix('cart')->middleware('auth:sanctum')->group(function () {
    Route::get('/user', [CartController::class, 'getUserCart']);
});


Route::prefix('cart')->group(function () {
    Route::post('/add/{product}', [CartController::class, 'addToCart']);
    Route::get('/', [CartController::class, 'getCart']);
    Route::post('/{cartItem}', [CartController::class, 'updateCartItem']); // تحديث الكمية
    Route::delete('{cartItem}', [CartController::class, 'removeCartItem']);
    Route::delete('/', [CartController::class, 'clearCart']);
});

Route::get('/get-session-id', [CartController::class, 'getSessionId']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AccountUserController::class, 'show']);
    Route::put('/user/info', [AccountUserController::class, 'updateUserInformation']);
    Route::put('/update-password', [AccountUserController::class, 'updatePassword']);
});
Route::prefix('addresses')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [AddressController::class, 'store']);
    Route::get('/user', [AddressController::class, 'getUserAddresses']);
    Route::post('/{id}/set-default', [AddressController::class, 'setDefault']);
    Route::delete('/{id}', [AddressController::class, 'delete']);
});

Route::post('/checkout/process', [CheckoutController::class, 'process']);
Route::get('/orders-user/{id}', [CheckoutController::class, 'getOrderUser']);

Route::get('cities', [LookupsController::class, 'getCities']);

Route::prefix('user/orders')->group(function () {
    Route::get('/', [OrderController::class, 'getUserOrders']);
    Route::get('/{id}', [OrderController::class, 'findByIdUserOrder']);
    Route::put('/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::get('/counts', [OrderController::class, 'getOrderCountsByStatus']);
});

/*Route::get('/user/addresses', function () {
    return Auth::user()->addresses()->where('temp', false)->get();
})->middleware('auth:sanctum');
*/