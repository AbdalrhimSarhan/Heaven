<?php

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminStoreController;
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

Route::middleware(['auth:api', 'admin','setLang'])->group(function () {
    Route::get('/showAllStores',[AdminStoreController::class,'getAllStores']);
    Route::get('showStore/{store}',[AdminStoreController::class,'showStore'])->missing(function(Request $request){
        app()->setLocale($request->header('lang','en'));
        return ResponseHelper::jsonResponse('',__('message.store_not_found'), 404,false);
    });
    Route::post('/createStore',[AdminStoreController::class,'createNewStore']);
    Route::post('/updateStore/{store}',[AdminStoreController::class,'updateStore'])->missing(function(Request $request){
        app()->setLocale($request->header('lang','en'));
        return ResponseHelper::jsonResponse('',__('message.store_not_found'), 404,false);
    });
    Route::delete('/destroyStore/{store}',[AdminStoreController::class,'destroyStore'])->missing(function(Request $request){
        app()->setLocale($request->header('lang','en'));
        return ResponseHelper::jsonResponse('',__('message.store_not_found'), 404,false);
    });

    Route::post('/createCategory',[CategoryController::class,'createNewCategory']);

    Route::post('/stores/{storeId}/products', [AdminProductController::class, 'createProduct']);
    Route::post('/stores/{store_id}/products/{product_id}', [AdminProductController::class, 'updateProduct']);
    Route::get('/stores/{storeId}/products', [AdminProductController::class, 'getStoreProducts']);
    Route::get('/stores/{storeId}/products/{productId}', [AdminProductController::class, 'showProductDetails']);
    Route::delete('/stores/{storeId}/products/{productId}', [AdminProductController::class, 'deleteProduct']);
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
