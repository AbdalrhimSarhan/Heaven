<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\FavouriteProductResource;
use App\Models\FavouriteProduct;
use App\Models\Store_product;
use Illuminate\Http\Request;

class FavouriteProductController extends Controller
{
    public function addToFavourites(Request $request)
    {
        // Validate the request
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'store_id' => 'required|exists:stores,id',
        ]);

        // Find the corresponding store_product_id
        $storeProduct = Store_product::where('product_id', $request->product_id)
            ->where('store_id', $request->store_id)
            ->first();

        if (!$storeProduct) {
            return ResponseHelper::jsonResponse(
                [],
                __('message.store product not found'),
                404,false
            );
        }

        // Check if the product is already in favourites
        $exists = FavouriteProduct::where('stores_product_id', $storeProduct->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($exists) {
            return ResponseHelper::jsonResponse(
                [],
                __('message.favourite product.exists'),
                409
            );
        }

        // Add to favourites
        FavouriteProduct::create([
            'stores_product_id' => $storeProduct->id,
            'user_id' => auth()->id(),
        ]);

        return ResponseHelper::jsonResponse(
            [],__('message.favourite product.added')
            ,
            201
        );
    }

    public function getFavourites()
    {
        // Fetch the user's favourite products
        $favourites = FavouriteProduct::with(['store_product.product', 'store_product.store'])
            ->where('user_id', auth()->id())
            ->get();

        if ($favourites->isEmpty()) {
            return ResponseHelper::jsonResponse(
                [],
                __('message.favourite product.empty'),
                404
            );
        }

        return ResponseHelper::jsonResponse(
            FavouriteProductResource::collection($favourites),
            __('message.favourite product.retrieved'),
            200
        );
    }

    public function deleteFavourite($id)
    {
        // Find the favourite product by its id
        $favouriteProduct = FavouriteProduct::find($id);

        if (!$favouriteProduct) {
            // If the product is not found, return a 404 response
            return ResponseHelper::jsonResponse([], __('message.favourite product.not found'), 404);
        }

        // Delete the favourite product
        $favouriteProduct->delete();

        // Return a success response
        return ResponseHelper::jsonResponse([], __('message.favourite product.deleted'), 200);
    }
}
