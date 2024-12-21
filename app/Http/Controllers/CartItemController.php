<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\UpdateQuantityRequest;
use App\Http\Resources\CartItemResource;
use App\Models\Cart_item;
use App\Models\Store_product;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public function addToCart(CartStoreRequest $request){

        $product = $request->validated();
        try {
        $storeProduct = Store_product::where('product_id', $product['product_id'])
            ->where('store_id', $product['store_id'])
            ->firstOrFail();


        $cartItem = Cart_item::create([
            'user_id' => auth()->id(),
            'store_product_id' => $product['store_product_id'],
            'quantity' => $product['quantity'],
            'order_id' => null, // Ensuring it's null initially
        ]);

        $storeProduct->decrement('quantity', $product['quantity']);

        return ResponseHelper::jsonResponse(['the product is added to cart'=>$cartItem,
            'total_price' => $storeProduct->price * $product['quantity']
            ,], __('message.cart.success'));
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the store_product is not found
        return ResponseHelper::jsonResponse(null, __('message.cart.store') ,404, false);
        } catch (\Exception $e) {
            // Handle any other exceptions
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }

    public function getCartItems()
    {
        // Fetch cart items for the user
        $cartItems = Cart_item::with(['store_product.product'])
            ->where('user_id', auth()->id())
            ->get();

        // Check if the cart is empty
        if ($cartItems->isEmpty()) {
            return ResponseHelper::jsonResponse([], __('message.show_fail'), 404);
        }

        // Return the cart items using CartItemResource
        return ResponseHelper::jsonResponse(
            CartItemResource::collection($cartItems),
        __('message.show_success'),
            200
        );
    }

    public function updateQuantitiyItem(UpdateQuantityRequest $request, $cartItemId)
    {
        $language = app()->getLocale();
        $validQuantity = $request->validated();
        // Find the cart item by ID
        $cartItem = Cart_item::find($cartItemId);

        if (!$cartItem) {
            return ResponseHelper::jsonResponse([], __('message.cart.update_fail'), 404,false);
        }

        // Get the related store product
        $storeProduct = $cartItem->store_product;

        if (!$storeProduct) {
            return ResponseHelper::jsonResponse([], __('message.cart.stores'), 404,false);
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
                return ResponseHelper::jsonResponse([], __('message.cart.less_quantity'), 400,false);
            }
            $storeProduct->quantity -= $quantityDifference; // Reduce the store stock
        }

        // Save the updated store product quantity
        $storeProduct->save();

        // Update the cart item's quantity
        $cartItem->quantity = $newQuantity;
        $cartItem->save();

        $productName = $language === 'ar'
            ? $storeProduct->product->name_ar
            : $storeProduct->product->name_en;

        // Return a success response
        return ResponseHelper::jsonResponse([
            'updated_cart_item' => [
                'id' => $cartItem->id,
                'product_name' =>  $productName,
                'quantity' => $cartItem->quantity,
                'unit_price' => $storeProduct->price,
                'total_price' => $cartItem->quantity * $storeProduct->price,
            ],
            'remaining_stock' => $storeProduct->quantity,
        ], __('message.cart.find_quantity'), 200);

    }

    public function destroy($cartItemId)
    {
        try {
            $cartItem = Cart_item::find($cartItemId);

            if (!$cartItem) {
                return ResponseHelper::jsonResponse(null, __('message.cart.fail'), 404, false);
            }

            $storeProductId = $cartItem->store_product_id;
            if($cartItem->order_id != null){
                return ResponseHelper::jsonResponse([], __('message.cart.after_confirm'), 404,false);
            }

            $storeProduct = Store_product::find($storeProductId);

            if (!$storeProduct) {
                return ResponseHelper::jsonResponse(null, __('message.cart.store'), 404, false);
            }

            $storeProduct->increment('quantity', $cartItem->quantity);
            $cartItem->delete();

            return ResponseHelper::jsonResponse(null, __('message.cart.destroy_success'));
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }
}
