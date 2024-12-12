<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use Illuminate\Http\Request;

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
    public function show($categoryId , $storeId , $productId)
    {
        try {
            $category = Category::where('id', $categoryId)->firstOrFail();

            $store = $category->stores()->where('stores.id', $storeId)->firstOrFail();

            $product = $store->products()->where('products.id', $productId)->firstOrFail();

            return ResponseHelper::jsonResponse(ProductResource::make($product), 'successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::jsonResponse(null, 'Resource not found', 404, false);
        }


        return ResponseHelper::jsonResponse(ProductResource::make($product),'successfully');
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
