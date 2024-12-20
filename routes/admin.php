<?php

use App\Http\Controllers\Admin\AdminStoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'admin','setLang'])->group(function () {
    Route::get('/showAllStores',[AdminStoreController::class,'getAllStores']);
    Route::get('showStore/{storeId}',[AdminStoreController::class,'showStore']);
    Route::post('/createStore',[AdminStoreController::class,'createNewStore'])->missing(function(){
        return response()->json(__('message.store_not_found'), 404);
    });
    Route::post('/updateStore/{store}',[AdminStoreController::class,'updateStore'])->missing(function(){
        return response()->json(__('message.store_not_found'), 404);
    });
    Route::post('/destroyStore',[AdminStoreController::class,'destroyStore'])->missing(function(){
        return response()->json(__('message.store_not_found'), 404);
    });
});
