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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CartItemController extends Controller
{
    //    private $fcmservice;
    //    public function __construct(FcmService  $fcmservice){
    //        $this->fcmservice = $fcmservice;
    //    }

    public function addToCart(CartStoreRequest $request)
    {
        $startTime = microtime(true);
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

            $storeProduct->decrement('quantity', $product['quantity']);

            $cartItem = Cart_item::create([
                'user_id' => auth()->id(),
                'store_product_id' => $storeProduct->id,
                'quantity' => $product['quantity'],
                'order_id' => null,
            ]);


            $cartItemData = json_encode($cartItem->only('user_id', 'store_product_id', 'quantity', 'id'));

            $data = array_merge(
                $cartItem->only('user_id', 'store_product_id', 'quantity', 'id'),
                ['total_price' => $storeProduct->price * $product['quantity']]
            );

            $executionTime = (microtime(true) - $startTime) * 1000; // Convert to ms

            return ResponseHelper::jsonResponse(
                $data,
                __('message.cart.success'),
                200,
                true
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('❌ [BASIC] Product not found', ['error' => $e->getMessage()]);
            return ResponseHelper::jsonResponse(null, __('message.cart.store'), 404, false);
        } catch (\Exception $e) {
            Log::error('❌ [BASIC] Exception occurred', ['error' => $e->getMessage()]);
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }


    public function addToCartBasicIntegrity(CartStoreRequest $request)
    {
        $startTime = microtime(true);
        $product = $request->validated();

        try {
            $storeProduct = Store_product::where('product_id', $product['product_id'])
                ->where('store_id', $product['store_id'])
                ->firstOrFail();


            $affected = DB::table('store_product')
                ->where('id', $storeProduct->id)
                ->where('quantity', '>=', $product['quantity'])
                ->decrement('quantity', $product['quantity']);

            if ($affected === 0) {
                return ResponseHelper::jsonResponse([
                    'metrics' => [
                        'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                        'strategy' => 'Atomic Conditional Update',
                    ]
                ], 'Insufficient stock or concurrent conflict detected', 409, false);
            }


            $cartItem = Cart_item::create([
                'user_id' => auth()->id(),
                'store_product_id' => $storeProduct->id,
                'quantity' => $product['quantity'],
                'order_id' => null,
            ]);

            return ResponseHelper::jsonResponse([
                'cart_item_id' => $cartItem->id,
                'remaining_stock' => Store_product::find($storeProduct->id)->quantity,
                'metrics' => [
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    'memory_usage_kb' => round(memory_get_peak_usage(true) / 1024, 2),
                    'strategy' => 'Requirement#1 Data Integrity Only',
                ]
            ], 'Cart item added with atomic integrity protection', 200, true);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }

    public function addToCartSafe(CartStoreRequest $request)
    {
        $startTime = microtime(true);
        $product = $request->validated();

        try {
            $result = DB::transaction(function () use ($product) {

                $storeProduct = Store_product::where('product_id', $product['product_id'])
                    ->where('store_id', $product['store_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($storeProduct->quantity < $product['quantity']) {
                    throw new \Exception('Insufficient stock');
                }


                $storeProduct->decrement('quantity', $product['quantity']);

                $cartItem = Cart_item::create([
                    'user_id' => auth()->id(),
                    'store_product_id' => $storeProduct->id,
                    'quantity' => $product['quantity'],
                    'order_id' => null,
                ]);

                return [
                    'cart' => $cartItem,
                    'stock' => $storeProduct->fresh()->quantity
                ];
            });

            return ResponseHelper::jsonResponse([
                'cart_item_id' => $result['cart']->id,
                'remaining_stock' => $result['stock'],
                'metrics' => [
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    'memory_usage_kb' => round(memory_get_peak_usage(true) / 1024, 2),
                    'strategy' => 'Requirement#8 Full ACID Transaction',
                ]
            ], 'Cart item added with full ACID transaction', 200, true);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse([
                'metrics' => [
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    'strategy' => 'Requirement#8 Full ACID Transaction',
                ]
            ], $e->getMessage(), 500, false);
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
            ['total_price' => $allTotalPrice],
        ]);
        return ResponseHelper::jsonResponse(
            $data,
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
            return ResponseHelper::jsonResponse([], __('message.cart.update_fail'), 404, false);
        }

        $storeProduct = $cartItem->store_product;

        if (!$storeProduct) {
            return ResponseHelper::jsonResponse([], __('message.cart.stores'), 404, false);
        }

        $oldQuantity = $cartItem->quantity;
        $newQuantity = $validQuantity['quantity'];
        $quantityDifference = $newQuantity - $oldQuantity;

        if ($quantityDifference < 0) {
            $absoluteDifference = abs($quantityDifference);
            $storeProduct->quantity += $absoluteDifference;
        } else {
            if ($storeProduct->quantity < $quantityDifference) {
                return ResponseHelper::jsonResponse([], __('message.cart.less_quantity'), 400, false);
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
            if ($cartItem->order_id != null) {
                return ResponseHelper::jsonResponse([], __('message.cart.after_confirm'), 404, false);
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
