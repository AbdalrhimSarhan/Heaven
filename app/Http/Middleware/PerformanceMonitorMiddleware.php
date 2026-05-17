<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime   = microtime(true);
        $startMemory = memory_get_usage(true);

        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        $memoryMb   = round((memory_get_peak_usage(true) - $startMemory) / 1048576, 2);

        Log::info('[AOP:Performance]', [
            'method'      => $request->method(),
            'path'        => $request->path(),
            'user_id'     => auth()->id(),
            'status'      => $response->getStatusCode(),
            'duration_ms' => $durationMs,
            'memory_mb'   => $memoryMb,
        ]);

        $response->headers->set('X-Execution-Time-Ms', $durationMs);

        return $response;
    }
}
