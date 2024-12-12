<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [UserController::class, 'login']);
    Route::post('register', [UserController::class, 'register']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('refresh', [UserController::class, 'refresh']);
    Route::post('me', [UserController::class, 'me']);
    Route::post('update/{user}', [UserController::class, 'updateProfile'])->missing(function(){
        return response()->json('user not found', 404);
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}/stores', [CategoryController::class, 'showStores']);
    Route::get('/categories/{categoryId}/stores/{storeId}/products', [StoreController::class, 'showProducts']);
    Route::get('/categories/{categoryId}/stores/{storeId}/products/{productId}', [ProductController::class, 'show']);

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
