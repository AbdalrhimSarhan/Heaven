<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\UpdateQuantityRequest;
use App\Models\Cart_item;
use App\Models\Store_product;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public function addToCart(CartStoreRequest $request){

        $product = $request->validated();

        $storeProduct = Store_product::findOrFail($product['store_product_id']);

        $cartItem = Cart_item::create([
            'user_id' => auth()->id(),
            'store_product_id' => $product['store_product_id'],
            'quantity' => $product['quantity'],
            'order_id' => null, // Ensuring it's null initially
        ]);

        $storeProduct->decrement('quantity', $product['quantity']);

        return ResponseHelper::jsonResponse(['the product is added to cart'=>$cartItem,
            'total_price' => $storeProduct->price * $product['quantity']
            ,], 'Item added to cart successfully');
    }

    public function updateQuantitiyItem(UpdateQuantityRequest $request, $cartItemId)
    {
        $validQuantity = $request->validated();
        // Find the cart item by ID
        $cartItem = Cart_item::find($cartItemId);

        if (!$cartItem) {
            return ResponseHelper::jsonResponse([], 'Cart item not found', 404);
        }

        // Get the related store product
        $storeProduct = $cartItem->store_product;

        if (!$storeProduct) {
            return ResponseHelper::jsonResponse([], 'Store product not found', 404);
        }

        // New and old quantity
        $oldQuantity = $cartItem->quantity;
        $newQuantity = $validQuantity['quantity'];
        $quantityDifference = $newQuantity - $oldQuantity;

        // Prevent invalid negative changes in stock
        if ($quantityDifference < 0) {
            $absoluteDifference = abs($quantityDifference);
            $storeProduct->quantity += $absoluteDifference; // Return the extra stock to the store
        } else {
            // Ensure sufficient stock is available
            if ($storeProduct->quantity < $quantityDifference) {
                return ResponseHelper::jsonResponse([], 'Not enough stock available.', 400);
            }
            $storeProduct->quantity -= $quantityDifference; // Reduce the store stock
        }

        // Save the updated store product quantity
        $storeProduct->save();

        // Update the cart item's quantity
        $cartItem->quantity = $newQuantity;
        $cartItem->save();

        // Return a success response
        return ResponseHelper::jsonResponse([
            'updated_cart_item' => [
                'id' => $cartItem->id,
                'product_name' => $storeProduct->product->name,
                'quantity' => $cartItem->quantity,
                'unit_price' => $storeProduct->price,
                'total_price' => $cartItem->quantity * $storeProduct->price,
            ],
            'remaining_stock' => $storeProduct->quantity,
        ], 'Quantity updated successfully', 200);

    }

    public function destroy($cartItemId)
    {
        try {
            $cartItem = Cart_item::find($cartItemId);

            if (!$cartItem) {
                return ResponseHelper::jsonResponse(null, 'Cart item not found', 404, false);
            }

            $storeProductId = $cartItem->store_product_id;

            $storeProduct = Store_product::find($storeProductId);

            if (!$storeProduct) {
                return ResponseHelper::jsonResponse(null, 'Store product not found', 404, false);
            }

            $storeProduct->increment('quantity', $cartItem->quantity);
            $cartItem->delete();

            return ResponseHelper::jsonResponse(null, 'Item deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }
}
