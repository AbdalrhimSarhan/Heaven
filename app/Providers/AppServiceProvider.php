<?php

namespace App\Providers;

use App\Models\Cart_item;
use App\Models\Order;
use App\Models\Store_product;
use App\Observers\CartItemObserver;
use App\Observers\OrderObserver;
use App\Observers\StoreProductObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Cart_item::observe(CartItemObserver::class);
        Order::observe(OrderObserver::class);
        Store_product::observe(StoreProductObserver::class);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success'             => false,
                        'message'             => 'Too many requests. Please slow down.',
                        'retry_after_seconds' => $headers['Retry-After'],
                    ], 429, $headers);
                });
        });
    }
}
