<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use App\Models\Store_product;

class AdminProductController extends Controller
{
    public function createProduct(CreateProductRequest $request, $storeId)
    {
        // Validate the store exists
        $store = Store::find($storeId);

        if (!$store) {
            return ResponseHelper::jsonResponse([], __('message.store_not_found'), 404);
        }

        // Create the product
        $product = Product::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'product_image' => $request->product_image,
        ]);

        // Check if product was created successfully
        if (!$product) {
            return ResponseHelper::jsonResponse([], __('message.product_creation_failed'), 500);
        }

        // Link product with the store in store_product table
        $store_product = Store_product::create([
            'store_id' => $storeId,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'price' => $request->price,
        ]);

        // Check if store_product was created successfully
        if (!$store_product) {
            return ResponseHelper::jsonResponse([], __('message.store_product_link_failed'), 500);
        }

        // Return the response using ProductResource
        return ResponseHelper::jsonResponse(
            new ProductResource($product, $store_product),
            __('message.admin_product.created'),
            201
        );
    }
    public function updateProduct(UpdateProductRequest $request, $store_id, $product_id)
    {
        // Validate store and product existence
        $store = Store::findOrFail($store_id);
        $product = Product::findOrFail($product_id);

        // Get validated data from the request
        $validated = $request->validated();

        // Update product attributes
        $product->update([
            'name_en' => $validated['name_en'] ?? $product->name_en,
            'name_ar' => $validated['name_ar'] ?? $product->name_ar,
            'description_en' => $validated['description_en'] ?? $product->description_en,
            'description_ar' => $validated['description_ar'] ?? $product->description_ar,
            'product_image' => $validated['product_image'] ?? $product->product_image,
        ]);

        // Find the existing store product pivot row
        $storeProduct = $store->products()->where('products.id', $product_id)->first()->pivot;

        // Update store product attributes (price and quantity)
        if (isset($validated['price']) || isset($validated['quantity'])) {
            $storeProduct->update([
                'price' => $validated['price'] ?? $storeProduct->price,
                'quantity' => $validated['quantity'] ?? $storeProduct->quantity,
            ]);
        }

        // Return updated product resource with storeProduct data
        return ResponseHelper::jsonResponse(
            new ProductResource($product, $storeProduct),
            __('message.admin_product.updated'),
            200
        );
    }
    public function getStoreProducts($storeId)
    {
        // Ensure the store exists
        $store = Store::findOrFail($storeId);
        // Retrieve all store_product entries for the given store
        $storeProducts = Store_product::where('store_id', $storeId)->get();

        // Retrieve the products using the product IDs from the store_product entries
        $products = Product::whereIn('id', $storeProducts->pluck('product_id'))->get();

        $data = [];

        // Loop through the products and include their store-specific data (price, quantity)
        foreach ($products as $product) {
            // Get the corresponding store_product data (price, quantity)
            $storeProduct = $storeProducts->firstWhere('product_id', $product->id);

            // Use the ProductResource to transform the product with the store-specific data
            $data[] = new ProductResource($product, $storeProduct);
        }

        // Get the store's name based on the current locale
        $storeName = app()->getLocale() === 'ar' ? $store->name_ar : $store->name_en;

        // Return the response
        return ResponseHelper::jsonResponse(['store_id' => $store->id,
            'store_name' => $storeName,
            'products' => $data],
         __('message.admin_product.get_products'), 200);
    }

    public function showProductDetails($storeId, $productId)
    {
        // Ensure the store exists
        $store = Store::find($storeId);
        if (!$store) {
            return ResponseHelper::jsonResponse([], __('message.store_not_found'), 404);
        }

        // Retrieve the product
        $product = Product::find($productId);
        if (!$product) {
            return ResponseHelper::jsonResponse([], __('message.product_not_found'), 404);
        }

        // Retrieve the store-product relationship
        $storeProduct = Store_product::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->first();

        if (!$storeProduct) {
            return ResponseHelper::jsonResponse([], __('message.store product not found'), 404);
        }

        // Use the ProductResource to format the response
        $productResource = new ProductResource($product, $storeProduct);

        // Return the response
        return ResponseHelper::jsonResponse(
            $productResource,
            __('message.admin_product.details_retrieved'),
            200
        );
    }
    public function deleteProduct($storeId, $productId)
    {
        // Ensure the store exists
        $store = Store::find($storeId);
        if (!$store) {
            return ResponseHelper::jsonResponse([], __('message.store_not_found'), 404);
        }

        // Check if the product exists
        $product = Product::find($productId);
        if (!$product) {
            return ResponseHelper::jsonResponse([], __('message.product_not_found'), 404);
        }

        // Check if the product is linked to the store
        $storeProduct = Store_product::where('store_id', $storeId)
            ->where('product_id', $productId)
            ->first();

        if (!$storeProduct) {
            return ResponseHelper::jsonResponse([], __('message.store product not found'), 404);
        }

        // Delete the relationship in the store_product table
        $storeProduct->delete();

        // Optionally delete the product itself if it is not linked to any other stores
        $remainingLinks = Store_product::where('product_id', $productId)->exists();
        if (!$remainingLinks) {
            $product->delete();
        }
        // Return the response
        return ResponseHelper::jsonResponse([], __('message.admin_product.deleted'), 200);
    }
}
