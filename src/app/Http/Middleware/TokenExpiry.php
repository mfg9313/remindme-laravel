<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenExpiry
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = $request->user()->currentAccessToken();

        // Define token validity duration
        $tokenValidityInSeconds = 20;

        // Calculate token expiration time
        $expiresAt = $accessToken->created_at->copy()->addSeconds($tokenValidityInSeconds);

        if (Carbon::now()->greaterThanOrEqualTo($expiresAt)) {
            // Revoke the expired token
            $accessToken->delete();

            return response()->json([
                'ok' => false,
                'err' => 'ERR_INVALID_TOKEN',
                'msg' => 'Access token has expired',
            ], 401);
        }

        return $next($request);
    }
}
