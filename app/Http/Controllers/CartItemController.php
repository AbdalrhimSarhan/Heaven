<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CartStoreRequest;
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

        return ResponseHelper::jsonResponse(['this products it add to cart'=>$cartItem,
            'total_price' => $storeProduct->price * $product['quantity']
            ,], 'Item added to cart successfully');
    }

    public function updateQuantitiyItem(CartStoreRequest $request)
    {

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
