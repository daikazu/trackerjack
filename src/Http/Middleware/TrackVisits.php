<?php

declare(strict_types=1);

namespace Daikazu\Trackerjack\Http\Middleware;

use Closure;
use Daikazu\Trackerjack\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    public function handle(Request $request, Closure $next): Response
    {
        $visitorId = $this->getVisitorId($request);
        Log::info('TrackerJack: Current visitor ID', ['visitor_id' => $visitorId]);

        if ($this->shouldTrack($request)) {
            Visit::create([
                'visitor_id' => $visitorId,
                'url' => $request->fullUrl(),
                'referrer' => $request->header('referer'),
                'utm_source' => $request->get('utm_source'),
                'utm_medium' => $request->get('utm_medium'),
                'utm_campaign' => $request->get('utm_campaign'),
                'utm_term' => $request->get('utm_term'),
                'utm_content' => $request->get('utm_content'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        $response = $next($request);
        $cookieName = config('trackerjack.cookie.name');
        $hasCookie = $request->cookie($cookieName);

        Log::info('TrackerJack: Cookie status', [
            'cookie_name' => $cookieName,
            'has_cookie' => $hasCookie,
            'response_status' => $response->status(),
        ]);

        if ($response->status() === 200 && ! $hasCookie) {
            Log::info('TrackerJack: Setting cookie', ['visitor_id' => $visitorId]);

            $cookie = cookie(
                $cookieName,
                $visitorId,
                config('trackerjack.cookie.ttl'),
                '/', // path
                null, // domain
                true, // secure
                true, // httpOnly
                false, // raw
                'Lax' // sameSite
            );

            $response = $response->withCookie($cookie);
        }

        return $response;
    }

    protected function shouldTrack(Request $request): bool
    {
        if ($request->isMethod('GET') === false) {
            return false;
        }

        foreach (config('trackerjack.excluded_routes', []) as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }

    protected function getVisitorId(Request $request): string
    {

        if ($request->hasCookie(config('trackerjack.cookie.name'))) {
            return $request->cookie(config('trackerjack.cookie.name'));
        }

        Cookie::queue(
            config('trackerjack.cookie.name'),
            $footprint = $this->fingerprint($request),
            config('trackerjack.cookie.ttl'),
            null,
            config('trackerjack.cookie.domain'),
        );

        return $footprint;

    }

    protected function fingerprint(Request $request): string
    {
        return sha1(implode('|', array_filter([
            $request->ip(),
            $request->header('User-Agent'),
            config('trackerjack.uniqueness') ? Str::random(20) : null,
        ])));
    }
}
