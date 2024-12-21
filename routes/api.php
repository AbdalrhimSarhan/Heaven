<?php

use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavouriteProductController;
use App\Http\Controllers\OrderController;
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

    'middleware' => ['api','setLang'],
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [UserController::class, 'login']);
    Route::post('register', [UserController::class, 'register']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('refresh', [UserController::class, 'refresh']);
    Route::post('me', [UserController::class, 'me']);
    Route::post('update/{user}', [UserController::class, 'updateProfile'])->missing(function(){
        return response()->json(__('message.user_not_found'), 404);
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}/stores', [CategoryController::class, 'showStores']);
    Route::get('/categories/{categoryId}/stores/{storeId}/products', [StoreController::class, 'showProducts']);
    Route::get('/categories/{categoryId}/stores/{storeId}/products/{productId}', [ProductController::class, 'show']);

    Route::post('/cart', [CartItemController::class, 'addToCart']);
    Route::get('/show/cart', [CartItemController::class, 'getCartItems']);
    Route::put('/cart/{cartItemId}', [CartItemController::class, 'updateQuantitiyItem']);
    Route::delete('/cart/{cartItem}', [CartItemController::class, 'destroy']);

    Route::post('/order',[OrderController::class, 'confirmOrder']);
    Route::get('/orders', [OrderController::class, 'getClientOrders']);

    Route::get('/search/{name}',[ProductController::class, 'search'])->name('product.search');

    Route::post('/add/favourites', [FavouriteProductController::class, 'addToFavourites']);
    Route::get('/get/favourites', [FavouriteProductController::class, 'getFavourites']);
    Route::delete('/delete/favourite/{favouriteId}', [FavouriteProductController::class, 'deleteFavourite']);
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
