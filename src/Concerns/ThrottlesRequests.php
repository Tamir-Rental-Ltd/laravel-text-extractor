<?php

namespace TamirRental\DocumentExtraction\Concerns;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Sleep;
use InvalidArgumentException;

/**
 * Shared per-second request budget for extraction providers.
 *
 * The limiter and its lock live in the application cache, so the budget is shared
 * by every queue worker and console process across all servers. Providers opt in
 * by calling awaitRequestSlot() with their own key and budget before each request.
 */
trait ThrottlesRequests
{
    /**
     * Validate a requests-per-second setting as a non-negative integer so a malformed
     * value can never silently disable throttling. 0 disables throttling.
     */
    protected function resolveRequestsPerSecond(mixed $value): int
    {
        $requestsPerSecond = filter_var($value ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0],
        ]);

        if ($requestsPerSecond === false) {
            throw new InvalidArgumentException('requests_per_second must be a non-negative integer.');
        }

        return $requestsPerSecond;
    }

    /**
     * Block until the shared per-second budget for the key has a free slot, then claim it.
     */
    protected function awaitRequestSlot(string $key, int $requestsPerSecond): void
    {
        if ($requestsPerSecond === 0) {
            return;
        }

        while (! $this->claimRequestSlot($key, $requestsPerSecond)) {
            Sleep::for(100)->milliseconds();
        }
    }

    /**
     * Atomically check the budget and record a hit when a slot is free.
     */
    protected function claimRequestSlot(string $key, int $requestsPerSecond): bool
    {
        return Cache::lock($key.':lock', 5)->block(5, function () use ($key, $requestsPerSecond): bool {
            if (RateLimiter::tooManyAttempts($key, $requestsPerSecond)) {
                return false;
            }

            RateLimiter::hit($key, 1);

            return true;
        });
    }
}
