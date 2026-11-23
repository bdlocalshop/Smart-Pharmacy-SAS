<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClientOrToken
{
    public function __construct(
        protected VerifyHmacSignature $hmacMiddleware
    ) {}

    /**
     * Handle an incoming request supporting either Sanctum Bearer tokens or HMAC headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. If Bearer token is provided, verify via Sanctum
        if ($request->bearerToken()) {
            if ($user = Auth::guard('sanctum')->user()) {
                $request->setUserResolver(fn () => $user);
                return $next($request);
            }

            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'INVALID_TOKEN',
                'message' => 'The provided Bearer token is invalid or expired.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 2. If HMAC headers are present, delegate to VerifyHmacSignature
        if ($request->hasHeader('X-Signature') || $request->hasHeader('X-Client-Id')) {
            return $this->hmacMiddleware->handle($request, $next);
        }

        // 3. Neither authentication credential supplied
        return new JsonResponse([
            'status' => 'error',
            'error_code' => 'UNAUTHORIZED',
            'message' => 'Authentication required via Bearer session token or HMAC signature headers.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
