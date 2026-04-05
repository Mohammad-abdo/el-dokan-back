<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminSecurityMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $userId = auth()->id();

        Log::channel('audit')->info('admin_access', [
            'user_id' => $userId,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ]);

        $response = $next($request);

        Log::channel('audit')->info('admin_response', [
            'user_id' => $userId,
            'status_code' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
