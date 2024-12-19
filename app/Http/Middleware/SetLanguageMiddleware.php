<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $language = $request->header('lang', 'en');

        if (!in_array($language, ['en', 'ar'])) {
            $language = 'en';
        }
        $request->merge(['lang' => $language]);

        return $next($request);
    }
}
