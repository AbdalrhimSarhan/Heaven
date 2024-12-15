<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($categoryId, $storeId, $productId)
    {
        try {
            // Fetch category
            $category = Category::where('id', $categoryId)->firstOrFail();

            // Fetch store belonging to the category
            $store = $category->stores()->where('stores.id', $storeId)->firstOrFail();

            // Fetch product belonging to the store
            $product = $store->products()->where('products.id', $productId)->firstOrFail();

            // Fetch store_product_id from store_product table
            $storeProductId = DB::table('store_product')
                ->where('store_id', $storeId)
                ->where('product_id', $productId)
                ->value('id'); // Assuming the primary key in store_product is 'id'

            if (!$storeProductId) {
                return ResponseHelper::jsonResponse(null, 'Product not found in this store', 404, false);
            }

            // Add store_product_id to product resource
            $response = ProductResource::make($product)->toArray(request());
            $response['store_product_id'] = $storeProductId;

            // Return the final response
            return ResponseHelper::jsonResponse($response, 'successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::jsonResponse(null, 'Resource not found', 404, false);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
