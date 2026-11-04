<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\VerificationStatus;
use App\Models\PharmacyLicense;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHmacSignature
{
    /**
     * Allowed clock skew in seconds (replay attack cutoff window).
     */
    public const MAX_CLOCK_SKEW_SECONDS = 300;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientId = $request->header('X-Client-Id');
        $timestamp = $request->header('X-Timestamp');
        $signature = $request->header('X-Signature');

        // 1. Verify presence of required security headers
        if (empty($clientId) || empty($timestamp) || empty($signature)) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'MISSING_AUTH_HEADERS',
                'message' => 'Missing required HMAC authentication headers: X-Client-Id, X-Timestamp, or X-Signature.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 2. Anti-Replay Protection: check ±300s timestamp window
        if (! is_numeric($timestamp)) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'INVALID_TIMESTAMP',
                'message' => 'The X-Timestamp header must be a valid integer Unix timestamp.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $now = time();
        $requestTime = (int) $timestamp;

        if (abs($now - $requestTime) > self::MAX_CLOCK_SKEW_SECONDS) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'TIMESTAMP_EXPIRED',
                'message' => 'Request timestamp is outside the valid 300-second window.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 3. Resolve Pharmacy and License
        /** @var PharmacyLicense|null $license */
        $license = PharmacyLicense::with('pharmacy')
            ->where('id', $clientId)
            ->orWhere('license_key', $clientId)
            ->orWhere('pharmacy_id', $clientId)
            ->first();

        if (! $license || ! $license->pharmacy) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'INVALID_CLIENT',
                'message' => 'Client ID or license key is not recognized.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($license->is_revoked) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'LICENSE_REVOKED',
                'message' => 'This license has been revoked by administration.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($license->pharmacy->verification_status === VerificationStatus::SUSPENDED) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'PHARMACY_SUSPENDED',
                'message' => 'This pharmacy has been suspended.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($license->pharmacy->verification_status !== VerificationStatus::APPROVED) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'PHARMACY_NOT_APPROVED',
                'message' => 'This pharmacy has not been approved.',
            ], Response::HTTP_FORBIDDEN);
        }

        // 4. Verify HMAC SHA-256 Signature
        $plainSecret = $license->getPlainSecret();
        $payload = $request->getContent();

        // Calculate signature with provided clientId
        $expectedSignature = hash_hmac('sha256', $clientId . $timestamp . $payload, $plainSecret);

        $isValid = hash_equals($expectedSignature, (string) $signature);

        // Also permit signing against license_key or license UUID if different from header
        if (! $isValid && $license->license_key !== $clientId) {
            $altSignature = hash_hmac('sha256', $license->license_key . $timestamp . $payload, $plainSecret);
            $isValid = hash_equals($altSignature, (string) $signature);
        }

        if (! $isValid && $license->id !== $clientId) {
            $altSignature2 = hash_hmac('sha256', $license->id . $timestamp . $payload, $plainSecret);
            $isValid = hash_equals($altSignature2, (string) $signature);
        }

        // Fallback check against raw secret hash if different
        if (! $isValid && $plainSecret !== $license->client_secret_hash) {
            $fallbackSig = hash_hmac('sha256', $clientId . $timestamp . $payload, $license->client_secret_hash);
            $isValid = hash_equals($fallbackSig, (string) $signature);
        }

        if (! $isValid) {
            return new JsonResponse([
                'status' => 'error',
                'error_code' => 'INVALID_SIGNATURE',
                'message' => 'HMAC signature verification failed.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 5. Store resolved credentials in request attributes
        $request->attributes->set('license', $license);
        $request->attributes->set('pharmacy', $license->pharmacy);

        return $next($request);
    }
}
