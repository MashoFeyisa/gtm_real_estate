<?php

namespace App\Http\Middleware;

use App\Models\PaymentGatewayLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticate an inbound payment-gateway callback (SRS §83).
 *
 * The provider signs `<timestamp>.<raw body>` with HMAC-SHA256 and sends
 * `X-Provider-Signature: sha256=<hex>`, `X-Provider: <telebirr|cbe_birr|chapa>`
 * and `X-Provider-Timestamp: <unix seconds>`. The timestamp is part of the
 * signed payload and must fall inside the provider's tolerance window, so a
 * captured request cannot be replayed later. A provider with no configured
 * secret rejects every call - the default is closed, never open.
 *
 * Every call is written to `payment_gateway_logs`, accepted or rejected, so
 * unauthorised callback attempts are visible to finance. The route itself is
 * rate limited (see the `payments-callback` limiter) so a flood cannot exhaust
 * the audit table or the signature check.
 */
class VerifyPaymentGatewaySignature
{
    public function handle(Request $request, Closure $next, ?string $provider = null): Response
    {
        $provider ??= (string) $request->header('X-Provider', '');
        $signature = (string) $request->header('X-Provider-Signature', '');
        $timestamp = (string) $request->header('X-Provider-Timestamp', '');

        $logContext = [
            'provider' => $provider !== '' ? $provider : null,
            'gateway_reference' => $request->input('gateway_reference'),
            'transaction_number' => $request->input('transaction_number'),
            'signature' => $signature !== '' ? substr($signature, 0, 255) : null,
            'provider_timestamp' => $timestamp !== '' ? substr($timestamp, 0, 32) : null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'payload' => $this->redactedPayload($request),
        ];

        $config = config("services.payments.providers.{$provider}");

        if (! $config || empty($config['secret'])) {
            return $this->reject($logContext, 'provider.unknown', 'No signing secret configured for provider ['.$provider.'] - every callback is rejected.');
        }

        if ($timestamp === '' || ! ctype_digit($timestamp)) {
            return $this->reject($logContext, 'timestamp.missing', 'Missing or malformed provider timestamp header.');
        }

        $tolerance = (int) ($config['tolerance_seconds'] ?? 300);
        if (abs(now()->timestamp - (int) $timestamp) > $tolerance) {
            return $this->reject($logContext, 'timestamp.expired', 'Provider timestamp is outside the accepted replay window.');
        }

        if ($signature === '' || ! $this->signatureMatches($request, $signature, $timestamp, (string) $config['secret'])) {
            return $this->reject($logContext, 'signature.invalid', 'Missing or invalid HMAC-SHA256 provider signature.');
        }

        $payment = PaymentGatewayLog::create($logContext + [
            'event' => 'callback.received',
            'outcome' => 'accepted',
            'signature_valid' => true,
            'response_code' => 200,
            'message' => 'Signature verified; callback forwarded to the payments controller.',
        ]);

        $request->attributes->set('provider_signature_log', $payment);

        return $next($request);
    }

    /**
     * Log a refused callback and answer 401. Rejections are fail-closed: an
     * unknown provider, a missing/expired timestamp and a bad signature all
     * stop the request before it can touch a payment.
     *
     * @param  array<string, mixed>  $logContext
     */
    protected function reject(array $logContext, string $event, string $message): Response
    {
        PaymentGatewayLog::create($logContext + [
            'event' => $event,
            'outcome' => 'rejected',
            'signature_valid' => false,
            'response_code' => 401,
            'message' => $message,
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Unauthorized: '.$message,
        ], 401);
    }

    /**
     * Constant-time comparison of the supplied signature against the HMAC of
     * the exact payload the provider signed: the timestamp, a separator, then
     * the raw request body.
     */
    protected function signatureMatches(Request $request, string $signature, string $timestamp, string $secret): bool
    {
        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        return hash_equals($expected, $this->normalise($signature));
    }

    /**
     * Accept `sha256=<hex>` or a bare hex digest, case-insensitively.
     */
    protected function normalise(string $signature): string
    {
        $hex = str_contains($signature, '=') ? substr($signature, strpos($signature, '=') + 1) : $signature;

        return strtolower(trim($hex));
    }

    /**
     * @return array<string, mixed>
     */
    protected function redactedPayload(Request $request): array
    {
        $payload = $request->except(['deposit_slip', 'slip', 'card', 'cvv', 'pan', 'account_number']);

        return is_array($payload) ? $payload : [];
    }
}
