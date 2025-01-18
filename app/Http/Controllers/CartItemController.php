<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\UpdateQuantityRequest;
use App\Http\Resources\CartItemResource;
use App\Models\Cart_item;
use App\Models\Store_product;
use App\services\FcmService;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
//    private $fcmservice;
//    public function __construct(FcmService  $fcmservice){
//        $this->fcmservice = $fcmservice;
//    }
    public function addToCart(CartStoreRequest $request)
    {
        $product = $request->validated();

        try {
            $storeProduct = Store_product::where('product_id', $product['product_id'])
                ->where('store_id', $product['store_id'])
                ->firstOrFail();

            if ($product['quantity'] > $storeProduct->quantity) {
                return ResponseHelper::jsonResponse(
                    null,
                    "The requested quantity exceeds the available stock of {$storeProduct->quantity}.",
                    400,
                    false
                );
            }
            $cartItem = Cart_item::create([
                'user_id' => auth()->id(),
                'store_product_id' => $storeProduct->id,
                'quantity' => $product['quantity'],
                'order_id' => null,
            ]);
            $storeProduct->decrement('quantity', $product['quantity']);

            $cartItemData = json_encode($cartItem->only('user_id', 'store_product_id', 'quantity', 'id'));

            $data = array_merge(
                $cartItem->only('user_id', 'store_product_id', 'quantity', 'id'),
                ['total_price' => $storeProduct->price * $product['quantity']]
            );
            return ResponseHelper::jsonResponse(
                $data,
                __('message.cart.success'),200,true
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::jsonResponse(null, __('message.cart.store'), 404, false);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }


    public function getCartItems()
    {
        $cartItems = Cart_item::with(['store_product.product'])
            ->where('user_id', auth()->id())
            ->where('order_id', null)
            ->get();

        if ($cartItems->isEmpty()) {
            return ResponseHelper::jsonResponse([], __('message.show_fail'), 404);
        }

        $allTotalPrice = $cartItems->sum(function ($item) {
            return $item->quantity * $item->store_product->price;
        });

        $data = array_merge([
            CartItemResource::collection($cartItems),
           [ 'total_price' => $allTotalPrice ],
        ]);
        return ResponseHelper::jsonResponse(
          $data ,
        __('message.cart.show_success'),
            200
        );
    }

    public function updateQuantitiyItem(UpdateQuantityRequest $request, $cartItemId)
    {
        $language = app()->getLocale();
        $validQuantity = $request->validated();
        $cartItem = Cart_item::find($cartItemId);

        if (!$cartItem) {
            return ResponseHelper::jsonResponse([], __('message.cart.update_fail'), 404,false);
        }

        $storeProduct = $cartItem->store_product;

        if (!$storeProduct) {
            return ResponseHelper::jsonResponse([], __('message.cart.stores'), 404,false);
        }

        $oldQuantity = $cartItem->quantity;
        $newQuantity = $validQuantity['quantity'];
        $quantityDifference = $newQuantity - $oldQuantity;

        if ($quantityDifference < 0) {
            $absoluteDifference = abs($quantityDifference);
            $storeProduct->quantity += $absoluteDifference;
        } else {
            if ($storeProduct->quantity < $quantityDifference) {
                return ResponseHelper::jsonResponse([], __('message.cart.less_quantity'), 400,false);
            }
            $storeProduct->quantity -= $quantityDifference;
        }

        $storeProduct->save();

        $cartItem->quantity = $newQuantity;
        $cartItem->save();

        $productName = $language === 'ar'
            ? $storeProduct->product->name_ar
            : $storeProduct->product->name_en;

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

           // $this->fcmservice->sendNotification(auth()->fcm_token,'Heaven App',__('message.cart.destroy_success'));
            return ResponseHelper::jsonResponse(null, __('message.cart.destroy_success'));
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }
}
