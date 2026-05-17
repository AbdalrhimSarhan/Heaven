<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CartStoreRequest;
use App\Http\Requests\UpdateQuantityRequest;
use App\Http\Resources\CartItemResource;
use App\Models\Cart_item;
use App\Models\Store_product;
use App\Services\CartService;
use Illuminate\Support\Facades\Log;

class CartItemController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function addToCart(CartStoreRequest $request)
    {
        $startTime = microtime(true);
        $data = $request->validated();

        try {
            $result = $this->cartService->addBasic(
                auth()->id(),
                $data['product_id'],
                $data['store_id'],
                $data['quantity']
            );

            return ResponseHelper::jsonResponse(
                array_merge(
                    $result['cart_item']->only('user_id', 'store_product_id', 'quantity', 'id'),
                    [
                        'total_price' => $result['total_price'],
                        'metrics'     => [
                            'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                            'strategy'          => 'Basic - No Concurrency Protection',
                        ],
                    ]
                ),
                __('message.cart.success'),
                200,
                true
            );
        } catch (\RuntimeException $e) {
            return ResponseHelper::jsonResponse(null, $e->getMessage(), $e->getCode() ?: 400, false);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Product not found', ['error' => $e->getMessage()]);
            return ResponseHelper::jsonResponse(null, __('message.cart.store'), 404, false);
        } catch (\Exception $e) {
            Log::error('Cart addBasic error', ['error' => $e->getMessage()]);
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }

    public function addToCartBasicIntegrity(CartStoreRequest $request)
    {
        $startTime = microtime(true);
        $data = $request->validated();

        try {
            $result = $this->cartService->addWithIntegrity(
                auth()->id(),
                $data['product_id'],
                $data['store_id'],
                $data['quantity']
            );

            return ResponseHelper::jsonResponse([
                'cart_item_id'    => $result['cart_item']->id,
                'remaining_stock' => $result['remaining_stock'],
                'metrics'         => [
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    'strategy'          => 'Atomic Conditional Update (Req #1)',
                ],
            ], 'Cart item added with atomic integrity protection', 200, true);
        } catch (\RuntimeException $e) {
            return ResponseHelper::jsonResponse([
                'metrics' => ['execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)],
            ], $e->getMessage(), $e->getCode() ?: 409, false);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::jsonResponse(null, __('message.cart.store'), 404, false);
        } catch (\Exception $e) {
            Log::error('Cart addWithIntegrity error', ['error' => $e->getMessage()]);
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }

    public function addToCartFlashSale(CartStoreRequest $request)
    {
        $startTime = microtime(true);
        $data = $request->validated();

        try {
            $result = $this->cartService->addFlashSale(
                auth()->id(),
                $data['product_id'],
                $data['store_id'],
                $data['quantity']
            );

            return ResponseHelper::jsonResponse([
                'reserved'        => $result['reserved'],
                'remaining_stock' => $result['remaining_stock'],
                'metrics'         => [
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    'strategy'          => 'Redis Atomic Counter + Async Queue (Flash Sale)',
                ],
                'note' => 'Stock reserved in Redis. Cart item is being written to DB in background.',
            ], 'Stock reserved successfully', 200, true);
        } catch (\RuntimeException $e) {
            return ResponseHelper::jsonResponse([
                'metrics' => ['execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)],
            ], $e->getMessage(), $e->getCode() ?: 409, false);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::jsonResponse(null, __('message.cart.store'), 404, false);
        } catch (\Exception $e) {
            Log::error('Cart addFlashSale error', ['error' => $e->getMessage()]);
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }

    public function addToCartSafe(CartStoreRequest $request)
    {
        $startTime = microtime(true);
        $data = $request->validated();

        try {
            $result = $this->cartService->addSafe(
                auth()->id(),
                $data['product_id'],
                $data['store_id'],
                $data['quantity']
            );

            return ResponseHelper::jsonResponse([
                'cart_item_id'    => $result['cart_item']->id,
                'remaining_stock' => $result['remaining_stock'],
                'metrics'         => [
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                    'strategy'          => 'Pessimistic Locking + ACID Transaction (Req #7 & #8)',
                ],
            ], 'Cart item added safely with pessimistic locking', 200, true);
        } catch (\RuntimeException $e) {
            return ResponseHelper::jsonResponse([
                'metrics' => ['execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)],
            ], $e->getMessage(), $e->getCode() ?: 409, false);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseHelper::jsonResponse(null, __('message.cart.store'), 404, false);
        } catch (\Exception $e) {
            Log::error('Cart addSafe error', ['error' => $e->getMessage()]);
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
            ['total_price' => $allTotalPrice],
        ]);

        return ResponseHelper::jsonResponse($data, __('message.cart.show_success'), 200);
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
            $storeProduct->quantity += abs($quantityDifference);
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
                'id'           => $cartItem->id,
                'product_name' => $productName,
                'quantity'     => $cartItem->quantity,
                'unit_price'   => $storeProduct->price,
                'total_price'  => $cartItem->quantity * $storeProduct->price,
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

            if ($cartItem->order_id != null) {
                return ResponseHelper::jsonResponse([], __('message.cart.after_confirm'), 404, false);
            }

            $storeProduct = Store_product::find($cartItem->store_product_id);

            if (!$storeProduct) {
                return ResponseHelper::jsonResponse(null, __('message.cart.store'), 404, false);
            }

            $storeProduct->increment('quantity', $cartItem->quantity);
            $cartItem->delete();

            return ResponseHelper::jsonResponse(null, __('message.cart.destroy_success'));
        } catch (\Exception $e) {
            Log::error('Cart destroy error', ['error' => $e->getMessage()]);
            return ResponseHelper::jsonResponse(null, $e->getMessage(), 500, false);
        }
    }
}
