<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class RateLimitMiddleware
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1'): BaseResponse
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, (int) $maxAttempts)) {
            return $this->buildResponse($key, (int) $maxAttempts);
        }

        $this->limiter->hit($key, (int) $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            (int) $maxAttempts,
            $this->calculateRemainingAttempts($key, (int) $maxAttempts)
        );
    }

    /**
     * Resolve request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $userId = $request->user()?->getAuthIdentifier();

        if ($userId) {
            return sha1('user:'.$userId.'|'.$request->route()?->getDomain().'|'.$request->ip());
        }

        return sha1($request->route()?->getDomain().'|'.$request->ip());
    }

    /**
     * Create a 'too many attempts' response.
     */
    protected function buildResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        return response()->json([
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded. Try again in '.$retryAfter.' seconds.',
            'retry_after' => $retryAfter,
        ], 429);
    }

    /**
     * Add rate limiting headers to response.
     */
    protected function addHeaders(BaseResponse $response, int $maxAttempts, int $remainingAttempts): BaseResponse
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remainingAttempts),
        ]);

        return $response;
    }

    /**
     * Calculate remaining attempts.
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return $this->limiter->retriesLeft($key, $maxAttempts);
    }
}
