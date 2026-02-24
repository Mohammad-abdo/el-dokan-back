<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DoctorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = Auth::user();
        
        if (!$user->hasRole('doctor')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Doctor access required.'
            ], 403);
        }

        if (!$user->doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found'
            ], 404);
        }

        if ($user->doctor->status === 'pending_review') {
            return response()->json([
                'success' => false,
                'message' => 'Account under review. We will notify you once activated.',
                'code' => 'account_pending_review',
            ], 403);
        }

        return $next($request);
    }
}
