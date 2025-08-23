<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EnforceUsageWindow
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $now          = Carbon::now();
        $windowSecs   = (int) config('usage.window_seconds', 60);   // use 3600 in prod
        $cooldownSecs = (int) config('usage.cooldown_seconds', 86400);
        $windowStart  = $user->usage_window_started_at ? Carbon::parse($user->usage_window_started_at) : null;

        if (!$windowStart || $now->greaterThanOrEqualTo($windowStart->copy()->addSeconds($cooldownSecs))) {
            $user->forceFill(['usage_window_started_at' => $now])->save();
            return $next($request);
        }

        $windowEnd = $windowStart->copy()->addSeconds($windowSecs);

        if ($now->lessThanOrEqualTo($windowEnd)) {
            return $next($request);
        }

        $retryAt    = $windowStart->copy()->addSeconds($cooldownSecs);
        $retryAfter = max(1, $now->diffInSeconds($retryAt));

        return response()
            ->json([
                'message'             => 'Votre session a expiré. Réessayez plus tard.',
                'code'                => 'cooldown_active',
                'retry_at'            => $retryAt->toIso8601String(),
                'retry_after_seconds' => $retryAfter,
            ], 429)
            ->withHeaders(['Retry-After' => $retryAfter]);
    }
}
